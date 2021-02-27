<?php

namespace YandexMarket\Xml;

use modX;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

class Preview
{
    public const PREVIEW_CATEGORIES = 'categories';
    public const PREVIEW_OFFERS     = 'offers';
    public const PREVIEW_SHOP       = 'shop';

    protected $writer;
    protected $pricelist;

    public function __construct(Pricelist $pricelist, modX $modx)
    {
        $this->pricelist = $pricelist;
        $this->writer = (new PricelistWriter($pricelist, $modx))->setPreviewMode();
        $this->writer->writeHeader();
    }

    public function previewCategories(): string
    {
        if ($total = $this->pricelist->offersCount()) {
            $this->writer->writeComment(' Всего подходящих предложений: '.$total.' ');
        } else {
            $this->writer->writeComment(' Не найдено подходящих предложений ');
        }

        if ($categoriesField = $this->pricelist->getFieldByType(Field::TYPE_CATEGORIES)) {
            $this->writer->writeField($categoriesField);
        } else {
            $this->writer->writeComment(' Не найден элемент categories ');
        }

        return $this->writer->getPreviewXml();
    }

    public function previewShop(): string
    {
        if ($shopField = $this->pricelist->getFieldByType(Field::TYPE_SHOP)) {
            $this->writer->writeField($shopField, [], [Field::TYPE_CATEGORIES, Field::TYPE_OFFERS,]);
        } else {
            $this->writer->writeComment(' Не найден элемент shop ');
        }
        return $this->writer->getPreviewXml();
    }

    public function previewOffer(): string
    {
        if (!$offerField = $this->pricelist->getFieldByType(Field::TYPE_OFFER)) {
            $this->writer->writeComment(' Не найден элемент offer ');
        } else {
            $offers = $this->pricelist->offersGenerator(['sortBy' => 'RAND()', 'limit' => 1]);

            foreach ($offers as $offer) {
                $this->writer->writeField($offerField, ['offer' => $offer]);
            }
        }
        return $this->writer->getPreviewXml();
    }

}