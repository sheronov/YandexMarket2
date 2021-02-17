<?php

namespace YandexMarket\Marketplaces;

use YandexMarket\Models\Attribute;
use YandexMarket\Models\Field;

class YandexMarket extends Marketplace
{
    public const TYPE_SIMPLE        = '';
    public const TYPE_CUSTOM        = 'vendor.model';
    public const TYPE_ALCOHOL       = 'alco';
    public const TYPE_AUDIOBOOKS    = 'audiobook';
    public const TYPE_EVENT_TICKETS = 'event-ticket';
    public const TYPE_BOOKS         = 'book';
    public const TYPE_DRUGS         = 'medicine';
    public const TYPE_MUSIC_VIDEO   = 'artist.title';
    public const TYPE_TOURS         = 'tour';

    /** @inheritDoc */
    public static function getKey(): string
    {
        return 'yandex.market';
    }

    /** @inheritDoc */
    public static function getRootFields(): array
    {
        return [
            'yml_catalog' => [
                'required'   => true,
                'type'       => Field::TYPE_ROOT,
                'attributes' => [
                    'date' => [
                        'type'     => Attribute::TYPE_DATE,
                        'required' => true,
                    ]
                ],
                'fields'     => [
                    'shop' => [
                        'type'   => Field::TYPE_SHOP,
                        'fields' => [] //the fields will be passed here automatically (cause TYPE_SHOP)
                    ]
                ],
            ]
        ];
    }

    /** @inheritDoc */
    public static function getShopFields(): array
    {
        return [
            'name'                  => [
                'required' => true,
                'type'     => Field::TYPE_OPTION,
            ],
            'company'               => [
                'required' => true,
                'type'     => Field::TYPE_OPTION,
            ],
            'url'                   => [
                'required' => true,
                'type'     => Field::TYPE_OPTION,
            ],
            'platform'              => [
                'type' => Field::TYPE_OPTION,
            ],
            'version'               => [
                'type' => Field::TYPE_OPTION,
            ],
            'agency'                => [
                'type' => Field::TYPE_OPTION,
            ],
            'email'                 => [
                'type' => Field::TYPE_OPTION,
            ],
            'currencies'            => [
                'type'     => Field::TYPE_CURRENCIES,
                'required' => true,
                'values'   => ['RUB', 'UAH', 'BYN', 'KZT', 'USD', 'EUR'],
            ],
            'categories'            => [
                'type'     => Field::TYPE_CATEGORIES,
                'required' => true,
            ],
            'delivery_options'      => [
                'type' => Field::TYPE_FEATURE,
            ],
            'pickup-options'        => [
                'type' => Field::TYPE_FEATURE
            ],
            'enable_auto_discounts' => [
                'type' => Field::TYPE_BOOLEAN,
            ],
            'offers'                => [
                'type'     => Field::TYPE_OFFERS,
                'required' => true,
                'fields'   => [
                    'offer' => [
                        'type'       => Field::TYPE_OFFER,
                        'required'   => true,
                        'attributes' => [
                            'id'   => [
                                'required' => true,
                                'type'     => Attribute::TYPE_STRING,
                            ],
                            'type' => [
                                'type'     => Attribute::TYPE_STRING,
                                'required' => true,
                                'values'   => [
                                    self::TYPE_SIMPLE,
                                    self::TYPE_CUSTOM,
                                    self::TYPE_ALCOHOL,
                                    self::TYPE_AUDIOBOOKS,
                                    self::TYPE_EVENT_TICKETS,
                                    self::TYPE_BOOKS,
                                    self::TYPE_DRUGS,
                                    self::TYPE_MUSIC_VIDEO,
                                    self::TYPE_TOURS
                                ],
                            ],
                            'bid'  => [
                                'type'     => Attribute::TYPE_STRING,
                                'optional' => true,
                            ]
                        ],
                        'fields'     => [] //fields will be passed here automatically (cause TYPE_OFFER)
                    ]
                ]
            ],
            'gifts'                 => [
                'component' => Field::TYPE_FEATURE,
            ],
            'promos'                => [
                'component' => Field::TYPE_FEATURE,
            ]
        ];
    }

    /** @inheritDoc */
    public static function getOfferFields(): array
    {
        return [
            'name'                  => [
                'type' => Field::TYPE_DEFAULT,
            ],
            'model'                 => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'vendor'                => [
                'type' => Field::TYPE_DEFAULT,
            ],
            'typePrefix'            => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true
            ],
            'vendorCode'            => [
                'type' => Field::TYPE_DEFAULT,
            ],
            'url'                   => [
                'type'     => Field::TYPE_DEFAULT,
                'required' => true,
            ],
            'price'                 => [
                'type'       => Field::TYPE_NUMBER,
                'required'   => true,
                'attributes' => [
                    'from' => [
                        'type'     => Attribute::TYPE_BOOLEAN,
                        'optional' => true,
                    ]
                ],
            ],
            'param'                 => [
                'type'       => Field::TYPE_PARAM, // TODO: тут убрать тип, так как можно выбирать
                'multiple'   => true,
                'attributes' => [
                    'name' => [
                        'type'     => Attribute::TYPE_STRING,
                        'required' => true,
                    ],
                    'unit' => [
                        'type' => Attribute::TYPE_STRING
                    ]
                ]
            ],
            'oldprice'              => [
                'type'     => Field::TYPE_NUMBER,
                'optional' => true,
            ],
            'purchase_price'        => [
                'type'     => Field::TYPE_NUMBER,
                'optional' => true
            ],
            'enable_auto_discount'  => [
                'type'     => Field::TYPE_BOOLEAN,
                'optional' => true
            ],
            'currencyId'            => [
                'type'     => Field::TYPE_DEFAULT,
                'required' => true,
            ],
            'categoryId'            => [
                'type'     => Field::TYPE_NUMBER,
                'required' => true,
            ],
            'picture'               => [
                'type' => Field::TYPE_PICTURES,
            ],
            'supplier'              => [
                'type'       => Field::TYPE_DEFAULT,
                'optional'   => true,
                'attributes' => [
                    'ogrn' => [
                        'required' => true,
                        'type'     => Attribute::TYPE_STRING
                    ]
                ]
            ],
            'delivery'              => [
                'type' => Field::TYPE_BOOLEAN,
            ],
            'pickup'                => [
                'type' => Field::TYPE_BOOLEAN,
            ],
            'delivery-options'      => [
                'type'     => Field::TYPE_FEATURE,
                'optional' => true
            ],
            'pickup-options'        => [
                'type'     => Field::TYPE_FEATURE,
                'optional' => true
            ],
            'store'                 => [
                'type'     => Field::TYPE_BOOLEAN,
                'optional' => true
            ],
            'description'           => [
                'type'   => Field::TYPE_CDATA,
                'length' => 3000
            ],
            'sales_notes'           => [
                'type'   => Field::TYPE_DEFAULT,
                'length' => 50
            ],
            'min-quantity'          => [
                'type'     => Field::TYPE_NUMBER,
                'optional' => true
            ],
            'manufacturer_warranty' => [
                'type'     => Field::TYPE_BOOLEAN,
                'optional' => true
            ],
            'country_of_origin'     => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true
            ],
            'adult'                 => [
                'type'     => Field::TYPE_BOOLEAN,
                'optional' => true
            ],
            'barcode'               => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true
            ],
            'condition'             => [
                'type'     => Field::TYPE_FEATURE,
                'optional' => true
            ],
            'credit-template'       => [
                'type'     => Field::TYPE_FEATURE,
                'optional' => true
            ],
            'expiry'                => [
                'type'     => Field::TYPE_FEATURE,
                'optional' => true
            ],
            'weight'                => [
                'type'     => Field::TYPE_NUMBER,
                'optional' => true
            ],
            'dimensions'            => [
                'type'     => Field::TYPE_FEATURE,
                'optional' => true
            ],
            'downloadable'          => [
                'type'     => Field::TYPE_BOOLEAN,
                'optional' => true
            ],
            'age'                   => [
                'type'     => Field::TYPE_FEATURE,
                'optional' => true
            ],
        ];
    }

}