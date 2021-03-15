<?php

use YandexMarket\Models\Attribute;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymAttributeUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType     = 'ym2_attribute';
    public $classKey       = ymFieldAttribute::class;
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
        return $this->success('', (new Attribute($this->modx, $this->object))->toArray());
    }
}

return ymAttributeUpdateProcessor::class;
