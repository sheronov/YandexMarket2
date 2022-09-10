<?php

namespace YandexMarket\Queries;

use MODX\Revolution\modTemplateVar;
use MODX\Revolution\modTemplateVarResource;
use MODX\Revolution\modResource;
use YandexMarket\Models\Field;
use YandexMarket\Service;

class OffersQuery extends ObjectsQuery
{

    /**
     * @param  \xPDO\om\xPDOQuery|\xPDOQuery  $query
     *
     * @return void
     */
    public function setCategoriesQuery($query)
    {
        $categoriesQuery = clone $query;
        $categoriesQuery->query['columns'] = '';
        $categoriesQuery->select(sprintf("DISTINCT `%s`.`id`", $query->getAlias()));
        $categoriesQuery->prepare();
        $this->query->where(sprintf("`%s`.`%s` IN (%s)", $this->query->getAlias(), $this->offerParentField,
            $categoriesQuery->toSQL(true)));
        $this->modx->log(Service::LOG_LEVEL_INFO,
            "Добавлено условие {$this->offerParentField} IN (select id from parentsQuery) для товаров", '',
            'YandexMarket2');
        $this->usesOtherQuery = true;
    }

    /**
     * @param  string  $class
     *
     * @return \xPDO\om\xPDOQuery|\xPDOQuery
     */
    protected function newQuery(string $class = '')
    {
        return parent::newQuery($this->pricelist->getClass());
    }

    protected function selectQuery()
    {
        parent::selectQuery();

        // TODO: на будущее для интеграций пример SQL для получения постоянного ID предложения при группировках
        //CONCAT(`msProduct`.`id`, 'x', SUBSTR(md5(`option.color`.`value`), 1, 19 - LENGTH(`msProduct`.`id`))) as id

        if (Service::hasMiniShop2()) {
            $this->query->join('msProductData', 'Data',
                //к продуктам данные всё равно джойним, даже если участвуют просто ресурсы
                mb_strtolower($this->pricelist->getClass()) === 'msproduct' ? 'JOIN' : 'LEFT JOIN',
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

        if (in_array('msop2', $this->pricelist->getModifiers(), true)) {
            $msop2Path = $this->modx->getOption('core_path', null, MODX_CORE_PATH).'components/msoptionsprice/model/';
            if ($this->modx->addPackage('msoptionsprice', $msop2Path)) {
                $this->query->leftJoin('msopModification', 'Modification',
                    sprintf('`Modification`.`rid` = `%s`.`id` and `Modification`.`active` = 1',
                        $this->query->getAlias()));
                $modificationColumns = $this->modx->getSelectColumns('msopModification', 'Modification',
                    'modification.');
                $this->query->select($modificationColumns);
                $this->addColumnsToGroupBy($modificationColumns);

                $this->join['Modification'] = true;

                $optionAlias = 'ModificationOption';
                $this->query->leftJoin('msopModificationOption', $optionAlias,
                    sprintf('`%s`.`mid` = `Modification`.`id`', $optionAlias));
                $this->query->select([
                    sprintf("CONCAT('{',GROUP_CONCAT(DISTINCT(CONCAT('\"',`%s`.`key`,'\":\"',`%s`.`value`,'\"'))),'}') as `modification.options`",
                        $optionAlias, $optionAlias)
                ]);

                $this->join[$optionAlias] = true;
            }
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
                    $this->query->leftJoin(Service::isMODX3() ? modResource::class : \modResource::class, 'Category',
                        sprintf('`Category`.`id` = `%s`.`%s`', $this->query->getAlias(), $this->offerParentField));
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
                    $this->query->leftJoin(Service::isMODX3() ? modResource::class : \modResource::class, 'Category',
                        sprintf('`Category`.`id` = `%s`.`%s`', $this->query->getAlias(), $this->offerParentField));
                    $this->join['Category'] = true;
                }
                $qTvs = $this->modx->newQuery(Service::isMODX3() ? modTemplateVar::class : \modTemplateVar::class, ['name:IN' => $keys]);
                foreach ($this->modx->getIterator($qTvs->getClass(), $qTvs) as $tv) {
                    /** @var modTemplateVar|\modTemplateVar $tv */
                    $alias = sprintf('CategoryTV-%s', $tv->name);
                    if (!isset($this->join[$alias])) {
                        $this->query->leftJoin(Service::isMODX3() ? modTemplateVarResource::class : \modTemplateVarResource::class,
                            $alias, sprintf('`%s`.`contentid` = `Category`.`id` AND `%s`.`tmplvarid` = %d',
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
