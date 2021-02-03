<?php

namespace YandexMarket\Xml;

use YandexMarket\Models\Offer;
use YandexMarket\Models\Pricelist;

class Preview
{
    public const PREVIEW_CATEGORIES = 'categories';
    public const PREVIEW_OFFERS     = 'offers';
    public const PREVIEW_SHOP       = 'shop';

    protected $writer;
    protected $pricelist;

    public function __construct(Pricelist $pricelist)
    {
        $this->pricelist = $pricelist;
        $this->writer = new PricelistWriter();
        // TODO: здесь дёрнуть ROOT элемент из прайс-листа и пусть отрисовка идёт от него.
    }

    public function previewCategories(array $unsavedData = []): string
    {
        $this->writer->writeCategories($this->pricelist->getCategories());
        return $this->writer->getXml();
    }

    public function previewShop(array $unsavedData = []): string
    {
        $data = array_filter(array_merge($this->pricelist->getShopValues(), $unsavedData), static function ($item) {
            return $item !== '' && $item !== null;
        });
        $this->writer->writeShopData($data);
        return $this->writer->getXml();
    }

    public function previewOffer(array $unsavedData = []): string
    {
        $resources = $this->pricelist->getPricelistOffers();
        foreach ($resources as $resource) {
            $this->writer->writeOffer(new Offer($this->pricelist->getXpdo(), $resource), $this->pricelist);
            break;
        }
        return $this->writer->getXml();
    }

}