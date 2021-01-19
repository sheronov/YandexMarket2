<?php

namespace YandexMarket\Marketplaces;

abstract class Marketplace
{
    public const YANDEX_MARKET = 'yandex.market';

    abstract public static function getFields(): array;

    public static function getMarketPlace(string $type): ?Marketplace
    {
        switch ($type) {
            case self::YANDEX_MARKET:
                return new YandexMarket();
            default:
                return null;
        }
    }
}