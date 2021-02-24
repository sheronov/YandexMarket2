<?php

namespace YandexMarket\Marketplaces;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use modX;
use YandexMarket\Models\Field;

abstract class Marketplace
{
    protected $modx;

    public function __construct(modX $modx)
    {
        $this->modx = $modx;
    }

    /**
     * Уникальный ключ маркетплейса. Нужен для лексиконов. Само название задаётся в лексиконе ym_marketplace_{key}
     * Для лексиконов к полю идёт двойной поиск ym_{key}_{parent}_{field}, а потом ym_{key}_{field}
     * Название полей - ym_{key}_{parent}_{field} если отсутствует, то ym_{key}_{field} (где {parent} - родитель узла)
     * Расширенная подсказка к полю - ym_{key}_{field}_help
     * Название атрибута поля - ym_{parent}_attr_{attribute} or ym_{key}_{field}_attr_{attribute}
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
     * @return array<string,array>
     * @throws Exception
     */
    public static function listMarketplaces(): array
    {
        $marketplaces = [];
        $classes = ClassFinder::getClassesInNamespace('YandexMarket\Marketplaces');

        foreach ($classes as $class) {
            /** @noinspection SelfClassReferencingInspection */
            if (is_subclass_of($class, Marketplace::class)) {
                $marketplaces[$class::getKey()] = [
                    'class'        => $class,
                    'key'          => $class::getKey(),
                    'shop_fields'  => $class::getShopFields(),
                    'offer_fields' => $class::getOfferFields()
                ];
            }
        }

        return $marketplaces;
    }

    /**
     * @param  string  $type
     * @param  modX  $modx
     *
     * @return Marketplace|null
     */
    public static function getMarketPlace(string $type, modX $modx): ?Marketplace
    {
        try {
            $marketplaces = self::listMarketplaces();
        } catch (Exception $e) {
            $marketplaces = [];
        }
        /** @noinspection SelfClassReferencingInspection */
        if (($class = $marketplaces[$type]['class'] ?? null) && is_subclass_of($class, Marketplace::class)) {
            return new $class($modx);
        }

        return null;
    }

    /**
     * По умолчанию значение попадёт в столбец value
     * Если вернуть массив - то можно указать ['value'=>modResource.field, 'handler' => '{$input ? 'Да' : 'Нет'}
     *
     * @return array[]
     */
    public function defaultValues(): array
    {
        return [
            Field::TYPE_SHOP  => [
                'name'                  => $this->getOption('shop_name', $this->modx->getOption('site_name')),
                'url'                   => $this->getOption('shop_url', $this->modx->getOption('site_url')),
                'platform'              => $this->getOption('shop_platform', 'MODX Revolution'),
                'version'               => $this->getOption('shop_version', $this->modx->getOption('settings_version')),
                'currencies'            => [
                    'value' => explode(',', $this->getOption('shop_currencies', 'RUB'))
                ],
                'enable_auto_discounts' => $this->getOption('shop_enable_auto_discounts', true) ? 'true' : 'false'
            ],
            Field::TYPE_OFFER => [
                'name'        => $this->getOption('offer_name', 'pagetitle'),
                'url'         => $this->getOption('offer_url', 'Offer.url'), // Offer собирающий класс
                'price'       => $this->getOption('offer_price', 'Offer.price'), // Offer собирающий класс
                'currencyId'  => $this->getOption('offer_currency_id', 'RUB'),
                'categoryId'  => $this->getOption('offer_parent', 'parent'),
                'delivery'    => $this->getOption('offer_delivery', 'true'),
                'pickup'      => $this->getOption('offer_pickup', 'true'),
                'description' => $this->getOption('offer_description', 'introtext'),
            ]
        ];
    }

    public function defaultAttributes(): array
    {
        return [
            Field::TYPE_ROOT  => [
                'date' => $this->getOption('yml_catalog_attr_date', 'Pricelist.generated_on')
            ],
            Field::TYPE_OFFER => [
                'id'   => $this->getOption('offer_attr_id', 'modResource.id'),
                'type' => $this->getOption('offer_attr_type', YandexMarket::TYPE_SIMPLE)
            ],
        ];
    }

    /**
     * @param  string  $key
     * @param  null  $default
     *
     * @return mixed
     */
    protected function getOption(string $key, $default = null)
    {
        return $this->modx->getOption('ym_default_'.$key, null, $default);
    }
}