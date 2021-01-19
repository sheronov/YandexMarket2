<?php

namespace YandexMarket\Xml;

use YandexMarket\Pricelist;

class Preview
{
    public const METHOD_CATEGORIES = 'categories';
    public const METHOD_OFFER      = 'offer';
    public const METHOD_SHOP       = 'shop';

    protected $writer;

    public function __construct(Pricelist $pricelist)
    {
        $this->writer = new Writer($pricelist);
    }

    public function previewCategories(array $additional = []): string
    {
        $this->writer->writeCategories();
        return $this->writer->getXml();
    }

    public function previewShop(array $additional = []): string
    {
        $this->writer->writeShopData();
        return $this->writer->getXml();
    }

    public function previewOffer(array $additional = []): string
    {
        return <<<EOT
<offers>
    <offer id="10" availability="true">
        <!-- todo: implement here -->
    </offer>
</offers>
EOT;
    }

}