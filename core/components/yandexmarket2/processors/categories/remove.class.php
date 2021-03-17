<?php

class ymCategoryRemoveProcessor extends modObjectProcessor
{
    public $objectType     = 'yandexmarket2.category';
    public $classKey       = ymCategory::class;
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

        if (!$resourceId = (int)$this->getProperty('resource_id')) {
            return $this->failure($this->modx->lexicon('ym2_category_err_nf'));
        }

        /** @var ymCategory $object */
        if (!$object = $this->modx->getObject($this->classKey, [
            'resource_id'  => $resourceId,
            'pricelist_id' => $pricelistId
        ])) {
            return $this->failure($this->modx->lexicon('ym2_category_err_nf'));
        }

        $object->remove();

        return $this->success();
    }

}

return ymCategoryRemoveProcessor::class;