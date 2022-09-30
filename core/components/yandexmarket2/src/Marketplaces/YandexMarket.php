<?php

namespace YandexMarket\Marketplaces;

use YandexMarket\Models\Attribute;
use YandexMarket\Models\Field;
use YandexMarket\Service;

class YandexMarket extends Marketplace
{
    const TYPE_SIMPLE        = '';
    const TYPE_CUSTOM        = 'vendor.model';
    const TYPE_ALCOHOL       = 'alco';
    const TYPE_AUDIOBOOKS    = 'audiobook';
    const TYPE_EVENT_TICKETS = 'event-ticket';
    const TYPE_BOOKS         = 'book';
    const TYPE_DRUGS         = 'medicine';
    const TYPE_MUSIC_VIDEO   = 'artist.title';
    const TYPE_TOURS         = 'tour';

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
                        'type'     => Attribute::TYPE_VALUE,
                        'required' => true
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
                'type'     => Field::TYPE_TEXT,
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
                'type' => Field::TYPE_TEXT,
            ],
            'version'               => [
                'type' => Field::TYPE_TEXT,
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
                'values'   => ['RUB', 'UAH', 'BYN', 'KZT', 'USD', 'EUR'],
            ],
            'categories'            => [
                'type'     => Field::TYPE_CATEGORIES,
                'required' => true,
                'fields'   => [
                    'category' => [
                        'type'       => Field::TYPE_CATEGORY,
                        'required'   => true,
                        'attributes' => [
                            'id'       => [
                                'required' => true,
                                'type'     => Attribute::TYPE_VALUE,
                            ],
                            'parentId' => [
                                'required' => true,
                                'type'     => Attribute::TYPE_VALUE
                            ]
                        ]
                    ]
                ]
            ],
            'delivery-options'      => [
                'type' => Field::TYPE_PARENT,
            ],
            'pickup-options'        => [
                'type' => Field::TYPE_PARENT
            ],
            'enable_auto_discounts' => [
                'type' => Field::TYPE_TEXT,
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
                                'type'     => Attribute::TYPE_VALUE,
                            ],
                            'type' => [
                                'type'     => Attribute::TYPE_TEXT,
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
                                'type'     => Attribute::TYPE_VALUE,
                                'optional' => true,
                            ]
                        ],
                        'fields'     => [] //fields will be passed here automatically (cause TYPE_OFFER)
                    ]
                ]
            ],
            'gifts'                 => [
                'type' => Field::TYPE_PARENT,
            ],
            'promos'                => [
                'type' => Field::TYPE_PARENT,
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
                'type'       => Field::TYPE_VALUE,
                'required'   => true,
                'attributes' => [
                    'from' => [
                        'type'     => Attribute::TYPE_TEXT,
                        'optional' => true,
                    ]
                ],
            ],
            'param'                 => [
                'type'       => Field::TYPE_DEFAULT,
                'multiple'   => true,
                'attributes' => [
                    'name' => [
                        'type'     => Attribute::TYPE_TEXT,
                        'required' => true,
                    ],
                    'unit' => [
                        'type' => Attribute::TYPE_TEXT
                    ]
                ]
            ],
            'oldprice'              => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true,
            ],
            'purchase_price'        => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'enable_auto_discount'  => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'currencyId'            => [
                'type'     => Field::TYPE_TEXT,
                'required' => true,
            ],
            'categoryId'            => [
                'type'     => Field::TYPE_VALUE,
                'required' => true,
            ],
            'picture'               => [
                'type'     => Field::TYPE_PICTURE,
                'multiple' => true,
                'count'    => 10
            ],
            'supplier'              => [
                'type'       => Field::TYPE_EMPTY,
                'optional'   => true,
                'attributes' => [
                    'ogrn' => [
                        'required' => true,
                        'type'     => Attribute::TYPE_VALUE
                    ]
                ]
            ],
            'delivery'              => [
                'type' => Field::TYPE_TEXT,
            ],
            'pickup'                => [
                'type' => Field::TYPE_TEXT,
            ],
            'delivery-options'      => [
                'type'     => Field::TYPE_PARENT,
                'optional' => true
            ],
            'pickup-options'        => [
                'type'     => Field::TYPE_PARENT,
                'optional' => true
            ],
            'store'                 => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'description'           => [
                'type'   => Field::TYPE_CDATA_VALUE,
                'length' => 3000
            ],
            'sales_notes'           => [
                'type'   => Field::TYPE_DEFAULT,
                'length' => 50
            ],
            'min-quantity'          => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'manufacturer_warranty' => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'country_of_origin'     => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true
            ],
            'adult'                 => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'barcode'               => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true
            ],
            'condition'             => [
                'type'     => Field::TYPE_PARENT,
                'optional' => true
            ],
            'credit-template'       => [
                'type'     => Field::TYPE_EMPTY,
                'optional' => true
            ],
            'expiry'                => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'weight'                => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'dimensions'            => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'downloadable'          => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
            'age'                   => [
                'type'     => Field::TYPE_VALUE,
                'optional' => true
            ],
        ];
    }

    public function defaultValues(): array
    {
        $values = [
            Field::TYPE_SHOP       => [
                'name'                  => $this->getOption('shop_name', $this->modx->getOption('site_name')),
                'url'                   => $this->getOption('shop_url',
                    $this->modx->getOption('yandexmarket2_site_url', null, $this->modx->getOption('site_url'), true)),
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
                'date' => [
                    'value'   => $this->getOption('root_attr_date', 'Pricelist.generated_on'),
                    'handler' => '{$input | date: "Y-m-d\TH:i:sP"}'
                ]
            ],
            Field::TYPE_OFFER    => [
                'id'   => $this->getOption('offer_attr_id', 'id'),
                'type' => $this->getOption('offer_attr_type', self::TYPE_SIMPLE)
            ],
            Field::TYPE_CATEGORY => [
                'id'       => $this->getOption('category_attr_id', 'id'),
                'parentId' => $this->getOption('category_attr_parent_id', 'parent')
            ]
        ];
    }

}