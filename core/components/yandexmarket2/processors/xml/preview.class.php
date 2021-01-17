<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Pricelist;
use YandexMarket\Xml\Preview;

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
            case Preview::METHOD_CATEGORIES:
                $xml = $this->xml->previewCategories($additional);
                break;
            case Preview::METHOD_OFFER:
                $xml = $this->xml->previewOffer($additional);
                break;
            case Preview::METHOD_SETTINGS:
                $xml = $this->xml->previewSettings($additional);
                break;
            default:
                return $this->failure($this->modx->lexicon('yandexmarket2_pricelist_err_method'));
        }

        return $this->success($xml);
    }
}

return ymXmlPreviewProcessor::class;