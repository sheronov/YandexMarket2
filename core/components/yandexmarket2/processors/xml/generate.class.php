<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Models\Pricelist;
use YandexMarket\Xml\Generator;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymXmlGenerateProcessor extends modProcessor
{

    /** @var Generator */
    protected $xmlGenerator;
    protected $pricelist;

    public function initialize()
    {
        /** @var ymPricelist $pricelist */
        if ((!$id = $this->getProperty('id')) || !$pricelist = $this->modx->getObject(ymPricelist::class, $id)) {
            return $this->modx->lexicon('ym_pricelist_err_nfs', ['id' => $id]);
        }

        $this->pricelist = new Pricelist($this->modx, $pricelist);
        $this->xmlGenerator = new Generator($this->pricelist);

        return true;
    }

    public function process()
    {
        if ($this->xmlGenerator->makeFile()) {
            return $this->success($this->xmlGenerator->getLog(true), $this->pricelist->toArray());
        }
        return $this->failure($this->xmlGenerator->getLog(true), $this->pricelist->toArray());
    }
}

return ymXmlGenerateProcessor::class;