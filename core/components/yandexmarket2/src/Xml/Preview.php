<?php

namespace YandexMarket\Xml;

use modResource;
use msProduct;
use YandexMarket\Models\Offer;
use YandexMarket\Models\Pricelist;
use YandexMarket\Service;

class Preview
{
    public const PREVIEW_CATEGORIES = 'categories';
    public const PREVIEW_OFFERS     = 'offers';
    public const PREVIEW_SHOP       = 'shop';

    protected $writer;
    protected $pricelist;
    protected $service;

    public function __construct(Pricelist $pricelist)
    {
        $this->pricelist = $pricelist;
        $this->writer = new PricelistWriter();
        $this->service = new Service($pricelist->modX());
        // TODO: здесь дёрнуть ROOT элемент из прайс-листа и пусть отрисовка идёт от него.
    }

    public function previewCategories(array $unsavedData = []): string
    {
        $modx = $this->pricelist->modX();
        // TODO: сделать аналогично офферам
        $q = $this->service->queryForPricelist($this->pricelist);

        if ($total = $modx->getCount($q->getClass(), $q)) {
            $this->writer->writeComment(' Всего подходящих предложений: '.$total.' ');
        } else {
            $this->writer->writeComment(' Не найдено подходящих предложений ');
        }

        $this->writer->writeCategories($this->pricelist->getCategories());
        return $this->writer->getXml();
    }

    public function previewShop(array $unsavedData = []): string
    {
        $data = array_filter(array_merge($this->pricelist->getShopValues(), $unsavedData), static function ($item) {
            return ($item['active'] ?? true);
        });
        // TODO: переделать
        $this->writer->writeShopData($data);
        return $this->writer->getXml();
    }

    public function previewOffer(array $unsavedData = []): string
    {
        // TODO: скорее всего отказаться от unsavedData (каждое поле само по себе сохраняется)
        $modx = $this->pricelist->modX();
        $q = $this->service->queryForPricelist($this->pricelist);

        $q->limit(1);
        $q->sortby('RAND()');

        /** @var modResource|msProduct $resource */
        if ($resource = $modx->getObject($q->getClass(), $q)) {
            // $modx->log(1, var_export($resource->toArray(), true));
            $offer = new Offer($modx, $resource);
            $offer->setService($this->service);
            $this->writer->writeOffer($offer, $this->pricelist);
        }

        return $this->writer->getXml();
    }

}