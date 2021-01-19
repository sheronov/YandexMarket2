<?php

namespace YandexMarket\Marketplaces;

class YandexMarket extends Marketplace
{
    public static function getFields(): array
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
                'title'    => 'Полное наименование компании (не публикуется)',
                'required' => true
            ],
            [
                'key'      => 'url',
                'title'    => 'URL главной страницы магазина',
                'help'     => 'Максимальная длина ссылки — 2048 символов. Допускаются кириллические ссылки. URL‑адрес формируется на основе стандарта RFC 3986',
                'required' => true
            ],
            [
                'key'       => 'currencies',
                'title'     => 'Список курсов валют магазина (первая - валюта магазина)',
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
                'key'       => 'enable_auto_discounts',
                'title'     => 'Автоматический расчет и показ скидок (для всего прайс-листа)',
                'component' => 'checkbox',
                'required'  => false
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
            ]
        ];
    }
}