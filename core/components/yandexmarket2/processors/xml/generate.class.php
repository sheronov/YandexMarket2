<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Models\Pricelist;
use YandexMarket\Xml\Generate;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymXmlGenerateProcessor extends modProcessor
{

    /** @var Generate */
    protected $xmlGenerator;
    /** @var Pricelist */
    protected $pricelist;

    public function initialize()
    {
        if ((!$id = $this->getProperty('id')) || !$this->pricelist = Pricelist::getById($id,$this->modx)) {
            return $this->modx->lexicon('ym_pricelist_err_nfs', ['id' => $id]);
        }

        $this->xmlGenerator = new Generate($this->pricelist, $this->modx);

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