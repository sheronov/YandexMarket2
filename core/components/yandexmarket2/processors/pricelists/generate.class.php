<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Models\Pricelist;
use YandexMarket\Xml\Generator;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymPricelistGenerateProcessor extends modProcessor
{

    /** @var Generator */
    protected $pricelistGenerator;
    protected $pricelist;

    public function initialize()
    {
        /** @var ymPricelist $pricelist */
        if ((!$id = $this->getProperty('id')) || !$pricelist = $this->modx->getObject(ymPricelist::class, $id)) {
            return $this->modx->lexicon('ym_pricelist_err_nfs', ['id' => $id]);
        }

        $this->pricelist = new Pricelist($this->modx, $pricelist);
        $this->pricelistGenerator = new Generator($this->pricelist);

        return true;
    }

    public function process()
    {
        if ($this->pricelistGenerator->makeXml()) {
            return $this->success($this->pricelistGenerator->getLog(true), $this->pricelist->toArray());
        }
        return $this->failure($this->pricelistGenerator->getLog(true), $this->pricelist->toArray());
    }
}

return ymPricelistGenerateProcessor::class;