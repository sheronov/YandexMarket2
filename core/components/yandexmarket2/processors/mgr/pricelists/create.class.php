<?php

class ymPricelistCreateProcessor extends modObjectCreateProcessor
{
    public $objectType     = 'ymPricelist';
    public $classKey       = 'ymPricelist';
    public $languageTopics = ['yandexmarket2'];
    //public $permission = 'create';

    /**
     * @return bool
     */
    public function beforeSet(): bool
    {
        if (!$this->getProperty('file')) {
            $fileName = 'goods.xml';
            if ($total = $this->modx->getCount('ymPricelist')) {
                $total++;
                $fileName = "goods{$total}.xml";
            }
            $this->setProperty('file', $fileName);
        }
        if (!$this->getProperty('type')) {
            $this->setProperty('type', 'yandex.market');
        }
        $this->object->set('created_on', date('Y-m-d H:i:s'));

        if ($this->modx->getCount($this->classKey, ['file' => $this->getProperty('file')])) {
            $this->modx->error->addField('file', $this->modx->lexicon('yandexmarket2_file_err_ae'));
        }

        return parent::beforeSet();
    }

}

return ymPricelistCreateProcessor::class;