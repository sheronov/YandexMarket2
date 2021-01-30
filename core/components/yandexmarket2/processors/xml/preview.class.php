<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Models\Pricelist;
use YandexMarket\Xml\Preview;
use YandexMarket\YandexMarket;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymXmlPreviewProcessor extends modProcessor
{
    /** @var Preview */
    protected $xml;

    public function initialize()
    {
        /** @var ymPricelist $pricelist */
        if ((!$id = $this->getProperty('id')) || !$pricelist = $this->modx->getObject(ymPricelist::class, $id)) {
            return $this->modx->lexicon('yandexmarket_pricelist_err_nfs', ['id' => $id]);
        }

        $this->xml = new Preview(new Pricelist($this->modx, $pricelist));

        return true;
    }

    public function process()
    {
        $additional = $this->getProperty('data', []);

        switch ($this->getProperty('method')) {
            case Preview::PREVIEW_CATEGORIES:
                $xml = $this->xml->previewCategories();
                break;
            case Preview::PREVIEW_OFFERS:
                $xml = $this->xml->previewOffer($additional);
                break;
            case Preview::PREVIEW_SHOP:
                $xml = $this->xml->previewShop($additional);
                break;
            default:
                return $this->failure($this->modx->lexicon('yandexmarket2_pricelist_err_method'));
        }

        return $this->success($xml, YandexMarket::debugInfo($this->modx));
    }
}

return ymXmlPreviewProcessor::class;