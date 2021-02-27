<?php

use YandexMarket\Models\Pricelist;
use YandexMarket\Service;
use YandexMarket\Xml\Preview;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymXmlPreviewProcessor extends modProcessor
{
    /** @var Preview */
    protected $xml;

    public function initialize()
    {
        if ((!$id = $this->getProperty('id')) || !$pricelist = Pricelist::getById($id, $this->modx)) {
            return $this->modx->lexicon('ym_pricelist_err_nfs', ['id' => $id]);
        }
        /** @var Pricelist $pricelist */
        $this->xml = new Preview($pricelist, $this->modx);

        return true;
    }

    public function process()
    {
        switch ($this->getProperty('method')) {
            case Preview::PREVIEW_CATEGORIES:
                $xml = $this->xml->previewCategories();
                break;
            case Preview::PREVIEW_OFFERS:
                $xml = $this->xml->previewOffer();
                break;
            case Preview::PREVIEW_SHOP:
                $xml = $this->xml->previewShop();
                break;
            default:
                return $this->failure($this->modx->lexicon('ym_pricelist_err_method'));
        }

        return $this->success($xml, Service::debugInfo($this->modx));
    }
}

return ymXmlPreviewProcessor::class;