<?php

namespace YandexMarket\Processors\Categories;

use MODX\Revolution\Processors\Model\CreateProcessor;
use YandexMarket\Model\YmCategory;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class ACreate extends \modObjectCreateProcessor
    {
        public $classKey = \YmCategory::class;
    }
} else {
    abstract class ACreate extends CreateProcessor
    {
        public $classKey = YmCategory::class;
    }
}

class Create extends ACreate
{
    public $objectType     = 'ym2_category';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'create';

    /**
     * @return bool
     */
    public function beforeSet(): bool
    {
        $options = [
            'resource_id'  => (int)$this->getProperty('resource_id'),
            'pricelist_id' => (int)$this->getProperty('pricelist_id')
        ];

        if (empty($options['resource_id']) || empty($options['pricelist_id'])) {
            $this->modx->error->addField('resource_id', $this->modx->lexicon($this->objectType .'_err_ns'));
        } elseif ($this->modx->getCount($this->classKey, $options)) {
            $this->modx->error->addField('resource_id', $this->modx->lexicon($this->objectType .'_err_ae'));
        }

        return parent::beforeSet();
    }

}
