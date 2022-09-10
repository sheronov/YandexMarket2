<?php

namespace YandexMarket\Processors\Xml;

use Exception;
use MODX\Revolution\Processors\Processor;
use YandexMarket\Models\Pricelist;
use YandexMarket\QueryService;
use YandexMarket\Service;
use YandexMarket\Xml\FileGenerator;

if (!Service::isMODX3()) {
    abstract class AGenerateProcessor extends \modProcessor { }
} else {
    abstract class AGenerateProcessor extends Processor { }
}

class Generate extends AGenerateProcessor
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
            $this->modx->log(Service::LOG_LEVEL_ERROR, $exception->getMessage());
        } finally {
            if ($debugInfo = Service::debugInfo($this->modx)) {
                $this->modx->log(Service::LOG_LEVEL_INFO, print_r($debugInfo, true));
            }
            $this->modx->log(Service::LOG_LEVEL_INFO, 'COMPLETED');
            return $this->modx->error->process('', !isset($exception), $this->pricelist->toArray());
        }
    }
}
