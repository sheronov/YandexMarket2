<?php

use YandexMarket\Models\Attribute;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymAttributeCreateProcessor extends modObjectCreateProcessor
{
    public $objectType     = 'yandexmarket2.attribute';
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
        if (empty($name) || empty($fieldId)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym2_attribute_err_valid'));
        }

        return parent::beforeSet();
    }

    public function cleanup()
    {
        return $this->success('', (new Attribute($this->modx, $this->object))->toArray());
    }
}

return ymAttributeCreateProcessor::class;
