<?php

class ymCategoryCreateProcessor extends modObjectCreateProcessor
{
    public $objectType     = 'ym_category';
    public $classKey       = ymCategory::class;
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
            $this->modx->error->addField('resource_id', $this->modx->lexicon('ym_category_err_id'));
        } elseif ($this->modx->getCount($this->classKey, [
            'resource_id'  => $resourceId,
            'pricelist_id' => $pricelistId
        ])) {
            $this->modx->error->addField('resource_id', $this->modx->lexicon('ym_category_err_ae'));
        }

        return parent::beforeSet();
    }

}

return ymCategoryCreateProcessor::class;