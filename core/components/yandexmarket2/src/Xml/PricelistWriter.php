<?php

namespace YandexMarket\Xml;

use modResource;
use XMLWriter;
use YandexMarket\Models\Category;
use YandexMarket\Models\Offer;
use YandexMarket\Models\Pricelist;

class PricelistWriter
{
    protected $xml;

    public function __construct()
    {
        $this->xml = new XMLWriter();
        $this->xml->openMemory();
        $this->xml->setIndent(true);
        $this->xml->setIndentString("\t");
    }

    public function writeShopData(array $data): void
    {
        $this->xml->startElement('shop');
        foreach ($data as $key => $value) {
            $this->xml->startElement($key);
            if ($key === 'currencies') {
                foreach ($value as $i => $val) {
                    $this->xml->startElement('currency');
                    $this->xml->writeAttribute('id', $val);
                    if (!$i) {
                        $this->xml->writeAttribute('rate', 1);
                    }
                    $this->xml->endElement();
                }
            } elseif (is_bool($value)) {
                $this->xml->text($value ? 'true' : 'false');
            } else {
                $this->xml->text($value);
            }
            $this->xml->endElement();
        }
        $this->xml->endElement();
    }

    public function writeCategories(array $categories): void
    {
        $this->xml->startElement('categories');
        if (count($categories)) {
            /** @var Category $category */
            foreach ($categories as $category) {
                if ($resource = $category->getResource()) {
                    $this->writeCategory($resource);
                }
            }
        } else {
            $this->xml->writeComment('Выберите категории');
        }
        $this->xml->endElement();
    }

    protected function writeCategory(modResource $category): void
    {
        $this->xml->startElement('category');
        $this->xml->writeAttribute('id', $category->get('id'));
        $this->xml->text($category->get('pagetitle'));
        $this->xml->endElement();
    }

    public function writeOffers(array $offers): void
    {
    }

    public function writeOffer(Offer $offer, Pricelist $pricelist): void
    {
        $this->xml->startElement('offer');
        $this->xml->writeAttribute('id', $offer->get('id'));
        $this->xml->writeAttribute('availability', 'true');
        $this->xml->startElement('name');
        $this->xml->text($offer->get('pagetitle'));
        $this->xml->endElement();
        $this->xml->startElement('url');
        $this->xml->text('base_url');
        $this->xml->endElement();
        $this->xml->endElement();
    }

    public function getXml(): string
    {
        return $this->xml->outputMemory(true);
    }

    public function setIndent(bool $indent = true): PricelistWriter
    {
        $this->xml->setIndent($indent);
        return $this;
    }
}