<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Exception;
use Generator;
use modResource;
use modTemplateVar;
use modX;
use msOption;
use PDO;
use xPDOObject;
use xPDOQuery;
use YandexMarket\Handlers\xPDOLazyIterator;
use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Service;
use YandexMarket\Xml\Generate;
use ymCategory;
use ymFieldAttribute;
use ymPricelist;

/**
 * @property int $id
 * @property string $name
 * @property string $file
 * @property string $type
 * @property string $class
 * @property bool $active
 * @property DateTimeImmutable $created_on
 * @property null|DateTimeImmutable $edited_on
 * @property null|DateTimeImmutable $generated_on
 * @property null|int $generate_mode
 * @property null|int $generate_interval
 * @property bool $need_generate
 * @property null|string $where //TODO: remove redundant field
 * @property null|array $properties
 * @property ymPricelist $object
 */
class Pricelist extends BaseObject
{
    const GENERATE_MODE_MANUALLY   = 0;
    const GENERATE_MODE_AFTER_SAVE = 1;
    const GENERATE_MODE_CRON_ONLY  = 2;

    /** @var Condition[] */
    protected $conditions;
    /** @var Field[] */
    protected $fields;
    /** @var Attribute[] */
    protected $fieldsAttributes;

    /** @var null|Marketplace */
    protected $marketplace;
    protected $groupedBy = [];

    protected $strictSql;
    protected $reduceQueries;
    protected $debugMode;

    public $categoriesPluginPrepared = false;
    public $offersPluginPrepared     = false;

    public $categoriesCount = 0; //categoriesGenerator устанавливает количество
    public $offersCount     = 0; //offersGenerator устанавливает количество

    public function __construct(modX $modx, xPDOObject $object = null)
    {
        parent::__construct($modx, $object);
        $this->marketplace = Marketplace::getMarketPlace($this->type, $modx);
        $this->strictSql = $modx->getOption('yandexmarket2_strict_sql', null, true);
        $this->reduceQueries = $modx->getOption('yandexmarket2_reduce_queries', null, false);
        $this->debugMode = $modx->getOption('yandexmarket2_debug_mode', null, false);
    }

    /**
     * @return null|Marketplace
     */
    public function getMarketplace()
    {
        return $this->marketplace;
    }

    public static function getObjectClass(): string
    {
        return ymPricelist::class;
    }

    public function get(string $field)
    {
        $field = str_replace(['Pricelist.', 'pricelist.'], '', $field);
        return parent::get($field);
    }

    public function touch()
    {
        $this->object->touch();
    }

    public function getFields(bool $withAttributes = false): array
    {
        if (!isset($this->fields)) {
            $this->fields = [];

            $q = $this->modx->newQuery('ymField', ['pricelist_id' => $this->id])->sortby('rank');
            foreach ($this->modx->getIterator('ymField', $q) as $ymField) {
                $field = new Field($this->modx, $ymField);
                $this->fields[$field->id] = $field;
            }

            if ($withAttributes && $attributes = $this->getFieldsAttributes(array_keys($this->fields))) {
                foreach ($attributes as $attribute) {
                    if ($field = $this->fields[$attribute->field_id] ?? null) {
                        $field->addAttribute($attribute);
                    }
                }
            }

            //It's reduces sql queries
            foreach ($this->fields as $field) {
                if ($field->parent && $parent = $this->fields[$field->parent] ?? null) {
                    $parent->addChildren($field);
                }
            }
        }

        return $this->fields;
    }

    protected function getFieldsAttributes(array $fieldIds): array
    {
        if (!isset($this->fieldsAttributes)) {
            $this->fieldsAttributes = [];

            $q = $this->modx->newQuery(ymFieldAttribute::class);
            $q->where(['field_id:IN' => $fieldIds]);

            $this->fieldsAttributes = array_map(function (ymFieldAttribute $attribute) {
                return new Attribute($this->modx, $attribute);
            }, $this->modx->getCollection(ymFieldAttribute::class, $q) ?? []);
        }

        return $this->fieldsAttributes;
    }

    /**
     * @param  int  $type
     *
     * @return Field|null
     */
    public function getFieldByType(int $type)
    {
        foreach ($this->getFields(true) as $field) {
            if ($field->type === $type) {
                return $field;
            }
        }
        return null;
    }

    protected function categoriesQuery(array $config = []): xPDOQuery
    {
        $q = $this->modx->newQuery('modResource');
        $q->select($this->modx->getSelectColumns('modResource', 'modResource'));

        if ($this->modx->getCount('ymCategory', ['pricelist_id' => $this->id])) {
            $q->innerJoin('ymCategory', 'Category', 'Category.resource_id = modResource.id');
            $q->select($this->modx->getSelectColumns('ymCategory', 'Category', 'category_'));
            $q->where(['Category.pricelist_id' => $this->id]);
        }

        $q = $this->assignConfigToQuery($q, $config);

        $eventResponse = $this->modx->invokeEvent('ym2OnBeforeCategoriesQuery',
            ['q' => &$q, 'query' => &$q, 'pricelist' => &$this]);
        if (!empty($eventResponse)) {
            $this->categoriesPluginPrepared = true;
        }

        if (empty($q->query['where'])) {
            //если в категории ничего не выбрано и не добавлено условий через плагин,
            $offersQuery = $this->offersQuery([], false);
            $offersQuery->query['columns'] = '';
            $offersQuery->query['groupby'] = '';
            $offersQuery->select(sprintf('DISTINCT `%s`.`parent`', $offersQuery->getAlias()));
            $offersQuery->prepare();
            $q->where(sprintf('`modResource`.`id` IN (%s)', $offersQuery->toSQL(true)));
            $q->usesOffersQuery = true;
        }

        return $q;
    }

    protected function assignConfigToQuery(xPDOQuery $query, array $config = []): xPDOQuery
    {
        if ($sortBy = $config['sortBy'] ?? null) {
            if (!is_array($sortBy)) {
                $sortBy = [$sortBy => $config['sortDir'] ?? 'ASC'];
            }
            foreach ($sortBy as $by => $dir) {
                $query->sortby($by, $dir);
            }
        }

        if ($limit = $config['limit'] ?? null) {
            $query->limit($limit, $config['offset'] ?? 0);
        }

        return $query;
    }

    /**
     * @param  array  $config
     *
     * @return Generator|Category[]
     */
    public function categoriesGenerator(array $config = [], bool $setCount = false): Generator
    {
        $query = $this->categoriesQuery($config);

        if ($setCount) {
            $countQuery = clone $query;
            $countQuery->query['limit'] = '';
            $countQuery->query['offset'] = '';
            $countQuery->query['orderby'] = [];
            $countQuery->query['sortby'] = [];
            $this->categoriesCount = $this->modx->getCount($countQuery->getClass(), $countQuery);
        }

        if ($this->modx->getOption('yandexmarket2_debug_mode')) {
            $query->prepare();
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Categories SQL: '.$query->toSQL(true));
        }

        $resources = $this->modx->getIterator($query->getClass(), $query);
        /** @var modResource $resource */
        foreach ($resources as $resource) {
            $ymCategory = new ymCategory($this->modx);
            $ymCategory->set('id', $resource->get('category_id') ?: $resource->id);
            $ymCategory->set('name', $resource->get('category_name') ?: $resource->pagetitle);
            $ymCategory->set('properties', $resource->get('category_properties') ?: []);
            $ymCategory->set('resource_id', $resource->id);
            $ymCategory->set('pricelist_id', $this->id);

            $category = new Category($this->modx, $ymCategory);
            $category->setResource($resource);
            yield $category;
        }
    }

    public function getConditions(): array
    {
        if (!isset($this->conditions)) {
            $this->conditions = [];
            $q = $this->modx->newQuery('ymCondition', ['pricelist_id' => $this->id])->sortby('id');
            foreach ($this->modx->getIterator('ymCondition', $q) as $ymCondition) {
                $condition = new Condition($this->modx, $ymCondition);
                $this->conditions[$condition->id] = $condition;
            }
        }
        return $this->conditions;
    }

    public function getFilePath(bool $withFile = false): string
    {
        $path = Service::preparePath($this->modx, $this->modx->getOption('yandexmarket2_files_path', null,
            '{assets_path}yandexmarket/'));
        if ($withFile) {
            $path .= $this->file;
        }

        return $path;
    }

    public function getFileUrl(bool $withFile = false): string
    {
        $url = Service::preparePath($this->modx, $this->modx->getOption('yandexmarket2_files_url', null,
            '{site_url}/{assets_url}/yandexmarket/'));
        if ($withFile) {
            $url .= $this->file;
        }

        return preg_replace('/(?<!:)\/+/', '/', $url);
    }

    public function toArray(bool $withValues = false): array
    {
        $data = parent::toArray();
        if (!empty($data['where']) && is_array($data['where'])) {
            $data['where'] = json_encode($data['where'], JSON_UNESCAPED_UNICODE);
        }

        $data['fileUrl'] = $this->getFileUrl(true);

        $data['conditions'] = array_map(static function (Condition $condition) {
            return $condition->toArray();
        }, array_values($this->getConditions()));

        if ($withValues) {
            $data['fields'] = array_map(static function (Field $field) {
                return $field->toArray();
            }, array_values($this->getFields()));

            $data['attributes'] = array_map(static function (Attribute $attribute) {
                return $attribute->toArray();
            }, array_values($this->getFieldsAttributes(array_keys($this->getFields()))));

            $data['categories'] = $this->selectedCategoriesId();
        }

        return $data;
    }

    /**
     * Генератор предложений для дальнейшей итерации
     *
     * @param  array  $config
     *
     * @return Offer[]|Generator
     */
    public function offersGenerator(array $config = [], bool $setCount = false): Generator
    {
        $query = $this->offersQuery($config);

        if ($setCount) {
            $countQuery = clone $query;
            $countQuery->query['limit'] = '';
            $countQuery->query['offset'] = '';
            $countQuery->query['orderby'] = [];
            $countQuery->query['sortby'] = [];
            $this->offersCount = $this->modx->getCount($countQuery->getClass(), $countQuery);
        }

        if ($this->modx->getOption('yandexmarket2_debug_mode')) {
            $query->prepare();
            $this->modx->log(modX::LOG_LEVEL_INFO, 'Offers SQL: '.$query->toSQL(true));
        }

        if ($this->reduceQueries) {
            $offers = new xPDOLazyIterator($this->modx, [
                'criteria'  => $query,
                'class'     => $query->getClass(),
                'cacheFlag' => true,
            ]);
        } else {
            $offers = $this->modx->getIterator($query->getClass(), $query);
        }

        foreach ($offers as $offer) {
            yield new Offer($this->modx, $offer);
        }
    }

    public function selectedCategoriesId(): array
    {
        $ids = [];
        $q = $this->modx->newQuery('ymCategory', ['pricelist_id' => $this->id]);
        $q->select("DISTINCT `ymCategory`.`resource_id`");
        $tstart = microtime(true);
        if ($q->prepare() && $q->stmt->execute()) {
            $this->modx->queryTime += microtime(true) - $tstart;
            $this->modx->executedQueries++;
            $ids = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return $ids;
    }

    /**
     * TODO: перенести в отдельный класс
     * Подготовка запроса для получения товаров
     *
     * @param  array  $config
     * @param  bool  $withCategoriesIn
     *
     * @return xPDOQuery
     */
    protected function offersQuery(array $config = [], bool $withCategoriesIn = true): xPDOQuery
    {
        $this->groupedBy = [];
        $q = $this->modx->newQuery($this->class);

        $offerColumns = $this->modx->getSelectColumns($q->getClass(), $q->getAlias());
        $q->select($offerColumns);
        // TODO: на будущее для интеграций пример SQL для получения постоянного ID предложения при группировках
        //CONCAT(`msProduct`.`id`, 'x', SUBSTR(md5(`option.color`.`value`), 1, 19 - LENGTH(`msProduct`.`id`))) as id

        $this->addColumnsToGroupBy($offerColumns);

        if ($withCategoriesIn) {
            $categoriesQuery = $this->categoriesQuery();
            if (!($categoriesQuery->usesOffersQuery ?? false)) {
                $categoriesQuery->query['columns'] = '';
                $categoriesQuery->select("DISTINCT `modResource`.`id`");
                $categoriesQuery->prepare();
                $q->where(sprintf("`%s`.`parent` IN (%s)", $q->getAlias(), $categoriesQuery->toSQL(true)));
            }
        }

        if (Service::hasMiniShop2()) {
            $q->join('msProductData', 'Data',
                mb_strtolower($this->class) === 'msproduct' ? xPDOQuery::SQL_JOIN_CROSS : xPDOQuery::SQL_JOIN_LEFT,
                sprintf('`Data`.`id` = `%s`.`id`', $q->getAlias()));
            $dataColumns = $this->modx->getSelectColumns('msProductData', 'Data', 'data.', ['id'], true);
            $q->select($dataColumns);
            $this->addColumnsToGroupBy($dataColumns);

            $q->leftJoin('msVendor', 'Vendor', "`Data`.`vendor` = `Vendor`.`id`");
            $vendorColumns = $this->modx->getSelectColumns('msVendor', 'Vendor', 'vendor.');
            $q->select($vendorColumns);
            $this->addColumnsToGroupBy($vendorColumns);
        }

        if ($externalClassKeys = $this->getExternalClassKeys()) {
            $this->joinExternalColumns($q, $externalClassKeys);
        }

        $this->addConditionsToQuery($q);

        if ($this->strictSql) {
            foreach ($this->groupedBy as $column) {
                $q->groupby($column);
            }
        } else {
            $pk = $this->modx->getPK($q->getClass());
            if (!is_array($pk)) {
                $pk = [$pk];
            }
            foreach ($pk as $primaryKey) {
                $q->groupby(sprintf('`%s`.`%s`', $q->getAlias(), $primaryKey));
            }
        }

        $q = $this->assignConfigToQuery($q, $config);

        $eventResponse = $this->modx->invokeEvent('ym2OnBeforeOffersQuery',
            ['q' => &$q, 'query' => &$q, 'pricelist' => &$this]);
        if (!empty($eventResponse)) {
            $this->offersPluginPrepared = true;
        }

        return $q;
    }

    /**
     * @param  string  $columns
     *
     * @return void
     */
    protected function addColumnsToGroupBy(string $columns)
    {
        foreach (explode(', ', $columns) as $column) {
            if (mb_strpos($column, ' AS ') !== false) {
                $column = explode(' AS ', $column)[0];
            }
            $this->groupedBy[] = $column;
        }
    }

    public function getExternalClassKeys(): array
    {
        $classKeys = [];
        foreach ($this->getFields(true) as $field) {
            if (in_array($field->type, [Field::TYPE_TEXT, Field::TYPE_CURRENCIES, Field::TYPE_CATEGORIES], true)) {
                continue;
            }

            $this->addClassKeyFromValue($field->value ?? '', $classKeys);
            $this->addClassKeyFromCodeHandler($field->handler ?? '', $classKeys);

            if ($attributes = $field->getAttributes()) {
                foreach ($attributes as $attribute) {
                    $this->addClassKeyFromValue($attribute->value ?? '', $classKeys);
                    $this->addClassKeyFromCodeHandler($attribute->handler ?? '', $classKeys);
                }
            }
        }

        if ($conditions = $this->getConditions()) {
            foreach ($conditions as $condition) {
                $this->addClassKeyFromValue($condition->column ?? '', $classKeys);
            }
        }

        return $classKeys;
    }

    /**
     * @param  xPDOQuery  $q
     * @param  array  $classKeys
     *
     * @return void
     */
    protected function joinExternalColumns(xPDOQuery $q, array $classKeys)
    {
        // TODO: залоггировать каждый join
        //TODO: подумать над тем, чтобы джойнить под одним псевдонимом, чтобы дваждый разнонаписанное ТВ не заджойнить
        foreach ($classKeys as $class => $keys) {
            switch (mb_strtolower($class)) {
                case 'tv':
                case 'modtemplatevar':
                case 'modtemplatevarresource':
                    $qTvs = $this->modx->newQuery('modTemplateVar');
                    $qTvs->where(['name:IN' => $keys]);
                    foreach ($this->modx->getIterator($qTvs->getClass(), $qTvs) as $tv) {
                        /** @var modTemplateVar $tv */
                        $field = sprintf('%s-%s', mb_strtolower($class), $tv->name);
                        $q->leftJoin('modTemplateVarResource', $field,
                            sprintf('`%s`.`contentid` = `%s`.`id` AND `%s`.`tmplvarid` = %d',
                                $field, $q->getAlias(), $field, $tv->id));
                        $q->select(sprintf('`%s`.`value` as `tv.%s`', $field, $tv->name));
                        $this->addColumnsToGroupBy(sprintf('`%s`.`value`', $field));
                    }
                    break;
                case 'option';
                case 'msoption';
                case 'msproductoption';
                    $qOptions = $this->modx->newQuery('msOption');
                    $qOptions->where(['key:IN' => $keys]);
                    foreach ($this->modx->getIterator($qOptions->getClass(), $qOptions) as $option) {
                        /** @var msOption $option */
                        $field = sprintf('%s-%s', mb_strtolower($class), $option->get('key'));
                        $q->leftJoin('msProductOption', $field,
                            sprintf("`%s`.`product_id` = `%s`.`id` AND `%s`.`key` = '%s'",
                                $field, $q->getAlias(), $field, $option->get('key')));
                        if (!in_array(sprintf('`%s`.`value`', $field), $this->groupedBy, true)
                            && in_array($option->get('type'), ['combo-multiple', 'combo-options'], true)) {
                            $q->select(sprintf("GROUP_CONCAT(DISTINCT `%s`.`value` SEPARATOR '||') as `option.%s`",
                                $field, $option->get('key')));
                        } else {
                            $q->select(sprintf("`%s`.`value` as `option.%s`", $field, $option->get('key')));
                            $this->addColumnsToGroupBy(sprintf('`%s`.`value`', $field));
                        }
                    }
                    foreach (['size', 'color', 'tags'] as $key) {
                        if (in_array($key, $keys, true)) {
                            $field = sprintf('%s-%s', mb_strtolower($class), $key);
                            $q->leftJoin('msProductOption', $field,
                                sprintf("`%s`.`product_id` = `%s`.`id` AND `%s`.`key` = '%s'",
                                    $field, $q->getAlias(), $field, $key));
                            $q->select(sprintf("GROUP_CONCAT(DISTINCT `%s`.`value` SEPARATOR '||') as `option.%s`",
                                $field, $key));
                        }
                    }
                    break;
                case 'msgallery':
                case 'msproductfile':
                    foreach ($keys as $key) {
                        $field = sprintf('%s-%s', mb_strtolower($class), $key);
                        $q->leftJoin('msProductFile', $field,
                            sprintf("`%s`.`product_id` = `%s`.`id` and `%s`.`parent` = 0 and `%s`.`type` = '%s'",
                                $field, $q->getAlias(), $field, $field, $key));
                        $q->select([
                            sprintf("SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `%s`.`url` ORDER BY `%s`.`rank` ASC SEPARATOR '||'), '||', 10) as `msgallery.%s`",
                                $field, $field, $key)
                        ]);
                    }
                    break;
                case 'ms2gallery':
                case 'msresourcefile':
                    if ($this->modx->addPackage('ms2gallery', MODX_CORE_PATH.'components/ms2gallery/model/')) {
                        foreach ($keys as $key) {
                            $field = sprintf('%s-%s', mb_strtolower($class), $key);
                            $q->leftJoin('msResourceFile', $field,
                                sprintf("`%s`.`resource_id` = `%s`.`id` and `%s`.`parent` = 0  and `%s`.`type` = '%s'",
                                    $field, $q->getAlias(), $field, $field, $key));
                            $q->select([
                                sprintf("SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `%s`.`url` ORDER BY `%s`.`rank` ASC SEPARATOR '||'), '||', 10) as `ms2gallery.%s`",
                                    $field, $field, $key)
                            ]);
                        }
                    } else {
                        $this->modx->log(modX::LOG_LEVEL_ERROR,
                            'Не удалось загрузить ms2Gallery. Проверьте настройки полей.', '', 'YandexMarket2');
                    }
                    break;
                case 'offer':
                case 'resource':
                case 'modresource':
                case 'product':
                case 'data':
                case 'msproduct':
                case 'msproductdata':
                case 'pricelist':
                case 'vendor':
                case 'msvendor':
                    //стандартные классы, не нужно ничего присоединять здесь
                    break;
                default:
                    // приджойнить выбранные столбцы других классов (с моделями что-то придумать нужно)
                    $this->modx->log(modX::LOG_LEVEL_ERROR,
                        'Неизвестный класс '.$class.'. Загрузите модель в своём плагине на событие ym2OnBeforeOffersQuery или обратитесь в поддержку.',
                        '', 'YandexMarket2');
                    break;
            }
        }
    }

    /**
     * @param  xPDOQuery  $q
     *
     * @return void
     */
    protected function addConditionsToQuery(xPDOQuery $q)
    {
        if ($conditions = $this->getConditions()) {
            foreach ($conditions as $condition) {
                if (mb_strpos($condition->column, '.') !== false) {
                    list($class, $key) = explode('.', $condition->column, 2);
                    switch (mb_strtolower($class)) {
                        case 'tv':
                        case 'modtemplatevar':
                        case 'modtemplatevarresource':
                        case 'option';
                        case 'msoption';
                        case 'msproductoption';
                            $column = sprintf('%s-%s.value', mb_strtolower($class), $key);
                            break;
                        case 'vendor':
                        case 'msvendor':
                            $column = "Vendor.{$key}"; //по столбцам
                            break;
                        case 'ms2gallery':
                        case 'msresourcefile':
                        case 'msproductfile':
                        case 'msgallery':
                            $column = sprintf('%s-%s.%s', mb_strtolower($class), $key, $key === 'image' ? 'url' : $key);
                            break;
                        default:
                            $column = $condition->column;
                    }
                } else {
                    $column = $condition->column;
                }

                if (!array_key_exists($condition->operator, Condition::OPERATOR_SYMBOLS)) {
                    $this->modx->log(modX::LOG_LEVEL_WARN, 'Неизвестный оператор для условия', '', 'YandexMarket2');
                    continue;
                }

                $operator = Condition::OPERATOR_SYMBOLS[$condition->operator];

                switch ($condition->operator) {
                    case 'exists in':
                    case 'not exists in':
                        $value = json_decode($condition->value, true, 512, JSON_UNESCAPED_UNICODE);
                        break;
                    case 'is null':
                    case 'is not null':
                        $operator = $condition->operator === 'is null' ? 'IS' : 'IS NOT';
                        $value = null;
                        break;
                    default:
                        $value = $condition->value;
                        break;
                }
                if ($operator === null) {
                    $operator = '';
                } else {
                    $operator = ':'.$operator;
                }
                $q->where([$column.$operator => $value]);
            }
        }
    }

    protected function addClassKeyFromValue(string $value, array &$classKeys): array
    {
        if (!empty($value) && mb_strpos($value, '.') !== false) {
            list($class, $key) = explode('.', $value, 2);
            if (!in_array($key, $classKeys[mb_strtolower($class)] ?? [], true)) {
                $classKeys[mb_strtolower($class)][] = $key;
            }
        }
        return $classKeys;
    }

    /**
     * Поиск столбцов по подобным Fenom конструкциям в коде {$Option.size}
     *
     * @param  string  $code
     * @param  array  $classKeys
     *
     * @return array
     */
    protected function addClassKeyFromCodeHandler(string $code, array &$classKeys): array
    {
        if (!empty($code) && preg_match_all('/{\$([A-z]+)\.([0-9A-z-_]+)[^}]*}/m', $code, $matches, PREG_SET_ORDER)) {
            foreach ($matches as list(, $class, $key)) {
                if (!in_array($key, $classKeys[mb_strtolower($class)] ?? [], true)) {
                    $classKeys[mb_strtolower($class)][] = $key;
                }
            }
        }
        return $classKeys;
    }

    public function newField(string $name, int $type = Field::TYPE_DEFAULT, bool $active = true): Field
    {
        $field = new Field($this->modx);
        $field->name = $name;
        $field->type = $type;
        $field->pricelist_id = $this->id;
        $field->created_on = new DateTimeImmutable();
        $field->active = $active;

        return $field;
    }

    public function newCondition(string $column, string $operator, $value): Condition
    {
        $condition = new Condition($this->modx);
        $condition->column = $column;
        $condition->operator = $operator;
        $condition->value = $value;
        $condition->pricelist_id = $this->id;

        return $condition;
    }

    /**
     * Уведомляем прайслист, что ресурс обновился
     * TODO: можно добавить проверку на изменение категорий
     *
     * @param  modResource  $resource
     */
    public function handleResourceChanges(modResource $resource)
    {
        $q = $this->offersQuery();
        $q->where([$q->getAlias().'.id' => $resource->id]);
        if (!$this->modx->getCount($q->getClass(), $this->id)) {
            //этого предложения нет в прайс-листе, пропускаем
            return;
        }

        if ($this->generate_mode === self::GENERATE_MODE_AFTER_SAVE) {
            $generator = new Generate($this, $this->modx);
            try {
                $generator->makeFile();
            } catch (Exception $e) {
                // не удалось сгенерировать файл
                $this->modx->log(modX::LOG_LEVEL_ERROR, '[YandexMarket2] '.$e->getMessage());
                $this->touch();
            }
        } else {
            $this->touch();
        }
    }
}