<?php

namespace YandexMarket;

use YandexMarket\Marketplaces\Marketplace;
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
        $data['fields'] = $this->getFieldsData();
        // TODO: может сделать группы для полей (чтобы на фронте легче разбивать по группам)

        $data['shop'] = $this->getShopData();
        $data['categories'] = array_values(array_map(static function (ymCategory $categoryObject) {
            return $categoryObject->get('category_id');
        }, $this->getCategories()));

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
            'platform'              => 'MODX'
        ];
    }

    public function getCategories(): array
    {
        return $this->object->getMany('Categories');
    }

    protected function getFieldsData(): array
    {
        if($marketplace = Marketplace::getMarketPlace($this->object->get('type'))) {
            return $marketplace::getFields();
        }
        return [];
    }

}