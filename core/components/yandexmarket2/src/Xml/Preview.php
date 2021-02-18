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
        // TODO: аналогично офферам
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
        $className = $this->service->hasMS2 ? 'msProduct' : 'modResource';
        // $this->pricelist->modX()->log(1, var_export($unsavedData, true));
        $q = $this->service->queryForPricelist($this->pricelist);

        if ($total = $modx->getCount($className, $q)) {
            $this->writer->writeComment(' Всего подходящих предложений: '.$modx->getCount($className, $q).' ');
        } else {
            $this->writer->writeComment(' Не найдено подходящих предложений ');
        }

        $q->limit(1);
        $q->sortby('RAND()');

        /** @var modResource|msProduct $resource */
        if ($resource = $modx->getObject($className, $q)) {
            // $modx->log(1, var_export($resource->toArray(), true));
            $offer = new Offer($modx, $resource);
            $offer->setService($this->service);
            $this->writer->writeOffer($offer, $this->pricelist);
        }
        // // $resources = $modx->getIterator('modResource',$q);
        // // $resources = $this->pricelist->getPricelistOffers();
        // foreach ($resources as $resource) {
        //     $this->writer->writeOffer(new Offer($modx, $resource), $this->pricelist);
        //     break;
        // }
        return $this->writer->getXml();
    }

}