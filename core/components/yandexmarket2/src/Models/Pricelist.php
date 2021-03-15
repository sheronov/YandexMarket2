<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Generator;
use modTemplateVar;
use modX;
use msOption;
use PDO;
use xPDOObject;
use xPDOQuery;
use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Service;
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
 */
class Pricelist extends BaseObject
{
    const GENERATE_MODE_MANUALLY = 0;
    const GENERATE_MODE_AFTER_SAVE = 1;
    const GENERATE_MODE_CRON_ONLY = 2;

    /** @var Category[] */
    protected $categories;
    /** @var Condition[] */
    protected $conditions;
    /** @var Field[] */
    protected $fields;
    /** @var Attribute[] */
    protected $fieldsAttributes;

    /** @var null|Marketplace */
    protected $marketplace;

    protected $groupedBy = [];

    public function __construct(modX $modx, xPDOObject $object = null)
    {
        parent::__construct($modx, $object);
        $this->marketplace = Marketplace::getMarketPlace($this->type, $modx);
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

    public function getFields(bool $withAttributes = false): array
    {
        if (!isset($this->fields)) {
            $this->fields = [];
            foreach ($this->object->getMany('Fields') as $ymField) {
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

            uasort($this->fields, static function (Field $a, Field $b) {
                if ($a->rank === $b->rank) {
                    return 0;
                }
                return ($a->rank < $b->rank) ? -1 : 1;
            });

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

    public function getCategories(): array
    {
        if (!isset($this->categories)) {
            $this->categories = [];
            foreach ($this->object->getMany('Categories') as $ymCategory) {
                $category = new Category($this->modx, $ymCategory);
                $this->categories[$category->id] = $category;
            }
        }
        return $this->categories;
    }

    public function getConditions(): array
    {
        if (!isset($this->conditions)) {
            $this->conditions = [];
            foreach ($this->object->getMany('Conditions') as $ymCondition) {
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

        $data['path'] = $this->getFilePath();
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

            $data['categories'] = array_map(static function (Category $categoryObject) {
                return $categoryObject->resource_id;
            }, array_values($this->getCategories()));
        }

        return $data;
    }

    /**
     * Генератор предложений для дальнейшей итерации
     *
     * @param  array  $config
     *
     * @return Generator
     */
    public function offersGenerator(array $config = []): Generator
    {
        $query = $this->queryForOffers();

        if ($sortBy = $config['sortBy'] ?? null) {
            $query->sortby($sortBy, $config['sortDir'] ?? 'ASC');
        }

        if ($limit = $config['limit'] ?? null) {
            $query->limit($limit, $config['offset'] ?? 0);
        }

        $offers = $this->modx->getIterator($query->getClass(), $query);
        foreach ($offers as $offer) {
            yield new Offer($this->modx, $offer);
        }
    }

    /**
     * Удобный метод для получения количества предложений
     *
     * @return int
     */
    public function offersCount(): int
    {
        $query = $this->queryForOffers();
        return $this->modx->getCount($query->getClass(), $query);
    }

    /**
     * Подготовка запроса для получения товаров
     *
     * @return xPDOQuery
     */
    protected function queryForOffers(): xPDOQuery
    {
        $q = $this->modx->newQuery($this->class);

        $offerColumns = $this->modx->getSelectColumns($q->getClass(), $q->getClass());
        $q->select($offerColumns);
        // TODO: на будущее для интеграций пример SQL для получения постоянного ID предложения при группировках
        //CONCAT(`msProduct`.`id`, 'x', SUBSTR(md5(`option.color`.`value`), 1, 19 - LENGTH(`msProduct`.`id`))) as id

        $this->addColumnsToGroupBy($offerColumns);

        if (!empty($this->getCategories())) {
            $q->where([
                'parent:IN' => array_map(static function (Category $category) {
                    return $category->resource_id;
                }, $this->getCategories())
            ]);
        }

        if (Service::hasMiniShop2()) {
            if (mb_strtolower($this->class) === mb_strtolower('msProduct')) {
                $q->join('msProductData', 'Data');
            } else {
                $q->leftJoin('msProductData', 'Data');
            }
            $dataColumns = $this->modx->getSelectColumns('msProductData', 'Data', '', ['id'], true);
            $q->select($dataColumns);
            $this->addColumnsToGroupBy($dataColumns);
        }

        if ($externalClassKeys = $this->getExternalClassKeys()) {
            $this->joinExternalColumns($q, $externalClassKeys);
        }

        $this->addConditionsToQuery($q);

        foreach ($this->groupedBy as $column) {
            $q->groupby($column);
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
        foreach ($classKeys as $class => $keys) {
            switch (mb_strtolower($class)) {
                case 'vendor':
                case 'msvendor':
                    $alias = $class;
                    $q->leftJoin('msVendor', $alias, "`Data`.`vendor` = `$alias`.`id`");
                    $columns = $this->modx->getSelectColumns('msVendor', $alias, $alias.'.', $keys, false);
                    $q->select($columns);
                    $this->addColumnsToGroupBy($columns);
                    break;
                case 'tv':
                case 'modtemplatevar':
                case 'modtemplatevarresource':
                    $qTvs = $this->modx->newQuery('modTemplateVar');
                    $qTvs->where(['name:IN' => $keys]);
                    foreach ($this->modx->getIterator($qTvs->getClass(), $qTvs) as $tv) {
                        /** @var modTemplateVar $tv */
                        $alias = "`{$class}-{$tv->name}`";
                        $q->leftJoin('modTemplateVarResource', $alias,
                            "{$alias}.`contentid` = `{$q->getClass()}`.`id` and {$alias}.`tmplvarid` = {$tv->get('id')}");
                        $q->select("{$alias}.`value` as {$alias}");
                        $this->groupedBy[] = "{$alias}.`value`";
                    }
                    break;
                case 'option';
                case 'msoption';
                case 'msproductoption';
                    $qOptions = $this->modx->newQuery('msOption');
                    $qOptions->where(['key:IN' => $keys]);
                    foreach ($this->modx->getIterator($qOptions->getClass(), $qOptions) as $option) {
                        /** @var msOption $option */
                        $alias = "`{$class}-{$option->get('key')}`";
                        $q->leftJoin('msProductOption', $alias,
                            "{$alias}.`product_id` = `{$q->getClass()}`.`id` and {$alias}.`key` = '{$option->get('key')}'");
                        if (!in_array("{$alias}.`value`", $this->groupedBy, true)
                            && in_array($option->get('type'), ['combo-multiple', 'combo-options'], true)) {
                            $q->select("GROUP_CONCAT(DISTINCT {$alias}.`value` SEPARATOR '||') as {$alias}");
                        } else {
                            $q->select("{$alias}.`value` as {$alias}");
                            $this->groupedBy[] = "{$alias}.`value`";
                        }
                    }
                    foreach (['size', 'color', 'tags'] as $key) {
                        if (in_array($key, $keys, true)) {
                            $alias = "`{$class}-{$key}`";
                            $q->leftJoin('msProductOption', $alias,
                                "{$alias}.`product_id` = `{$q->getClass()}`.`id` and {$alias}.`key` = '{$key}'");
                            $q->select("GROUP_CONCAT(DISTINCT {$alias}.`value` SEPARATOR '||') as {$alias}");
                        }
                    }
                    break;
                case 'msgallery':
                case 'msproductfile':
                    foreach ($keys as $key) {
                        $alias = "`$class-$key`";
                        $q->leftJoin('msProductFile', $alias,
                            "{$alias}.`product_id` = `{$q->getClass()}`.`id` and {$alias}.`parent` = 0 and {$alias}.`type` = '{$key}'");
                        $q->select(["SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT {$alias}.`url` ORDER BY {$alias}.`rank` ASC SEPARATOR '||'), '||', 10) as {$alias}"]);
                    }
                    break;
                case 'ms2gallery':
                case 'msresourcefile':
                    foreach ($keys as $key) {
                        $alias = "`$class-$key`";
                        $q->leftJoin('msResourceFile', $alias,
                            "{$alias}.`resource_id` = `{$q->getClass()}`.`id` and {$alias}.`parent` = 0  and {$alias}.`type` = '{$key}'");
                        $q->select(["SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT {$alias}.`url` ORDER BY {$alias}.`rank` ASC SEPARATOR '||'), '||', 10) as {$alias}"]);
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
                    //стандартные классы, не нужно ничего присоединять здесь
                    break;
                default:
                    // приджойнить выбранные столбцы других классов (с моделями что-то придумать нужно)
                    $this->modx->log(modX::LOG_LEVEL_ERROR,
                        '[YandexMarket2] Unimplemented class '.$class.'. Please contact to the support.');
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
                            $column = mb_strtolower($class)."-{$key}.value";
                            break;
                        case 'vendor':
                        case 'msvendor':
                            $column = mb_strtolower($class).'.'.$key;
                            break;
                        case 'msgallery':
                        case 'ms2gallery':
                        case 'msproductfile':
                        case 'msresourcefile':
                            $column = mb_strtolower($class)."-{$key}.".($key === 'image' ? 'url' : $key);
                            break;
                        default:
                            $column = $condition->column;
                    }
                } else {
                    $column = $condition->column;
                }

                if (!array_key_exists($condition->operator, Condition::OPERATOR_SYMBOLS)) {
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
     * Если же категории не выбраны - нужно вернуть все те, что в товарах участвуют (а не все сайта)
     * Снова генератор для уменьшения памяти
     *
     * @return Generator
     */
    public function suitableOffersCategoriesGenerator(): Generator
    {
        $parentIds = [];
        $q = $this->queryForOffers();
        $q->query['columns'] = '';
        $q->select('DISTINCT `'.$q->getClass().'`.`parent`');

        if ($q->prepare() && $q->stmt->execute()) {
            $parentIds = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        if (!empty($parentIds)) {
            $q = $this->modx->newQuery('modResource');
            $q->sortby('parent');
            $q->sortby('id');
            $q->where(['id:IN' => $parentIds]);
            foreach ($this->modx->getIterator('modResource', $q) as $resource) {
                $category = new Category($this->modx);
                $category->setResource($resource);
                yield $category;
            }
        }
    }

    public function isOfferFits(int $offerId): bool
    {
        $q = $this->queryForOffers();
        $q->where(['id' => $offerId]);
        return $this->modx->getCount($q->getClass(), $q) > 0;
    }
}