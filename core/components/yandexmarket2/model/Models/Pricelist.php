<?php

namespace YandexMarket\Models;

use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Marketplaces\YandexMarket;
use ymCategory;
use ymPricelist;

class Pricelist extends BaseObject
{
    /** @var ymPricelist $object */

    public const OBJECT_CLASS = ymPricelist::class;

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = $this->object->toArray();

        // TODO: может сделать группы для полей [shop, categories, offer] (чтобы на фронте легче разбивать по группам)
        $data['fields'] = $this->getFieldsData(); // fields[ 'shop' => getFieldsData(), 'categories' => (), 'offer' => getOfferFields()]

        $data['values'] = [
            'shop'       => $this->getShopData(),
            'categories' => array_values(array_map(static function (ymCategory $categoryObject) {
                return $categoryObject->get('category_id');
            }, $this->getCategories())),
            'offer'      => $this->getOfferData()
        ];

        $data['offer_fields'] = $this->getOfferFields();

        $data['shop'] = $this->getShopData();
        $data['categories'] = array_values(array_map(static function (ymCategory $categoryObject) {
            return $categoryObject->get('category_id');
        }, $this->getCategories()));
        $data['offer'] = $this->getOfferData();

        return $data;
    }

    public function getShopData(): array
    {
        return [
            'name'                  => $this->xpdo->getOption('site_name', 'Test'),
            'company'               => 'Рога и копыта',
            'url'                   => $this->xpdo->getOption('site_url'),
            'currencies'            => ['RUB'],
            'enable_auto_discounts' => false,
            'platform'              => 'MODX Revolution',
            'version'               => $this->xpdo->getOption('settings_version')
        ];
    }

    public function getOfferData(): array
    {
        return [
            'offer' => [
                'attributes' => [
                    'id'   => 'modResource.id',
                    'type' => ''
                ]
            ],
            'name'  => 'modResource.pagetitle',
            'param' => [
                [
                    'attributes' => [
                        'name' => ['handler' => 'Цвет']
                    ],
                    'column'     => 'Option.color'
                ],
                [
                    'attributes' => [
                        'name' => ['handler' => 'Размер']
                    ],
                    'handler'    => '@INLINE {$Data.color[0]}'
                ],
            ]
        ];
    }

    public function getOfferFields(): array
    {
        return YandexMarket::getOfferFields();
    }

    public function getCategories(): array
    {
        return $this->object->getMany('Categories');
    }

    protected function getFieldsData(): array
    {
        if ($marketplace = Marketplace::getMarketPlace($this->object->get('type'))) {
            return $marketplace::getShopFields();
        }
        return [];
    }

}