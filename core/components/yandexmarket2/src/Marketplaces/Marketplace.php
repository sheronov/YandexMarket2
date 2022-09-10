<?php

namespace YandexMarket\Marketplaces;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use YandexMarket\Models\Field;
use YandexMarket\Service;

abstract class Marketplace
{
    protected $modx;

    /**
     * @param  \MODX\Revolution\modX|\modX  $modx
     */
    public function __construct($modx)
    {
        $this->modx = $modx;
    }

    /**
     * Уникальный ключ маркетплейса. Нужен для лексиконов. Само название задаётся в лексиконе ym2_marketplace_{key}
     * Для лексиконов к полю идёт двойной поиск ym2_{key}_{parent}_{field}, а потом ym2_{key}_{field}
     * Название полей - ym2_{key}_{parent}_{field} если отсутствует, то ym2_{key}_{field} (где {parent} - родитель узла)
     * Расширенная подсказка к полю - ym2_{key}_{field}_help
     * Название атрибута поля - ym2_{parent}_attr_{attribute} or ym2_{key}_{field}_attr_{attribute}
     * Если есть поля или атрибуты с выбором из нескольких значений, то к лексикону названия добавляется _value_{val}
     *
     * @return string
     */
    abstract public static function getKey(): string;

    /**
     * Неймспейс(тема) лексикона (обычно название файла из папки /lexicon/lang без .inc.php)
     */
    public static function getLexiconNs(): string
    {
        return 'yandexmarket2:'.str_replace('.', '', static::getKey());
    }

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
                    'lexicon'      => $class::getLexiconNs(),
                    'shop_fields'  => $class::getShopFields(),
                    'offer_fields' => $class::getOfferFields()
                ];
            }
        }

        return $marketplaces;
    }

    /**
     * @param  string  $type
     * @param  \MODX\Revolution\modX|\modX  $modx
     *
     * @return Marketplace|null
     */
    public static function getMarketPlace(string $type, $modx)
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
        $values = [
            Field::TYPE_SHOP       => [
                'name'                  => $this->getOption('shop_name', $this->modx->getOption('site_name')),
                'url'                   => $this->getOption('shop_url', $this->modx->getOption('yandexmarket2_site_url', null, $this->modx->getOption('site_url'), true)),
                'platform'              => $this->getOption('shop_platform', 'MODX Revolution'),
                'version'               => $this->getOption('shop_version', $this->modx->getOption('settings_version')),
                'currencies'            => [
                    'value' => explode(',', $this->getOption('shop_currencies', 'RUB'))
                ],
                'enable_auto_discounts' => $this->getOption('shop_enable_auto_discounts', true) ? 'true' : 'false'
            ],
            Field::TYPE_OFFER      => [
                'name'        => $this->getOption('offer_name', 'pagetitle'),
                'url'         => $this->getOption('offer_url', 'Offer.url'), // Offer собирающий класс
                'price'       => $this->getOption('offer_price', 'Offer.price'), // Offer собирающий класс
                'currencyId'  => $this->getOption('offer_currency_id', 'RUB'),
                'categoryId'  => $this->getOption('offer_category_id', 'parent'),
                'delivery'    => $this->getOption('offer_delivery', 'true'),
                'pickup'      => $this->getOption('offer_pickup', 'true'),
                'description' => $this->getOption('offer_description', 'introtext'),
            ],
            Field::TYPE_CATEGORIES => [
                'category' => $this->getOption('categories_category', 'pagetitle')
            ]
        ];

        if ($this->getOption('ms2gallery_sync_ms2', false, '')) {
            // интеграция ms2Gallery с ms2
            $values[Field::TYPE_OFFER]['picture'] = 'ms2Gallery.image';
        } elseif (Service::hasMiniShop2()) {
            $values[Field::TYPE_OFFER]['picture'] = 'msGallery.image';
        }

        return $values;
    }

    public function defaultAttributes(): array
    {
        return [
            Field::TYPE_ROOT     => [
                'date' => $this->getOption('root_attr_date', 'Pricelist.generated_on')
            ],
            Field::TYPE_OFFER    => [
                'id'   => $this->getOption('offer_attr_id', 'id'),
                'type' => $this->getOption('offer_attr_type', YandexMarket::TYPE_SIMPLE)
            ],
            Field::TYPE_CATEGORY => [
                'id'       => $this->getOption('category_attr_id', 'id'),
                'parentId' => $this->getOption('category_attr_parent_id', 'parent')
            ]
        ];
    }

    public function getChildrenFieldsForType(int $type)
    {
        switch ($type) {
            case Field::TYPE_ROOT:
                return $this::getRootFields();
            case Field::TYPE_SHOP:
                return $this::getShopFields();
            case Field::TYPE_OFFER:
                return $this::getOfferFields();
            case Field::TYPE_OFFERS:
            case Field::TYPE_OFFERS_TRANSPARENT:
            case Field::TYPE_CATEGORIES:
            case Field::TYPE_CATEGORIES_TRANSPARENT:
                $shopFields = $this::getShopFields();
                foreach ($shopFields as $shopField) {
                    if ($type === ($shopField['type'] ?? Field::TYPE_DEFAULT) && !empty($shopField['fields'])) {
                        return $shopField['fields'];
                    }
                }
                break;
        }
        return null;
    }

    /**
     * @param  string  $key
     * @param  null  $default
     * @param  string  $prefix
     *
     * @return mixed
     */
    protected function getOption(string $key, $default = null, string $prefix = 'ym2_default_')
    {
        return $this->modx->getOption($prefix.$key, null, $default);
    }

}
