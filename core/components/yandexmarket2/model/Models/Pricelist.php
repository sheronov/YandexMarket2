<?php

namespace YandexMarket\Models;

use DateTimeImmutable;
use Exception;
use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Marketplaces\YandexMarket;
use ymCategory;
use ymField;
use ymPricelist;

/**
 * @property int $id
 * @property string $name
 * @property string $file
 * @property bool $active
 * @property int $type
 * @property DateTimeImmutable $created_on
 * @property null|DateTimeImmutable $edited_on
 * @property null|DateTimeImmutable $generated_on
 * @property null|int $generate_mode
 * @property null|int $generate_interval
 * @property bool $need_generate
 * @property null|string $where
 * @property null|array $properties //make here array by default
 */
class Pricelist extends BaseObject
{

    public static function getObjectClass(): string
    {
        return ymPricelist::class;
    }

    /**
     * @return Field[]|array
     */
    public function getFields(): array
    {
        return array_map(function (ymField $field) {
            return new Field($this->xpdo, $field);
        }, $this->object->getMany('Fields'));
    }

    /**
     * @return Category[]|array
     */
    public function getCategories(): array
    {
        return array_map(function (ymCategory $category) {
            return new Category($this->xpdo, $category);
        }, $this->object->getMany('Categories'));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        // TODO: может сделать группы для полей [shop, categories, offer] (чтобы на фронте легче разбивать по группам)
        $data['fields'] = $this->getFieldsData(); // fields[ 'shop' => getFieldsData(), 'categories' => (), 'offer' => getOfferFields()]

        $data['values'] = [
            'shop'       => $this->getShopData(),
            'categories' => array_values(array_map(static function (Category $categoryObject) {
                return $categoryObject->resource_id;
            }, $this->getCategories())),
            'offer'      => $this->getOfferData()
        ];

        $data['offer_fields'] = $this->getOfferFields();

        $data['shop'] = $this->getShopData();
        $data['categories'] = array_values(array_map(static function (Category $categoryObject) {
            return $categoryObject->resource_id;
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
        // TODO: тут нужно сделать иначе, по ID из БД например, чтобы избежать множественных значений внутри одного
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


    protected function getFieldsData(): array
    {
        if ($marketplace = Marketplace::getMarketPlace($this->object->get('type'))) {
            return $marketplace::getShopFields();
        }
        return [];
    }
}