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