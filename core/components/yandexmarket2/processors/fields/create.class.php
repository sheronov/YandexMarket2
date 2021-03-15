<?php

use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymFieldCreateProcessor extends modObjectCreateProcessor
{
    public $objectType     = 'ym2_field';
    public $classKey       = ymField::class;
    public $languageTopics = ['yandexmarket2'];

    //public $permission = 'save';

    public function beforeSet(): bool
    {
        $name = trim($this->getProperty('name'));
        $type = (int)$this->getProperty('type');
        $pricelist = Pricelist::getById((int)$this->getProperty('pricelist_id'), $this->modx);
        if (empty($name) || $type === null || $pricelist === null) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym2_field_err_valid'));
        }
        $this->setProperty('created_on', date('Y-m-d H:i:s'));
        $this->setProperty('active', filter_var($this->getProperty('active', true), FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        if (is_array($this->getProperty('value'))) {
            $this->setProperty('value', json_encode($this->getProperty('value')));
        }

        return parent::beforeSet();
    }

    public function cleanup()
    {
        return $this->success('', (new Field($this->modx, $this->object))->toArray());
    }
}

return ymFieldCreateProcessor::class;
