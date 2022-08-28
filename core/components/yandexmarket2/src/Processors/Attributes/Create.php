<?php

namespace YandexMarket\Processors\Attributes;

use MODX\Revolution\Processors\Model\CreateProcessor;
use YandexMarket\Model\YmFieldAttribute;
use YandexMarket\Models\Attribute;

class Create extends CreateProcessor
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
        if (empty($name) || empty($fieldId)) {
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
