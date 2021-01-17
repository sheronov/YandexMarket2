<?php

namespace YandexMarket\Xml;

use YandexMarket\Pricelist;

class Preview
{
    public const METHOD_CATEGORIES = 'categories';
    public const METHOD_OFFER      = 'offer';
    public const METHOD_SETTINGS   = 'settings';

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

    public function previewSettings(array $additional = []): string
    {
        return <<<EOT
<?xml version = "1.0" encoding = "UTF-8" ?>
<shop>
    <!-- todo: implement here -->
</shop>
EOT;
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