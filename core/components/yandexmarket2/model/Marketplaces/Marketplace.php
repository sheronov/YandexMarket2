<?php

namespace YandexMarket\Marketplaces;

use xPDO;

abstract class Marketplace
{
    abstract public static function getKey(): string;

    abstract public static function getShopFields(xPDO $xpdo): array;

    abstract public static function getOfferFields(xPDO $xpdo): array;

    abstract public static function getDefaultFields(xPDO $xpdo): array;

    /**
     * @TODO Сделать тут автозагрузку всех маркетплейсов из этой папки method listMarketplaces()
     *
     * @param  string  $type
     *
     * @return Marketplace|null
     */
    public static function getMarketPlace(string $type): ?Marketplace
    {
        switch ($type) {
            case YandexMarket::getKey():
                return new YandexMarket();
            default:
                return null;
        }
    }
}