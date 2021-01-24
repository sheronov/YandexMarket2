<?php

namespace YandexMarket\Xml;

use YandexMarket\Models\Pricelist;

class Preview
{
    public const PREVIEW_CATEGORIES = 'categories';
    public const PREVIEW_OFFERS     = 'offers';
    public const PREVIEW_SHOP       = 'shop';

    protected $writer;
    protected $pricelist;

    public function __construct(Pricelist $pricelist)
    {
        $this->pricelist = $pricelist;
        $this->writer = new PricelistWriter();
        // TODO: здесь дёрнуть ROOT элемент из прайс-листа и пусть отрисовка идёт от него.
    }

    public function previewCategories(): string
    {
        $this->writer->writeCategories($this->pricelist->getCategories());
        return $this->writer->getXml();
    }

    public function previewShop(array $additional = []): string
    {
        $data = array_filter(array_merge($this->pricelist->getShopData(), $additional), static function ($item) {
            return $item !== '' && $item !== null;
        });
        $this->writer->writeShopData($data);
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