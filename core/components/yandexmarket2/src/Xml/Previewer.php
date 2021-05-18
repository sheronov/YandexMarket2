<?php

namespace YandexMarket\Xml;

use Exception;
use YandexMarket\Models\Field;
use YandexMarket\QueryService;

class Previewer extends Writer
{
    const PREVIEW_CATEGORIES = 'categories';
    const PREVIEW_OFFERS     = 'offers';
    const PREVIEW_SHOP       = 'shop';

    protected $preview = true;

    public function __construct(QueryService $pricelistService)
    {
        parent::__construct($pricelistService);
        $this->xml->openMemory();
        $this->writeHeader();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function previewCategories(): string
    {
        if ($categoriesField = $this->pricelistService->getFieldByType(Field::TYPE_CATEGORIES)) {
            $categoriesCount = $this->pricelistService->getCategoriesCount();
            $this->writeComment(' Подходящих категорий: '.$categoriesCount.' ');

            if ($this->pricelistService->isCategoriesPluginPrepared()) {
                $this->writeComment(' Возможно используются условия для категорий из плагинов ');
            }

            if (!$offersCount = $this->pricelistService->getOffersCount()) {
                $this->writeComment(' Не найдено подходящих предложений ');
                return $this->getPreviewXml();
            }

            $this->writeComment(' Подходящих предложений: '.$offersCount.' ');

            $this->xml->startElement($categoriesField->name);
            $this->writeAttributes($categoriesField->getAttributes());

            if ($categoryField = $categoriesField->getChildren()[0] ?? null) {
                $categories = $this->pricelistService->categoriesGenerator();
                foreach ($categories as $category) {
                    $this->writeCategoryField($categoryField, $category);
                }
            } else {
                $this->writeComment(' Не найден элемент category ');
            }

            $this->xml->endElement();
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
        if ($shopField = $this->pricelistService->getFieldByType(Field::TYPE_SHOP)) {
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
        if (!$offerField = $this->pricelistService->getFieldByType(Field::TYPE_OFFER)) {
            $this->writeComment(' Не найден элемент offer ');
        } else {
            $this->pricelistService->setOffersOrder('RAND()', '');
            $this->pricelistService->setOffersLimit(1);

            if (!$offersCount = $this->pricelistService->getOffersCount()) {
                $this->writeComment(' Не найдено подходящих предложений ');
                return $this->getPreviewXml();
            }
            $this->writeComment(' Подходящих предложений: '.$offersCount.' ');

            if ($this->pricelistService->isOffersPluginPrepared()) {
                $this->writeComment(' Возможно используются условия для предложений из плагинов ');
            }

            $offers = $this->pricelistService->offersGenerator();
            foreach ($offers as $offer) {
                $this->switchContext($offer->get('context_key'));
                $this->writeOfferField($offerField, $offer);
            }
        }
        return $this->getPreviewXml();
    }

    protected function getPreviewXml(): string
    {
        return $this->xml->outputMemory(true);
    }

}