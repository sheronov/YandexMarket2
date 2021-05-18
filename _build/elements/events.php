<?php

return [
    'ym2OnBeforeQuery', //для загрузки моделей сторонних компонентов
    'ym2OnBeforeWritingOffer', //для какой-нибудь предобработки и проставления плейсхолдеров в _pls
    'ym2OnBeforeWritingCategory', //для какой-нибудь предобработки и проставления плейсхолдеров в _pls
    'ym2OnOffersQuery', //чтоб добавить условия на товары или что-то ещё в свой плагин
    'ym2OnCategoriesQuery', //чтоб добавить условия на категории или что-то ещё в свой плагин
    'ym2OnBeforePricelistGenerate', // TODO: это событие заинвокать
    'ym2OnAfterPricelistCreate', // TODO: это событие заинвокать
];