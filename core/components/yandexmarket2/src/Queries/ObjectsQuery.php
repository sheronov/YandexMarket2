<?php

namespace YandexMarket\Queries;

use modTemplateVar;
use modX;
use msOption;
use xPDOQuery;
use YandexMarket\Models\Attribute;
use YandexMarket\Models\Condition;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

abstract class ObjectsQuery
{
    /** @var xPDOQuery $query */
    protected $query;
    protected $classKeys       = [];
    protected $join            = []; // TODO: как значение сделать не true - а класс
    protected $groupBy         = [];
    protected $limit           = 0;
    protected $offset          = 0;
    protected $count           = null;
    protected $plugins         = false;
    protected $usesOtherQuery  = false;
    protected $hasCodeHandlers = false;

    protected $pricelist;
    protected $modx;

    protected $strictSql     = true;
    protected $reduceQueries = false;
    protected $debugMode     = false;

    protected $queryPrepared = false;

    protected $type;
    protected $offerParentField = 'parent';

    public function __construct(Pricelist $pricelist, modX $modx)
    {
        $this->pricelist = $pricelist;
        $this->modx = $modx;

        $this->offerParentField = $this->modx->getOption(sprintf('yandexmarket2_%s_parent_field',
            mb_strtolower($this->pricelist->getClass())), null, 'parent'); //у разных объектов свои категории
        $this->strictSql = $this->modx->getOption('yandexmarket2_strict_sql', null, false);
        $this->reduceQueries = $this->modx->getOption('yandexmarket2_reduce_queries', null, false);
        $this->debugMode = $this->modx->getOption('yandexmarket2_debug_mode', null, false);
        $this->type = str_replace('YandexMarket\\Queries\\', '', get_class($this));
        $this->resetQuery();
    }

    public function resetQuery()
    {
        $this->classKeys = [];
        $this->join = [];
        $this->groupBy = [];
        $this->limit = 0;
        $this->offset = 0;
        $this->count = null;
        $this->plugins = false;
        $this->usesOtherQuery = false;
        $this->hasCodeHandlers = false;
        $this->query = $this->newQuery();
    }

    public function getQuery(): xPDOQuery
    {
        if (!$this->queryPrepared) {
            $this->selectQuery();

            $this->joinExternalColumnsToQuery();

            $this->addConditionsToQuery();
            $this->groupQuery();
            $this->limitQuery();

            $this->afterQuery();

            $this->queryPrepared = true;
        }
        return $this->query;
    }

    public function getCount(): int
    {
        if (!isset($this->count)) {
            $countQuery = clone $this->getQuery();
            $countQuery->query['limit'] = '';
            $countQuery->query['offset'] = '';
            $countQuery->query['orderby'] = [];
            $countQuery->query['sortby'] = [];
            $this->count = $this->modx->getCount($countQuery->getClass(), $countQuery);
        }
        return $this->count;
    }

    public function getAlias(): string
    {
        return $this->query->getAlias();
    }

    public function hasPlugins(): bool
    {
        return $this->plugins;
    }

    public function hasCodeHandlers(): bool
    {
        return $this->hasCodeHandlers;
    }

    public function isUsesOtherQuery(): bool
    {
        return $this->usesOtherQuery;
    }

    public function setOrder(string $by, string $dir = 'ASC')
    {
        $this->query->sortby($by, $dir);
    }

    public function setLimit(int $limit = 0, int $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
    }

    protected function selectQuery()
    {
        $columns = $this->modx->getSelectColumns($this->query->getClass(), $this->query->getAlias());
        $this->query->select($columns);
        $this->addColumnsToGroupBy($columns);
    }

    protected function newQuery(string $class = 'modResource'): xPDOQuery
    {
        $this->modx->invokeEvent('ym2OnBeforeQuery', [
            'pricelist' => &$this->pricelist,
            'class'     => $class,
            'type'      => $this->type,
        ]);
        return $this->modx->newQuery($class);
    }

    protected function afterQuery()
    {
    }

    protected function joinExternalColumnsToQuery()
    {
        $classKeys = $this->collectExternalClassKeys();
        foreach ($classKeys as $class => $keys) {
            $this->joinExternalClassKey($class, $keys);
            $this->modx->log(modX::LOG_LEVEL_INFO,
                sprintf('Приджойнена таблица `%s` со столбцами "%s" к %s', $class, implode(',', $keys), $this->type),
                '', 'YandexMarket2');
        }
    }

    protected function limitQuery()
    {
        if ($this->limit || $this->offset) {
            $this->query->limit($this->limit, $this->offset);
        }
    }

    protected function groupQuery()
    {
        if ($this->strictSql) {
            foreach ($this->groupBy as $column) {
                $this->query->groupby($column);
            }
        } else {
            $pk = $this->modx->getPK($this->query->getClass());
            if (!is_array($pk)) {
                $pk = [$pk];
            }
            foreach ($pk as $primaryKey) {
                $this->query->groupby(sprintf('`%s`.`%s`', $this->query->getAlias(), $primaryKey));
            }

            if (isset($this->join['Modification']) && in_array('msop2', $this->pricelist->getModifiers(), true)) {
                $pkMsOp2 = $this->modx->getPK('msopModification');
                if (!is_array($pkMsOp2)) {
                    $pkMsOp2 = [$pkMsOp2];
                }
                foreach ($pkMsOp2 as $primaryKey) {
                    $this->query->groupby(sprintf('`%s`.`%s`', 'Modification', $primaryKey));
                }
            }
        }
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
            $this->groupBy[] = $column;
        }
    }

    /**
     * @return array|Field[]
     */
    protected function getFields(): array
    {
        return [];
    }

    protected function collectExternalClassKeys(): array
    {
        foreach ($this->getFields() as $field) {
            if ($attributes = $field->getAttributes()) {
                foreach ($attributes as $attribute) {
                    if ($attribute->type === Attribute::TYPE_TEXT) {
                        continue;
                    }
                    $this->addClassKeyFromValue($attribute->value ?? '');
                    $this->addClassKeyFromCodeHandler($attribute->handler ?? '');
                }
            }

            if ($field->type === Field::TYPE_TEXT) {
                continue;
            }
            $this->addClassKeyFromValue($field->value ?? '');
            $this->addClassKeyFromCodeHandler($field->handler ?? '');
        }

        if ($conditions = $this->getConditions()) {
            foreach ($conditions as $condition) {
                $this->addClassKeyFromValue($condition->column ?? '');
            }
        }

        return $this->classKeys;
    }

    /**
     * Обработка выбранных столбцов {$Option.size}
     */
    protected function addClassKeyFromValue(string $value)
    {
        if (!empty($value) && mb_strpos($value, '.') !== false) {
            list($class, $key) = explode('.', $value, 2);
            if (!in_array($key, $this->classKeys[mb_strtolower($class)] ?? [], true)) {
                $this->classKeys[mb_strtolower($class)][] = $key;
            }
        }
    }

    /**
     * Поиск столбцов по Fenom конструкциям в коде, пример {$Option.size}
     */
    protected function addClassKeyFromCodeHandler(string $code)
    {
        if (!empty($code)) {
            $this->hasCodeHandlers = true;
            if (preg_match_all('/\$(?>_pls\[[\"\'])?([0-9A-z_]+)\.([0-9A-z-_]+)\b/m', $code, $matches,
                PREG_SET_ORDER)) {
                foreach ($matches as list(, $class, $key)) {
                    if (!in_array($key, $this->classKeys[mb_strtolower($class)] ?? [], true)) {
                        $this->classKeys[mb_strtolower($class)][] = $key;
                    }
                }
            }
        }
    }

    /**
     * @return array|Condition[]
     */
    protected function getConditions(): array
    {
        return [];
    }

    /**
     * @param  string  $class
     * @param  array  $keys
     */
    protected function joinExternalClassKey(string $class, array $keys)
    {
        switch (mb_strtolower($class)) {
            case 'tv':
            case 'modtemplatevar':
            case 'modtemplatevarresource':
                $qTvs = $this->modx->newQuery('modTemplateVar');
                $qTvs->where(['name:IN' => $keys]);
                foreach ($this->modx->getIterator($qTvs->getClass(), $qTvs) as $tv) {
                    /** @var modTemplateVar $tv */
                    $alias = sprintf('TV-%s', $tv->name);
                    if (!isset($this->join[$alias])) {
                        $this->query->leftJoin('modTemplateVarResource', $alias,
                            sprintf('`%s`.`contentid` = `%s`.`id` AND `%s`.`tmplvarid` = %d',
                                $alias, $this->query->getAlias(), $alias, $tv->id));
                        $this->query->select(sprintf('`%s`.`value` as `tv.%s`', $alias, $tv->name));
                        $this->addColumnsToGroupBy(sprintf('`%s`.`value`', $alias));
                        $this->join[$alias] = true;
                    }
                }
                break;
            case 'option';
            case 'msoption';
            case 'msproductoption';
                $qOptions = $this->modx->newQuery('msOption');
                $qOptions->where(['key:IN' => $keys]);
                foreach ($this->modx->getIterator($qOptions->getClass(), $qOptions) as $option) {
                    /** @var msOption $option */
                    $alias = sprintf('Option-%s', $option->get('key'));
                    if (!isset($this->join[$alias])) {
                        $this->query->leftJoin('msProductOption', $alias,
                            sprintf("`%s`.`product_id` = `%s`.`id` AND `%s`.`key` = '%s'",
                                $alias, $this->query->getAlias(), $alias, $option->get('key')));
                        if (!in_array(sprintf('`%s`.`value`', $alias), $this->groupBy, true)
                            && in_array($option->get('type'), ['combo-multiple', 'combo-options'], true)) {
                            $this->query->select(sprintf("GROUP_CONCAT(DISTINCT `%s`.`value` SEPARATOR '||') as `option.%s`",
                                $alias, $option->get('key')));
                        } else {
                            $this->query->select(sprintf("`%s`.`value` as `option.%s`", $alias, $option->get('key')));
                            $this->addColumnsToGroupBy(sprintf('`%s`.`value`', $alias));
                        }
                        $this->join[$alias] = true;
                    }
                }
                foreach (['size', 'color', 'tags'] as $key) {
                    if (in_array($key, $keys, true)) {
                        $alias = sprintf('%s-%s', 'Option', $key);
                        if (!isset($this->join[$alias])) {
                            $this->query->leftJoin('msProductOption', $alias,
                                sprintf("`%s`.`product_id` = `%s`.`id` AND `%s`.`key` = '%s'",
                                    $alias, $this->query->getAlias(), $alias, $key));
                            $this->query->select(sprintf("GROUP_CONCAT(DISTINCT `%s`.`value` SEPARATOR '||') as `option.%s`",
                                $alias, $key));
                            $this->join[$alias] = true;
                        }
                    }
                }
                break;
            case 'msgallery':
            case 'msproductfile':
                foreach ($keys as $key) {
                    $alias = sprintf('%s-%s', 'msGallery', $key);
                    if (!isset($this->join[$alias])) {
                        $this->query->leftJoin('msProductFile', $alias,
                            sprintf("`%s`.`product_id` = `%s`.`id` and `%s`.`parent` = 0 and `%s`.`type` = '%s'",
                                $alias, $this->query->getAlias(), $alias, $alias, $key));
                        $this->query->select([
                            sprintf("SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `%s`.`url` ORDER BY `%s`.`rank` ASC SEPARATOR '||'), '||', 10) as `msgallery.%s`",
                                $alias, $alias, $key)
                        ]);
                        $this->join[$alias] = true;
                    }
                }
                break;
            case 'ms2gallery':
            case 'msresourcefile':
                if (!$this->modx->addPackage('ms2gallery', MODX_CORE_PATH.'components/ms2gallery/model/')) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR,
                        'Не удалось загрузить ms2Gallery. Проверьте настройки полей.', '', 'YandexMarket2');
                    break;
                }
                foreach ($keys as $key) {
                    $alias = sprintf('%s-%s', 'ms2Gallery', $key);
                    if (!isset($this->join[$alias])) {
                        $this->query->leftJoin('msResourceFile', $alias,
                            sprintf("`%s`.`resource_id` = `%s`.`id` and `%s`.`parent` = 0  and `%s`.`type` = '%s'",
                                $alias, $this->query->getAlias(), $alias, $alias, $key));
                        $this->query->select([
                            sprintf("SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT `%s`.`url` ORDER BY `%s`.`rank` ASC SEPARATOR '||'), '||', 10) as `ms2gallery.%s`",
                                $alias, $alias, $key)
                        ]);
                        $this->join[$alias] = true;
                    }
                }
                break;
            case 'msop2':
            case 'modification':
            case 'msopmodification':
                // TODO: вообще к этому моменту приджойнено, но вполне может быть неактивна настрока
                if(empty($this->join['Modification'])) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR,
                        'Для модификаций msOptionsPrice2 активируйте настройку yandexmarket2_msop2_integration');
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

    /**
     * TODO: когда-нибудь сделать более логично, чтобы не дублировать switch с методов выше
     */
    protected function addConditionsToQuery()
    {
        if ($conditions = $this->getConditions()) {
            foreach ($conditions as $condition) {
                if (mb_strpos($condition->column, '.') !== false) {
                    list($class, $key) = explode('.', $condition->column, 2);
                    switch (mb_strtolower($class)) {
                        case 'tv':
                        case 'modtemplatevar':
                        case 'modtemplatevarresource':
                            $column = sprintf('TV-%s.value', $key);
                            break;
                        case 'option';
                        case 'msoption';
                        case 'msproductoption';
                            $column = sprintf('Option-%s.value', $key);
                            break;
                        case 'vendor':
                        case 'msvendor':
                            $column = sprintf("Vendor.%s", $key); //по столбцам
                            break;
                        case 'ms2gallery':
                        case 'msresourcefile':
                            $column = sprintf('ms2Gallery-%s.%s', $key, $key === 'image' ? 'url' : $key);
                            break;
                        case 'msproductfile':
                        case 'msgallery':
                            $column = sprintf('msGallery-%s.%s', $key, $key === 'image' ? 'url' : $key);
                            break;
                        case 'msop2':
                        case 'modification':
                        case 'msopmodification':
                            $column = sprintf('Modification.%s', $key);
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
                $this->query->where([$column.$operator => $value]);
            }
        }
    }
}
