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
            return $this->modx->lexicon('ym2_pricelist_err_nf', ['id' => $id]);
        }

        $this->xmlGenerator = new Generate($this->pricelist, $this->modx);

        return true;
    }

    public function process()
    {
        try {
            $this->xmlGenerator->makeFile();
            if ($debugInfo = Service::debugInfo($this->modx)) {
                $this->modx->log(modX::LOG_LEVEL_INFO, print_r($debugInfo, true));
            }
            $this->modx->log(modX::LOG_LEVEL_INFO, 'COMPLETED');
            return $this->success('', $this->pricelist->toArray());
        } catch (Exception $exception) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $exception->getMessage());
            $this->modx->log(modX::LOG_LEVEL_INFO, 'COMPLETED');
            return $this->failure($exception->getMessage(), $this->pricelist->toArray());
        }
    }
}

return ymXmlGenerateProcessor::class;