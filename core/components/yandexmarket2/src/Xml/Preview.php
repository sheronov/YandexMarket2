<?php

namespace YandexMarket\Xml;

use Exception;
use modX;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

class Preview extends PricelistWriter
{
    const PREVIEW_CATEGORIES = 'categories';
    const PREVIEW_OFFERS     = 'offers';
    const PREVIEW_SHOP       = 'shop';

    protected $preview = true;

    public function __construct(Pricelist $pricelist, modX $modx)
    {
        parent::__construct($pricelist, $modx);
        $this->xml->openMemory();
        $this->writeHeader();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function previewCategories(): string
    {
        if ($total = $this->pricelist->offersCount()) {
            $this->writeComment(' Подходящих предложений: '.$total.' ');
        } else {
            $this->writeComment(' Не найдено подходящих предложений ');
        }

        if ($categoriesField = $this->pricelist->getFieldByType(Field::TYPE_CATEGORIES)) {
            $this->writeField($categoriesField);
        } else {
            $this->writeComment(' Не найден элемент categories ');
        }

        return $this->getPreviewXml();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function previewShop(): string
    {
        if ($shopField = $this->pricelist->getFieldByType(Field::TYPE_SHOP)) {
            $this->writeField($shopField, [], [Field::TYPE_CATEGORIES, Field::TYPE_OFFERS,]);
        } else {
            $this->writeComment(' Не найден элемент shop ');
        }
        return $this->getPreviewXml();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function previewOffer(): string
    {
        if (!$offerField = $this->pricelist->getFieldByType(Field::TYPE_OFFER)) {
            $this->writeComment(' Не найден элемент offer ');
        } else {
            if ($total = $this->pricelist->offersCount()) {
                $this->writeComment(' Подходящих предложений: '.$total.' ');
            } else {
                $this->writeComment(' Не найдено подходящих предложений ');
            }

            $offers = $this->pricelist->offersGenerator(['sortBy' => 'RAND()', 'limit' => 1]);

            foreach ($offers as $offer) {
                $this->switchContext($offer->get('context_key'));
                $this->writeField($offerField, ['offer' => $offer]);
            }
        }
        return $this->getPreviewXml();
    }

    protected function getPreviewXml(): string
    {
        return $this->xml->outputMemory(true);
    }

}