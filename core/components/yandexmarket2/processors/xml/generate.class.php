<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Models\Pricelist;
use YandexMarket\QueryService;
use YandexMarket\Service;
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
        if ((!$id = $this->getProperty('id')) || !$this->pricelist = Pricelist::getById($id, $this->modx)) {
            return $this->modx->lexicon('ym2_pricelist_err_nf', ['id' => $id]);
        }

        $this->xmlGenerator = new FileGenerator(new QueryService($this->pricelist, $this->modx));

        return true;
    }

    public function process()
    {
        try {
            $this->xmlGenerator->makeFile();
        } catch (Exception $exception) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $exception->getMessage());
        } finally {
            if ($debugInfo = Service::debugInfo($this->modx)) {
                $this->modx->log(modX::LOG_LEVEL_INFO, print_r($debugInfo, true));
            }
            $this->modx->log(modX::LOG_LEVEL_INFO, 'COMPLETED');
            return $this->modx->error->process('', !isset($exception), $this->pricelist->toArray());
        }
    }
}

return ymXmlGenerateProcessor::class;