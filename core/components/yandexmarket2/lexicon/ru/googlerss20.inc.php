<?php

$lexicon = [
    'channel'        => 'Фид данных',
    'title'          => 'Название фида данных',
    'link'           => 'Ссылка',
    'description'    => 'Описание',
    'items'          => 'Вспомогательный элемент для элемента <item>',
    'item'           => 'Товар',
    'g:id'           => 'Идентификатор товара',
    'g:title'        => 'Название товара',
    'g:description'  => 'Описание товара',
    'g:link'         => 'Ссылка на товар',
    'g:image_link'   => 'Ссылка на изображение товара',
    'g:price'        => 'Цена товара',
    'g:condition'    => 'Состояние товара',
    'g:availability' => 'Наличие товара',
    'g:brand'        => 'Бренд товара',
];

$_lang = array_merge($_lang ?? [], array_combine(array_map(static function (string $key) {
    return 'ym2_google.rss20_'.$key;
}, array_keys($lexicon)), $lexicon));