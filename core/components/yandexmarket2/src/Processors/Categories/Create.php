<?php

namespace YandexMarket\Processors\Categories;

use MODX\Revolution\Processors\Model\CreateProcessor;
use YandexMarket\Model\YmCategory;

class Create extends CreateProcessor
{
    public $objectType     = 'ym2_category';
    public $classKey       = YmCategory::class;
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'create';

    /**
     * @return bool
     */
    public function beforeSet(): bool
    {
        $pricelistId = (int)($this->getProperty('pricelist_id', 0)) ?: null;
        $resourceId = (int)($this->getProperty('resource_id', 0));

        if (!$resourceId) {
            $this->modx->error->addField('resource_id', $this->modx->lexicon('ym2_category_err_nf'));
        } elseif ($this->modx->getCount($this->classKey, [
            'resource_id'  => $resourceId,
            'pricelist_id' => $pricelistId
        ])) {
            $this->modx->error->addField('resource_id', $this->modx->lexicon('ym2_category_err_ae'));
        }

        return parent::beforeSet();
    }

}
