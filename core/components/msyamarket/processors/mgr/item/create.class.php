<?php

class msYaMarketItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'msYaMarketItem';
    public $classKey = 'msYaMarketItem';
    public $languageTopics = ['msyamarket'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('msyamarket_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('msyamarket_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'msYaMarketItemCreateProcessor';