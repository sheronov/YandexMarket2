<?php

use YandexMarket\Models\Attribute;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymAttributeUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType     = 'ym_attribute';
    public $classKey       = 'ymFieldAttribute';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'save';

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        $type = (int)$this->getProperty('type') ?: Attribute::TYPE_DEFAULT;
        $fieldId = (int)$this->getProperty('field_id');
        $id = (int)$this->getProperty('id');
        $properties = $this->getProperty('properties') ?? [];
        $properties['type'] = $type;
        $this->setProperty('properties', $properties);
        if (empty($id) || empty($name) || empty($fieldId)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym_attribute_err_valid'));
        }

        return parent::beforeSet();
    }

    public function cleanup()
    {
        return $this->success('', (new Attribute($this->modx, $this->object))->toArray());
    }
}

return ymAttributeUpdateProcessor::class;
