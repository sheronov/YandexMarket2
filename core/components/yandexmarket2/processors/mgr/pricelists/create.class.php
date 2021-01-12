<?php

class ymPricelistCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'ymPricelist';
    public $classKey = 'ymPricelist';
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

return ymPricelistCreateProcessor::class;