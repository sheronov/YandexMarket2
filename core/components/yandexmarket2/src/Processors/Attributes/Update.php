<?php

namespace YandexMarket\Processors\Attributes;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use YandexMarket\Model\YmFieldAttribute;
use YandexMarket\Models\Attribute;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AUpdate extends \modObjectUpdateProcessor
    {
        public $classKey = \YmFieldAttribute::class;
    }
} else {
    abstract class AUpdate extends UpdateProcessor
    {
        public $classKey = YmFieldAttribute::class;
    }
}
class Update extends AUpdate
{
    public $objectType     = 'ym2_attribute';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'save';

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        $fieldId = (int)$this->getProperty('field_id');
        $id = (int)$this->getProperty('id');
        if (empty($id) || empty($name) || empty($fieldId)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym2_attribute_err_valid'));
        }

        return parent::beforeSet();
    }

    public function cleanup()
    {
        $attribute = new Attribute($this->modx, $this->object);
        $source = $attribute->getField()->getPricelist()->getMarketplace();
        $this->modx->lexicon->load($source::getLexiconNs());
        return $this->success('', $attribute->toArray());
    }
}
