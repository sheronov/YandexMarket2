<?php

use YandexMarket\Models\Field;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymFieldUpdateProcessor extends modObjectUpdateProcessor
{
    public $objectType     = 'ym_field';
    public $classKey       = 'ymField';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'save';

    /**
     * @return bool
     */
    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $name = trim($this->getProperty('name'));
        $type = (int)$this->getProperty('type');
        $pricelistId = (int)$this->getProperty('pricelist_id');
        if (empty($id) || empty($name) || empty($type) || empty($pricelistId)) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym_field_err_valid'));
        }
        $this->setProperty('active', filter_var($this->getProperty('active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);

        return parent::beforeSet();
    }

    public function cleanup()
    {
        return $this->success('', (new Field($this->modx, $this->object))->toFrontend(true));
    }
}

return ymFieldUpdateProcessor::class;
