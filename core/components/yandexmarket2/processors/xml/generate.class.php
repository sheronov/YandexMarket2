<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Models\Pricelist;
use YandexMarket\Xml\FileGenerator;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymXmlGenerateProcessor extends modProcessor
{

    /** @var FileGenerator */
    protected $xmlGenerator;
    /** @var Pricelist */
    protected $pricelist;

    public function initialize()
    {
        if ((!$id = $this->getProperty('id')) || !$this->pricelist = Pricelist::getById($id,$this->modx)) {
            return $this->modx->lexicon('ym_pricelist_err_nfs', ['id' => $id]);
        }

        $this->xmlGenerator = new FileGenerator($this->pricelist, $this->modx);

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