<?php

namespace YandexMarket\Marketplaces;

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

    public const OFFER_TYPES = [
        self::TYPE_SIMPLE        => 'Упрощённый тип',
        self::TYPE_CUSTOM        => 'Произвольный тип',
        self::TYPE_ALCOHOL       => 'Алкоголь',
        self::TYPE_AUDIOBOOKS    => 'Аудиокниги',
        self::TYPE_EVENT_TICKETS => 'Билеты на мероприятие',
        self::TYPE_BOOKS         => 'Книги',
        self::TYPE_DRUGS         => 'Лекарства',
        self::TYPE_MUSIC_VIDEO   => 'Музыкальная и видеопродукция',
        self::TYPE_TOURS         => 'Туры'
    ];

    public static function getFields(): array
    {
        // title or help we can add after (from MODX lexicons by this mask "ym_yandex.market_{$field}_title"
        return [];
    }

    public static function getKey(): string
    {
        return 'yandex.market';
    }

    public static function getShopFields(): array
    {
        return [
            [
                'key'      => 'name',
                'title'    => 'Короткое название магазина',
                'help'     => "В названии нельзя использовать слова, которые не относятся к наименованию магазина (например «лучший», «дешевый»), указывать номер телефона и т. п.\n\nНазвание магазина должно совпадать с фактическим названием, которое публикуется на сайте. Если требование не соблюдается, Яндекс.Маркет может самостоятельно изменить название без уведомления магазина.",
                'required' => true
            ],
            [
                'key'      => 'company',
                'title'    => 'Полное наименование компании',
                'help'     => 'Полное наименование компании, владеющей магазином. Не публикуется.',
                'required' => true
            ],
            [
                'key'      => 'url',
                'title'    => 'URL главной страницы магазина',
                'help'     => 'Максимальная длина ссылки — 2048 символов. Допускаются кириллические ссылки. URL‑адрес формируется на основе стандарта RFC 3986',
                'required' => true
            ],
            [
                'key'      => 'platform',
                'title'    => 'CMS магазина',
                'required' => false
            ],
            [
                'key'      => 'version',
                'title'    => 'Версия CMS',
                'required' => false
            ],
            [
                'key'      => 'agency',
                'title'    => 'Агентство, оказывающее техническую поддержку',
                'required' => false
            ],
            [
                'key'      => 'email',
                'title'    => 'Контактный адрес разработчиков',
                'required' => false
            ],
            [
                'key'       => 'currencies',
                'title'     => 'Список курсов валют магазина (первая - основная валюта)',
                'help'      => 'Первая по порядку в списке будет считаться основной валютой, в которой указаны цены',
                'required'  => true,
                'component' => 'select',
                'values'    => [
                    [
                        'key'  => 'RUB',
                        'text' => 'рубли'
                    ],
                    [
                        'key'  => 'UAH',
                        'text' => 'гривны'
                    ],
                    [
                        'key'  => 'BYN',
                        'text' => 'белорусские рубли'
                    ],
                    [
                        'key'  => 'KZT',
                        'text' => 'тенге'
                    ],
                    [
                        'key'  => 'USD',
                        'text' => 'доллары'
                    ],
                    [
                        'key'  => 'EUR',
                        'text' => 'евро'
                    ]
                ]
            ],
            [
                'key'       => 'categories',
                'title'     => 'Список категорий магазина',
                'comment'   => 'Выбираются на специальной вкладке',
                'required'  => true,
                'component' => 'categories'
            ],
            [
                'key'      => 'delivery-options',
                'title'    => 'Стоимость и сроки курьерской доставки по региону, в котором находится магазин.',
                'help'     => 'Обязательно, если все данные по доставке передаются в прайс-листе.',
                'required' => false
            ],
            [
                'key'      => 'pickup-options',
                'title'    => 'Стоимость и сроки самовывоза по региону, в котором находится магазин.',
                'required' => false
            ],
            [
                'key'       => 'enable_auto_discounts',
                'title'     => 'Автоматический расчет и показ скидок (для всего прайс-листа)',
                'component' => 'checkbox',
                'required'  => false
            ],
            [
                'key'       => 'offers',
                'title'     => 'Список предложений магазина',
                'component' => 'offers',
                'comment'   => 'Настраиваются на специальной вкладке',
                'required'  => true,
            ],
            [
                'key'       => 'gifts',
                'title'     => 'Подарки, которые не размещаются на Маркете (для акции «Подарок при покупке»)',
                'component' => 'feature',
                'disabled'  => true,
                'comment'   => 'Пока неактивно. Обратитесь к webrush@bk.ru'
            ],
            [
                'key'       => 'promos',
                'title'     => 'Информация об акциях магазина',
                'component' => 'feature',
                'disabled'  => true,
                'comment'   => 'Для интеграции обратитесь к webrush@bk.ru'
            ]
        ];
    }

    public static function getOfferFields(): array
    {
        return [
            'offer' => [
                'type'       => 'parent',
                'attributes' => [
                    'id'   => [
                        'title'    => 'Уникальный ID',
                        'required' => true,
                    ],
                    'type' => [
                        'title'    => 'Тип прайс-листа',
                        'required' => true,
                        'values'   => array_map(static function (string $key, string $text) {
                            return compact('key', 'text');
                        }, array_keys(self::OFFER_TYPES), array_values(self::OFFER_TYPES))
                    ],
                    'bid'  => [
                        'title' => 'Ставка в у.е.',
                    ]
                ],
                'fields'     => [
                    'name'  => [
                        'title'    => 'Полное название предложения',
                        'help'     => "Полное название предложения, в которое входит: тип товара, производитель, модель и название товара, важные характеристики. \nСоставляйте по схеме: что (тип товара) + кто (производитель) + товар (модель, название) + важные характеристики. \n\nДанные в name влияют на привязку к карточке товара.",
                        'url'      => 'https://yandex.ru/support/partnermarket/elements/name.html#vendor-name-model',
                        'parent'   => 'offer',
                        'type'     => 'string',
                        'required' => true
                    ],
                    'url'   => [
                        'title'    => 'URL страницы товара на сайте магазина.',
                        'help'     => ' Максимальная длина ссылки — 2048 символов',
                        'required' => true
                    ],
                    'param' => [
                        'title'            => 'Параметр/свойство товара',
                        'multiple'         => true,
                        'allow_attributes' => true
                    ]
                ],
            ]
        ];
    }


}