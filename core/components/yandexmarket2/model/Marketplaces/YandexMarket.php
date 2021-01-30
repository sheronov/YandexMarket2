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

    public static function getKey(): string
    {
        return 'yandex.market';
    }

    public static function getDefaultFields(): array
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
                        'fields'   => self::getShopFields()
                    ]
                ]
            ]
        ];
    }

    public static function getShopFields(): array
    {
        return [
            'name'                  => [
                'required' => true,
                'type'     => Field::TYPE_TEXT
            ],
            'company'               => [
                'required' => true,
                'type'     => Field::TYPE_TEXT,
            ],
            'url'                   => [
                'required' => true,
                'type'     => Field::TYPE_TEXT,
            ],
            'platform'              => [
                'required' => false,
                'type'     => Field::TYPE_TEXT,
            ],
            'version'               => [
                'required' => false,
                'type'     => Field::TYPE_TEXT
            ],
            'agency'                => [
                'required' => false,
                'type'     => Field::TYPE_TEXT
            ],
            'email'                 => [
                'required' => false,
                'type'     => Field::TYPE_TEXT
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
                'required' => false,
                'type'     => Field::TYPE_FEATURE,
            ],
            'pickup-options'        => [
                'required' => false,
                'type'     => Field::TYPE_FEATURE
            ],
            'enable_auto_discounts' => [
                'type'     => Field::TYPE_BOOLEAN,
                'required' => false
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
                                'type'     => Attribute::TYPE_VALUE
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
                                ]
                            ],
                            'bid'  => [
                                'type' => Attribute::TYPE_VALUE
                            ]
                        ],
                        'fields'     => self::getOfferFields()
                    ]
                ]
            ],
            'gifts'                 => [
                'component' => Field::TYPE_FEATURE,
                'disabled'  => true,
            ],
            'promos'                => [
                'component' => Field::TYPE_FEATURE,
                'disabled'  => true,
            ]
        ];
    }

    public static function getOfferFields(): array
    {
        return [
            'name'  => [
                'url'      => 'https://yandex.ru/support/partnermarket/elements/name.html#vendor-name-model',
                'type'     => Field::TYPE_OFFER_FIELD,
                'required' => true
            ],
            'url'   => [
                'type'     => Field::TYPE_OFFER_FIELD,
                'required' => true,
            ],
            'param' => [
                'type'       => Field::TYPE_OFFER_PARAM,
                'attributes' => [
                    'name' => [
                        'type'     => Attribute::TYPE_TEXT,
                        'required' => true,
                    ],
                    'unit' => [
                        'type' => Attribute::TYPE_TEXT
                    ]
                ]
            ]
        ];
    }

}