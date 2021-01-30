<?php

namespace YandexMarket\Marketplaces;

abstract class Marketplace
{
    abstract public static function getKey(): string;

    abstract public static function getShopFields(): array;

    abstract public static function getOfferFields(): array;

    abstract public static function getDefaultFields(): array;

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