<?php

namespace YandexMarket\Processors\Attributes;

use MODX\Revolution\Processors\Model\UpdateProcessor;
use YandexMarket\Model\YmFieldAttribute;
use YandexMarket\Models\Attribute;

class Update extends UpdateProcessor
{
    public $objectType     = 'ym2_attribute';
    public $classKey       = YmFieldAttribute::class;
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
