<?php

class ymCategoryRemoveProcessor extends modObjectProcessor
{
    public $objectType     = 'ym_category';
    public $classKey       = 'ymCategory';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';

    /**
     * @return array|string
     */
    public function process()
    {
        if (!$this->checkPermissions()) {
            return $this->failure($this->modx->lexicon('access_denied'));
        }

        $pricelistId = (int)$this->getProperty('pricelist_id') ?: null;

        if (!$categoryId = (int)$this->getProperty('category_id')) {
            return $this->failure($this->modx->lexicon('yandexmarket2_category_err_ns'));
        }

        /** @var ymCategory $object */
        if (!$object = $this->modx->getObject($this->classKey, [
            'category_id'  => $categoryId,
            'pricelist_id' => $pricelistId
        ])) {
            return $this->failure($this->modx->lexicon('yandexmarket2_category_err_nf'));
        }

        $object->remove();

        return $this->success();
    }

}

return ymCategoryRemoveProcessor::class;