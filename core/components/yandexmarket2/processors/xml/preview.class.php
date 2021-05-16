<?php

use YandexMarket\Models\Pricelist;
use YandexMarket\QueryService;
use YandexMarket\Service;
use YandexMarket\Xml\Previewer;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymXmlPreviewProcessor extends modProcessor
{
    /** @var Previewer */
    protected $xmlPreviewer;

    public function initialize()
    {
        if ((!$id = $this->getProperty('id')) || !$pricelist = Pricelist::getById($id, $this->modx)) {
            return $this->modx->lexicon('ym2_pricelist_err_nf', ['id' => $id]);
        }
        /** @var Pricelist $pricelist */
        $this->xmlPreviewer = new Previewer(new QueryService($pricelist, $this->modx));

        return true;
    }

    public function process()
    {
        switch ($this->getProperty('method')) {
            case Previewer::PREVIEW_CATEGORIES:
                $xml = $this->xmlPreviewer->previewCategories();
                break;
            case Previewer::PREVIEW_OFFERS:
                $xml = $this->xmlPreviewer->previewOffer();
                break;
            case Previewer::PREVIEW_SHOP:
                $xml = $this->xmlPreviewer->previewShop();
                break;
            default:
                return $this->failure($this->modx->lexicon('ym2_pricelist_err_method'));
        }

        return $this->success($xml, Service::debugInfo($this->modx));
    }
}

return ymXmlPreviewProcessor::class;