<?php

namespace YandexMarket\Processors\Categories;

use MODX\Revolution\modAccessibleObject;
use MODX\Revolution\Processors\Model\RemoveProcessor;
use YandexMarket\Model\YmCategory;

class Remove extends RemoveProcessor
{
    public $objectType     = 'ym2_category';
    public $classKey       = YmCategory::class;
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'remove';

    public function initialize()
    {
        $options = [
            'resource_id'  => (int)$this->getProperty('resource_id'),
            'pricelist_id' => (int)$this->getProperty('pricelist_id')
        ];

        if (empty($options['resource_id']) || empty($options['pricelist_id'])) {
            return $this->failure($this->modx->lexicon($this->objectType .'_err_ns'));
        }

        $this->object = $this->modx->getObject($this->classKey, $options);
        if (empty($this->object)) {
            return $this->modx->lexicon($this->objectType . '_err_nfs', $options);
        }

        if ($this->checkRemovePermission && $this->object instanceof modAccessibleObject && !$this->object->checkPolicy('remove')) {
            return $this->modx->lexicon('access_denied');
        }
        return true;
    }
}
