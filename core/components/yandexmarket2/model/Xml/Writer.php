<?php

namespace YandexMarket\Xml;

use modResource;
use XMLWriter;
use YandexMarket\Pricelist;
use ymCategory;

class Writer
{
    protected $pricelist;
    protected $xml;

    public function __construct(Pricelist $pricelist)
    {
        $this->pricelist = $pricelist;
        $this->xml = new XMLWriter();
        $this->xml->openMemory();
        $this->xml->setIndent(true);
        $this->xml->setIndentString("\t");
    }

    public function writeShopData(): void
    {
        $this->xml->startElement('shop');
        foreach ($this->pricelist->getShopData() as $key => $value) {
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

    public function writeCategories(): void
    {
        if ($categories = $this->pricelist->getCategories()) {
            $this->xml->startElement('categories');
            /** @var ymCategory $category */
            foreach ($categories as $category) {
                /** @var modResource $resource */
                if ($resource = $category->getOne('Category')) {
                    $this->writeCategory($resource);
                }
            }
            $this->xml->endElement();
        }
    }

    protected function writeCategory(modResource $category): void
    {
        $this->xml->startElement('category');
        $this->xml->writeAttribute('id', $category->get('id'));
        $this->xml->text($category->get('pagetitle'));
        $this->xml->endElement();
    }

    public function getXml(): string
    {
        return $this->xml->outputMemory(true);
    }

    public function setIndent(bool $indent = true): Writer
    {
        $this->xml->setIndent($indent);
        return $this;
    }
}