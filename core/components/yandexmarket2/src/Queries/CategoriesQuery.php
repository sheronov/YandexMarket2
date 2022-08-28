<?php

namespace YandexMarket\Queries;

use MODX\Revolution\modX;
use xPDO\om\xPDOQuery;
use YandexMarket\Model\YmCategory;
use YandexMarket\Models\Field;

class CategoriesQuery extends ObjectsQuery
{
    public function setOffersQuery(xPDOQuery $query)
    {
        $offersQuery = clone $query;
        $offersQuery->query['columns'] = '';
        $offersQuery->query['groupby'] = '';
        $offersQuery->select(sprintf('DISTINCT `%s`.`%s`', $offersQuery->getAlias(), $this->offerParentField));
        $offersQuery->prepare();
        $this->query->where(sprintf('`modResource`.`id` IN (%s)', $offersQuery->toSQL(true)));
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Добавлено условие id IN (offers ids) для категорий', '',
            'YandexMarket2');
        $this->usesOtherQuery = true;
    }

    protected function selectQuery()
    {
        parent::selectQuery();
        if ($this->modx->getCount(YmCategory::class, ['pricelist_id' => $this->pricelist->id])) {
            $this->query->innerJoin(YmCategory::class, 'Category', 'Category.resource_id = modResource.id');
            $this->query->select($this->modx->getSelectColumns(YmCategory::class, 'Category', 'category_'));
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
