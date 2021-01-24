<?php

class ymCategoryCreateProcessor extends modObjectCreateProcessor
{
    public $objectType     = 'ym_category';
    public $classKey       = 'ymCategory';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'create';

    /**
     * @return bool
     */
    public function beforeSet(): bool
    {
        $pricelistId = (int)($this->getProperty('pricelist_id', 0)) ?: null;
        $categoryId = (int)($this->getProperty('category_id', 0));

        if (!$categoryId) {
            $this->modx->error->addField('category_id', $this->modx->lexicon('yandexmarket2_category_err_id'));
        } elseif ($this->modx->getCount($this->classKey, [
            'category_id'  => $categoryId,
            'pricelist_id' => $pricelistId
        ])) {
            $this->modx->error->addField('category_id', $this->modx->lexicon('yandexmarket2_category_err_ae'));
        }

        return parent::beforeSet();
    }

}

return ymCategoryCreateProcessor::class;