<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Handlers\PricelistFiller;
use YandexMarket\Models\Pricelist;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

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
        if (empty($this->getProperty('name', ''))) {
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

    public function afterSave(): bool
    {
        $pricelist = new Pricelist($this->modx, $this->object);

        $pricelistFiller = new PricelistFiller($pricelist);
        $pricelistFiller->fillDefaultValues();

        $this->object = $pricelist;

        return parent::afterSave();
    }

    public function cleanup(): array
    {
        return $this->success('', $this->object->toArray());
    }

}

return ymPricelistCreateProcessor::class;