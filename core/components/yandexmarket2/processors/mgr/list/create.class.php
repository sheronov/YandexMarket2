<?php

class YandexMarket2ListCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'YandexMarket2List';
    public $classKey = 'YandexMarket2List';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('yandexmarket2_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('yandexmarket2_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'YandexMarket2ListCreateProcessor';