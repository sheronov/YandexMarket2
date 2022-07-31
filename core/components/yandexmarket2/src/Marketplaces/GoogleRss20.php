<?php

namespace YandexMarket\Marketplaces;

use YandexMarket\Models\Attribute;
use YandexMarket\Models\Field;
use YandexMarket\Service;

class GoogleRss20 extends Marketplace
{

    /**
     * @inheritDoc
     */
    public static function getKey(): string
    {
        return 'google.rss20';
    }

    /**
     * @inheritDoc
     */
    public static function getRootFields(): array
    {
        return [
            'rss' => [
                'required'   => true,
                'type'       => Field::TYPE_ROOT,
                'attributes' => [
                    'xmlns:g' => [
                        'type'     => Attribute::TYPE_TEXT,
                        'required' => true,
                    ],
                    'version' => [
                        'type'     => Attribute::TYPE_TEXT,
                        'required' => true
                    ]
                ],
                'fields'     => [
                    'channel' => [
                        'type'   => Field::TYPE_SHOP,
                        'fields' => [] //the fields will be passed here automatically (cause TYPE_SHOP)
                    ]
                ],
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getShopFields(): array
    {
        return [
            'title'       => [
                'required' => true,
                'type'     => Field::TYPE_TEXT
            ],
            'link'        => [
                'required' => true,
                'type'     => Field::TYPE_TEXT,
            ],
            'description' => [
                'type' => Field::TYPE_TEXT
            ],
            'items'       => [
                'required' => true,
                'type'     => Field::TYPE_OFFERS_TRANSPARENT,
                'fields'   => [
                    'item' => [
                        'type'     => Field::TYPE_OFFER,
                        'required' => true,
                        'fields'   => [] //fields will be passed here automatically (cause TYPE_OFFER)
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getOfferFields(): array
    {
        return [
            'g:id'                        => [
                'type'     => Field::TYPE_DEFAULT,
                'required' => true
            ],
            'g:title'                     => [
                'type'     => Field::TYPE_DEFAULT,
                'required' => true
            ],
            'g:description'               => [
                'type'     => Field::TYPE_DEFAULT,
                'required' => true
            ],
            'g:link'                      => [
                'type'     => Field::TYPE_DEFAULT,
                'required' => true
            ],
            'g:image_link'                => [
                'type'     => Field::TYPE_DEFAULT,
                'required' => true
            ],
            'g:availability'              => [
                'type'     => Field::TYPE_TEXT,
                'required' => true
            ],
            'g:availability_date'         => [
                'type'     => Field::TYPE_TEXT,
                'optional' => true
            ],
            'g:price'                     => [
                'type'     => Field::TYPE_DEFAULT,
                'required' => true
            ],
            'g:brand'                     => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true
            ],
            'g:gtin'                      => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:mpn'                       => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:identifier_exists'         => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true
            ],
            'g:additional_image_link'     => [
                'type'     => Field::TYPE_PICTURE,
                'optional' => true
            ],
            'g:google_product_category'   => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:product_type'              => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:sale_price'                => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:sale_price_effective_date' => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:condition'                 => [
                'type'     => Field::TYPE_TEXT,
                'optional' => true
            ],
            'g:adult'                     => [
                'type'     => Field::TYPE_TEXT,
                'optional' => true
            ],
            'g:gender'                    => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:age_group'                 => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:color'                     => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:material'                  => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:pattern'                   => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:size'                      => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
            'g:item_group_id'             => [
                'type'     => Field::TYPE_DEFAULT,
                'optional' => true,
            ],
        ];
    }

    public function defaultAttributes(): array
    {
        return [
            Field::TYPE_ROOT => [
                'xmlns:g' => 'http://base.google.com/ns/1.0',
                'version' => '2.0'
            ]
        ];
    }

    public function defaultValues(): array
    {
        return [
            Field::TYPE_SHOP  => [
                'title'       => $this->getOption('channel_title', $this->modx->getOption('site_name')),
                'link'        => $this->getOption('channel_url', $this->modx->getOption('yandexmarket2_site_url', null, $this->modx->getOption('site_url'), true)),
                'description' => $this->getOption('channel_description', ''),
            ],
            Field::TYPE_OFFER => [
                'g:id'           => $this->getOption('item_id', 'id'),
                'g:title'        => $this->getOption('item_title', 'pagetitle'),
                'g:description'  => $this->getOption('item_description', 'description'),
                'g:link'         => $this->getOption('item_link', 'Offer.url'), // Offer собирающий класс
                'g:image_link'   => $this->getOption('item_image_link', 'Offer.image'),
                'g:price'        => [
                    'value' => $this->getOption('item_price', 'Offer.price'), // Offer собирающий класс
                    'handler' => '{$input} RUB'
                ],
                'g:availability' => $this->getOption('item_availability', 'in_stock'),
                'g:brand'        => $this->getOption('item_brand', Service::hasMiniShop2() ? 'Vendor.name' : ''),
            ]
        ];
    }
}
