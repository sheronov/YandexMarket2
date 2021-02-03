<?php

namespace YandexMarket\Marketplaces;

use xPDO;

abstract class Marketplace
{
    public const NODE_ROOT   = 'yml_catalog';
    public const NODE_OFFERS = 'offers';
    public const NODE_OFFER  = 'offer';
    public const NODE_SHOP   = 'shop';

    protected $xpdo;

    public function __construct(xPDO $xpdo)
    {
        $this->xpdo = $xpdo;
    }

    /**
     * Уникальный ключ маркетплейса. Нужен для лексиконов. Само название задаётся в лексиконе ym_marketplace_{key}
     * Для лексиконов к полю идёт двойной поиск ym_{key}_{parent}_{field}, а потом ym_{key}_{field}
     * Название полей - ym_{key}_{parent}_{field} если отсутствует, то ym_{key}_{field} (где {parent} - родитель узла)
     * Расширенная подсказка к полю - ym_{key}_{field}_help
     * Название атрибута поля - ym_{key}_{field}_attr_{attribute}
     * Если есть поля или атрибуты с выбором из нескольких значений, то к лексикону названия добавляется _value_{val}
     *
     * @return string
     */
    abstract public static function getKey(): string;

    /**
     * Обязательная структура, которая будет добавлена при создании прайс-листа.
     * Должна содержать поле с типом \YandexMarket\Models\Field::TYPE_SHOP
     *
     * @return array
     */
    abstract public static function getRootFields(): array;

    /**
     * Для первой вкладки настроек магазина и прайс-листа
     * Обязательно должно быть поле с типом \YandexMarket\Models\Field::TYPE_OFFER
     *
     * @return array
     */
    abstract public static function getShopFields(): array;

    /**
     * Для настройки полей предложений в последней вкладке
     *
     * @return array
     */
    abstract public static function getOfferFields(): array;

    /**
     * @TODO Сделать тут автозагрузку всех маркетплейсов из этой папки method listMarketplaces()
     *
     * @param  string  $type
     * @param  xPDO  $xpdo
     *
     * @return Marketplace|null
     */
    public static function getMarketPlace(string $type, xPDO $xpdo): ?Marketplace
    {
        switch ($type) {
            case YandexMarket::getKey():
                return new YandexMarket($xpdo);
            default:
                return null;
        }
    }

    public function defaultValues(string $tag): array
    {
        switch ($tag) {
            case self::NODE_SHOP:
                return [
                    'name'       => $this->defaultOption('shop_name', $this->xpdo->getOption('site_name')),
                    'url'        => $this->defaultOption('shop_url', $this->xpdo->getOption('site_url')),
                    'platform'   => $this->defaultOption('shop_platform', 'MODX Revolution'),
                    'version'    => $this->defaultOption('shop_version', $this->xpdo->getOption('settings_version')),
                    'currencies' => explode(',', $this->defaultOption('shop_currencies', 'RUR'))
                ];
            case self::NODE_OFFER:
                return [
                    'name'        => $this->defaultOption('offer_name', 'modResource.pagetitle'),
                    'url'         => $this->defaultOption('offer_url', 'Offer.url'), // Offer собирающий класс
                    'currencyId'  => $this->defaultOption('offer_currency_id', 'RUR'),
                    'delivery'    => $this->defaultOption('offer_delivery', true),
                    'pickup'      => $this->defaultOption('offer_pickup', true),
                    'description' => $this->defaultOption('offer_description', 'modResource.introtext'),
                ];
            default:
                return [];
        }
    }

    public function defaultAttributes(string $tag): array
    {
        switch ($tag) {
            case self::NODE_OFFER:
                return [
                    'id'   => $this->defaultOption('offer_attr_id', 'modResource.id'),
                    'type' => $this->defaultOption('offer_attr_type', YandexMarket::TYPE_SIMPLE)
                ];
            case self::NODE_ROOT:
                return [
                    'date' => $this->defaultOption('yml_catalog_attr_date', 'Pricelist.generated_on')
                ];
            default:
                return [];
        }
    }

    /**
     * @param  string  $key
     * @param  null  $default
     *
     * @return mixed
     */
    protected function defaultOption(string $key, $default = null)
    {
        return $this->xpdo->getOption('ym_default_'.$key, null, $default);
    }

}