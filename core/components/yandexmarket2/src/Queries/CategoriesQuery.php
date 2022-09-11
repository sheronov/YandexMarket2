<?php

namespace YandexMarket\Queries;

use YandexMarket\Models\Category;
use YandexMarket\Models\Field;
use YandexMarket\Service;

class CategoriesQuery extends ObjectsQuery
{
    /**
     * @param  \xPDO\om\xPDOQuery|\xPDOQuery  $query
     *
     * @return void
     */
    public function setOffersQuery($query)
    {
        $offersQuery = clone $query;
        $offersQuery->query['columns'] = '';
        $offersQuery->query['groupby'] = '';
        $offersQuery->select(sprintf('DISTINCT `%s`.`%s`', $offersQuery->getAlias(), $this->offerParentField));
        $offersQuery->prepare();
        $this->query->where(sprintf('`modResource`.`id` IN (%s)', $offersQuery->toSQL(true)));
        $this->modx->log(Service::LOG_LEVEL_INFO, $this->modx->lexicon('ym2_debug_add_offers_condition'), '',
            'YandexMarket2');
        $this->usesOtherQuery = true;
    }

    protected function selectQuery()
    {
        parent::selectQuery();
        if ($this->modx->getCount(Category::getObjectClass(), ['pricelist_id' => $this->pricelist->id])) {
            $this->query->innerJoin(Category::getObjectClass(), 'Category', 'Category.resource_id = modResource.id');
            $this->query->select($this->modx->getSelectColumns(Category::getObjectClass(), 'Category', 'category_'));
            $this->query->where(['Category.pricelist_id' => $this->pricelist->id]);
        }
    }

    protected function afterQuery()
    {
        parent::afterQuery();
        $eventResponse = $this->modx->invokeEvent('ym2OnCategoriesQuery',
            ['q' => &$this->query, 'query' => &$this->query, 'pricelist' => &$this]);
        if (!empty($eventResponse)) {
            $this->plugins = true;
        }
    }

    protected function getFields(): array
    {
        $fields = $this->pricelist->getFields(true);

        return array_filter($fields, function (Field $field) {
            return in_array($field->type, [
                Field::TYPE_CATEGORIES,
                Field::TYPE_CATEGORY,
            ], true);
        });
    }
}
