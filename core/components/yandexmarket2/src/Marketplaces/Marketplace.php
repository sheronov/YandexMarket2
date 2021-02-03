<?php

namespace YandexMarket\Marketplaces;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use xPDO;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

abstract class Marketplace
{
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
     * @return array<string,string>
     * @throws Exception
     */
    public static function listMarketplaces(): array
    {
        $marketplaces = [];
        $classes = ClassFinder::getClassesInNamespace('YandexMarket\Marketplaces');

        foreach ($classes as $class) {
            /** @noinspection SelfClassReferencingInspection */
            if (is_subclass_of($class, Marketplace::class)) {
                $marketplaces[$class::getKey()] = $class;
                // [
                // 'class' => $class,
                // 'key'   => $class::getKey(),
                // 'fields' => [
                //     self::NODE_SHOP  => $class::getShopFields(),
                //     self::NODE_OFFER => $class::getOfferFields()
                //    ]
                // ];
            }
        }

        return $marketplaces;
    }

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
        try {
            $marketplaces = self::listMarketplaces();
        } catch (Exception $e) {
            $marketplaces = [];
        }
        /** @noinspection SelfClassReferencingInspection */
        if (($class = $marketplaces[$type] ?? null) && is_subclass_of($class, Marketplace::class)) {
            return new $class($xpdo);
        }

        return null;
    }

    public function defaultValues(): array
    {
        return [
            Field::TYPE_SHOP  => [
                'name'       => $this->defaultOption('shop_name', $this->xpdo->getOption('site_name')),
                'url'        => $this->defaultOption('shop_url', $this->xpdo->getOption('site_url')),
                'platform'   => $this->defaultOption('shop_platform', 'MODX Revolution'),
                'version'    => $this->defaultOption('shop_version', $this->xpdo->getOption('settings_version')),
                'currencies' => explode(',', $this->defaultOption('shop_currencies', 'RUB'))
            ],
            Field::TYPE_OFFER => [
                'name'        => $this->defaultOption('offer_name', 'modResource.pagetitle'),
                'url'         => $this->defaultOption('offer_url', 'Offer.url'), // Offer собирающий класс
                'currencyId'  => $this->defaultOption('offer_currency_id', 'RUB'),
                'delivery'    => $this->defaultOption('offer_delivery', true),
                'pickup'      => $this->defaultOption('offer_pickup', true),
                'description' => $this->defaultOption('offer_description', 'modResource.introtext'),
            ]
        ];
    }

    public function defaultAttributes(): array
    {
        return [
            Field::TYPE_ROOT  => [
                'date' => $this->defaultOption('yml_catalog_attr_date', 'Pricelist.generated_on')
            ],
            Field::TYPE_OFFER => [
                'id'   => $this->defaultOption('offer_attr_id', 'modResource.id'),
                'type' => $this->defaultOption('offer_attr_type', YandexMarket::TYPE_SIMPLE)
            ],
        ];
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