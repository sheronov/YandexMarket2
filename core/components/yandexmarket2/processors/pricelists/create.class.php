<?php

class ymPricelistCreateProcessor extends modObjectCreateProcessor
{
    public $objectType     = 'ym_pricelist';
    public $classKey       = 'ymPricelist';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'create';

    /**
     * @return bool
     */
    public function beforeSet(): bool
    {
        if (empty($this->getProperty('name',''))) {
            $this->modx->error->addField('name', $this->modx->lexicon('ym_pricelist_name_err_ns'));
        }
        if (!$this->getProperty('file')) {
            $this->setProperty('file', 'pricelist-'.date('Y-m-d-H-i-s').'.xml');
        }
        if (!$this->getProperty('type')) {
            $this->setProperty('type', 'yandex.market');
        }
        $this->object->set('created_on', date('Y-m-d H:i:s'));

        if ($this->modx->getCount($this->classKey, ['file' => $this->getProperty('file')])) {
            $this->modx->error->addField('file', $this->modx->lexicon('ym_pricelist_file_err_ae'));
        }

        return parent::beforeSet();
    }

}

return ymPricelistCreateProcessor::class;