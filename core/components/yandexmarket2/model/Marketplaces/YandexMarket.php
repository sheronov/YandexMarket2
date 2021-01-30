<?php

namespace YandexMarket\Marketplaces;

use xPDO;
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

    public static function getKey(): string
    {
        return 'yandex.market';
    }

    public static function getDefaultFields(xPDO $xpdo): array
    {
        return [
            'yml_catalog' => [
                'type'       => Field::TYPE_PARENT,
                'editable'   => false, //it means also required=true and cant add another child
                'attributes' => [
                    'date' => [
                        'type'     => Attribute::TYPE_DATE,
                        'required' => true,
                    ]
                ],
                'fields'     => [
                    'shop' => [
                        'type'     => Field::TYPE_PARENT,
                        'editable' => false,
                        'fields'   => self::getShopFields($xpdo)
                    ]
                ]
            ]
        ];
    }

    public static function getShopFields(xPDO $xpdo): array
    {
        return [
            'name'                  => [
                'required' => true,
                'type'     => Field::TYPE_TEXT,
                'default'  => $xpdo->getOption('site_name')
            ],
            'company'               => [
                'required' => true,
                'type'     => Field::TYPE_TEXT,
            ],
            'url'                   => [
                'required' => true,
                'type'     => Field::TYPE_TEXT,
                'default'  => $xpdo->getOption('site_url')
            ],
            'platform'              => [
                'type'    => Field::TYPE_TEXT,
                'default' => 'MODX Revolution',
            ],
            'version'               => [
                'type'    => Field::TYPE_TEXT,
                'default' => $xpdo->getOption('settings_version')
            ],
            'agency'                => [
                'type' => Field::TYPE_TEXT,
            ],
            'email'                 => [
                'type' => Field::TYPE_TEXT,
            ],
            'currencies'            => [
                'type'     => Field::TYPE_CURRENCIES,
                'required' => true,
                'values'   => ['RUR', 'UAH', 'BYN', 'KZT', 'USD', 'EUR'],
                'default'  => ['RUR']
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
                'type'    => Field::TYPE_BOOLEAN,
                'default' => null
            ],
            'offers'                => [
                'type'     => Field::TYPE_PARENT,
                'required' => true,
                'fields'   => [
                    'offer' => [
                        'type'       => Field::TYPE_OFFER,
                        'attributes' => [
                            'id'   => [
                                'required' => true,
                                'type'     => Attribute::TYPE_VALUE,
                                'default'  => 'modResource.id'
                            ],
                            'type' => [
                                'type'     => Attribute::TYPE_SELECT,
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
                                'default'  => self::TYPE_SIMPLE
                            ],
                            'bid'  => [
                                'type'     => Attribute::TYPE_VALUE,
                                'optional' => true,
                            ]
                        ],
                        'fields'     => self::getOfferFields($xpdo)
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

    public static function getOfferFields(xPDO $xpdo): array
    {
        return [
            'name'                  => [
                'type'    => Field::TYPE_DEFAULT,
                'default' => 'modResource.pagetitle',
                //'required' => true //при vendor.model необязательно
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
                'default'  => 'offer.url',
            ],
            'price'                 => [
                'type'     => Field::TYPE_NUMBER,
                'required' => true,
            ],
            'param'                 => [
                'type'       => Field::TYPE_OFFER_PARAM,
                'multiple'   => true,
                'attributes' => [
                    'name' => [
                        'type'       => Attribute::TYPE_TEXT,
                        'required'   => true,
                        'attributes' => [
                            'from' => [
                                'type'     => Attribute::TYPE_BOOLEAN,
                                'default'  => true,
                                'optional' => true,
                            ]
                        ],
                    ],
                    'unit' => [
                        'type' => Attribute::TYPE_TEXT
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
                'default'  => 'RUR'
            ],
            'categoryId'            => [
                'type'     => Field::TYPE_NUMBER,
                'required' => true,
            ],
            'picture'               => [
                'type' => Field::TYPE_OFFER_PICTURES,
            ],
            'supplier'              => [
                'type'       => Field::TYPE_DEFAULT,
                'optional'   => true,
                'attributes' => [
                    'ogrn' => [
                        'required' => true,
                        'type'     => Attribute::TYPE_VALUE
                    ]
                ]
            ],
            'delivery'              => [
                'type'    => Field::TYPE_BOOLEAN,
                'default' => true,
            ],
            'pickup'                => [
                'type'    => Field::TYPE_BOOLEAN,
                'default' => true,
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
                'type'    => Field::TYPE_CDATA,
                'default' => 'modResource.introtext',
                'length'  => 3000
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