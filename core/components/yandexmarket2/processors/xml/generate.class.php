<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Models\Pricelist;
use YandexMarket\Service;
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
        if ((!$id = $this->getProperty('id')) || !$this->pricelist = Pricelist::getById($id, $this->modx)) {
            return $this->modx->lexicon('ym_pricelist_err_nfs', ['id' => $id]);
        }

        $this->xmlGenerator = new Generate($this->pricelist, $this->modx);

        return true;
    }

    public function process()
    {
        try {
            $this->xmlGenerator->makeFile();
            $log = $this->xmlGenerator->getLog(true);
            if ($debugInfo = Service::debugInfo($this->modx)) {
                $log .= PHP_EOL.print_r($debugInfo, true);
            }
            return $this->success($log, $this->pricelist->toArray());
        } catch (Exception $exception) {
            return $this->failure($exception->getMessage(), $this->pricelist->toArray());
        }
    }
}

return ymXmlGenerateProcessor::class;