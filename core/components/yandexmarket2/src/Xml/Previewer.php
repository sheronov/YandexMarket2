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
        if (!$offersCount = $this->pricelistService->getOffersCount()) {
            $this->writeComment($this->lexicon('ym2_debug_offers_not_found'));
            return $this->getPreviewXml();
        }
        $this->writeComment($this->lexicon('ym2_debug_suitable_offers').$offersCount.' ');

        $categoriesCount = $this->pricelistService->getCategoriesCount();
        $this->writeComment($this->lexicon('ym2_debug_suitable_categories').$categoriesCount.' ');

        if ($this->pricelistService->isCategoriesPluginPrepared()) {
            $this->writeComment($this->lexicon('ym2_debug_possible_categories_plugins'));
        }

        if ($categoriesField = $this->pricelistService->getFieldByType(Field::TYPE_CATEGORIES)) {
            $this->xml->startElement($categoriesField->name);
            $this->writeAttributes($categoriesField->getAttributes());

            if ($categoryField = $categoriesField->getChildren()[0] ?? null) {
                $categories = $this->pricelistService->categoriesGenerator();
                foreach ($categories as $category) {
                    $this->writeCategoryField($categoryField, $category);
                }
            } else {
                $this->writeComment($this->lexicon('ym2_debug_element_not_found').'category ');
            }

            $this->xml->endElement();
        } else {
            $this->writeComment($this->lexicon('ym2_debug_element_not_found').'categories ');
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
            $this->writeField($shopField, [], [
                Field::TYPE_CATEGORIES,
                Field::TYPE_OFFERS,
                Field::TYPE_OFFERS_TRANSPARENT,
                Field::TYPE_CATEGORIES_TRANSPARENT
            ]);
        } else {
            $this->writeComment($this->lexicon('ym2_debug_element_not_found').'shop ');
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
            $this->writeComment($this->lexicon('ym2_debug_element_not_found').'offer ');
        } else {
            $this->pricelistService->setOffersOrder('RAND()', '');
            $this->pricelistService->setOffersLimit(1);

            if (!$offersCount = $this->pricelistService->getOffersCount()) {
                $this->writeComment($this->lexicon('ym2_debug_offers_not_found'));
                return $this->getPreviewXml();
            }
            $this->writeComment($this->lexicon('ym2_debug_suitable_offers').$offersCount.' ');

            if ($this->pricelistService->isOffersPluginPrepared()) {
                $this->writeComment($this->lexicon('ym2_debug_possible_offers_plugins'));
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