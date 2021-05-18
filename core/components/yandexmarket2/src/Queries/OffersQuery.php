<?php

namespace YandexMarket\Queries;

use modTemplateVar;
use modX;
use xPDOQuery;
use YandexMarket\Models\Field;
use YandexMarket\Service;

class OffersQuery extends ObjectsQuery
{

    public function setCategoriesQuery(xPDOQuery $query)
    {
        $categoriesQuery = clone $query;
        $categoriesQuery->query['columns'] = '';
        $categoriesQuery->select("DISTINCT `modResource`.`id`");
        $categoriesQuery->prepare();
        $this->query->where(sprintf("`%s`.`parent` IN (%s)", $this->query->getAlias(), $categoriesQuery->toSQL(true)));
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Добавлено условие parent IN (parents ids) для товаров', '',
            'YandexMarket2');
        $this->usesOtherQuery = true;
    }

    protected function newQuery(string $class = 'modResource'): xPDOQuery
    {
        return parent::newQuery($this->pricelist->class);
    }

    protected function selectQuery()
    {
        parent::selectQuery();

        // TODO: на будущее для интеграций пример SQL для получения постоянного ID предложения при группировках
        //CONCAT(`msProduct`.`id`, 'x', SUBSTR(md5(`option.color`.`value`), 1, 19 - LENGTH(`msProduct`.`id`))) as id

        if (Service::hasMiniShop2()) {
            $this->query->join('msProductData', 'Data',
                //к продуктам данные всё равно джойним, даже если участвуют просто ресурсы
                mb_strtolower($this->pricelist->class) === 'msproduct' ? xPDOQuery::SQL_JOIN_CROSS : xPDOQuery::SQL_JOIN_LEFT,
                sprintf('`Data`.`id` = `%s`.`id`', $this->query->getAlias()));
            $dataColumns = $this->modx->getSelectColumns('msProductData', 'Data', 'data.', ['id'], true);
            $this->query->select($dataColumns);
            $this->addColumnsToGroupBy($dataColumns);
            $this->join['Data'] = true;

            //может и не нужно все колонки дёргать
            $this->query->leftJoin('msVendor', 'Vendor', "`Data`.`vendor` = `Vendor`.`id`");
            $vendorColumns = $this->modx->getSelectColumns('msVendor', 'Vendor', 'vendor.');
            $this->query->select($vendorColumns);
            $this->addColumnsToGroupBy($vendorColumns);
            $this->join['Vendor'] = true;
        }
    }

    protected function afterQuery()
    {
        parent::afterQuery();
        $eventResponse = $this->modx->invokeEvent('ym2OnOffersQuery',
            ['q' => &$this->query, 'query' => &$this->query, 'pricelist' => &$this->pricelist]);
        if (!empty($eventResponse)) {
            $this->plugins = true;
        }
    }

    protected function getConditions(): array
    {
        return $this->pricelist->getConditions();
    }

    protected function getFields(): array
    {
        $fields = $this->pricelist->getFields(true);

        return array_filter($fields, function (Field $field) {
            return !in_array($field->type, [
                Field::TYPE_TEXT,
                Field::TYPE_CURRENCIES,
                Field::TYPE_CATEGORIES,
                Field::TYPE_CATEGORY,
                Field::TYPE_ROOT,
                Field::TYPE_SHOP,
            ], true);
        });
    }

    protected function joinExternalClassKey(string $class, array $keys)
    {
        switch (mb_strtolower($class)) {
            case 'category':
            case 'parent':
                if (!isset($this->join['Category'])) {
                    $this->query->leftJoin('modResource', 'Category',
                        sprintf('`Category`.`id` = `%s`.`parent`', $this->query->getAlias()));
                    $this->join['Category'] = true;
                }
                foreach ($keys as $key) {
                    $this->query->select(sprintf("`Category`.`%s` as `category.%s`", $key, $key));
                    $this->addColumnsToGroupBy(sprintf('`Category`.`%s`', $key));
                }
                break;
            case 'categorytv':
            case 'parenttv':
                if (!isset($this->join['Category'])) {
                    $this->query->leftJoin('modResource', 'Category',
                        sprintf('`Category`.`id` = `%s`.`parent`', $this->query->getAlias()));
                    $this->join['Category'] = true;
                }
                $qTvs = $this->modx->newQuery('modTemplateVar', ['name:IN' => $keys]);
                foreach ($this->modx->getIterator($qTvs->getClass(), $qTvs) as $tv) {
                    /** @var modTemplateVar $tv */
                    $alias = sprintf('CategoryTV-%s', $tv->name);
                    if (!isset($this->join[$alias])) {
                        $this->query->leftJoin('modTemplateVarResource', $alias,
                            sprintf('`%s`.`contentid` = `Category`.`id` AND `%s`.`tmplvarid` = %d',
                                $alias, $alias, $tv->id));
                        $this->query->select(sprintf('`%s`.`value` as `categorytv.%s`', $alias, $tv->name));
                        $this->addColumnsToGroupBy(sprintf('`%s`.`value`', $alias));
                        $this->join[$alias] = true;
                    }
                }
                break;
            default:
                parent::joinExternalClassKey($class, $keys);
        }
    }

}