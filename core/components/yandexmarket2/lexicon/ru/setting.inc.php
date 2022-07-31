<?php

$_lang = array_merge($_lang ?? [], [
    'area_yandexmarket2_main'                   => 'Основные',
    'setting_yandexmarket2_debug_mode'          => 'Режим отладки',
    'setting_yandexmarket2_debug_mode_desc'     => 'Покажет количество запросов и потребление памяти в XML',
    'setting_yandexmarket2_strict_sql'          => 'Строгий режим SQL',
    'setting_yandexmarket2_strict_sql_desc'     => 'Если включён, то в группировке участвуют все столбцы запроса, иначе только id (при большом количестве полей могут быть проблемы в MySQL)',
    'setting_yandexmarket2_reduce_queries'      => 'Меньше SQL запросов',
    'setting_yandexmarket2_reduce_queries_desc' => 'Если да, то используются экспериментальные способы запросов для товаров',
    'setting_yandexmarket2_prepare_arrays'      => 'Автоматически делать массивы из множественных значений',
    'setting_yandexmarket2_prepare_arrays_desc' => 'Для Fenom обработки ТВ-полей (где разделитель ||) и опций ms2. Необработанные массивы будут через разделитель ", "',
    'setting_yandexmarket2_files_url'           => 'URL путь до файлов',
    'setting_yandexmarket2_files_url_desc'      => 'Нужен только для корректной ссылки на готовый файл в интерфейсе. Если меняете _files_path, не забудьте поправить тут. Доступны переменные {site_url}, {assets_url}. По умолчанию {site_url}/{assets_url}/yandexmarket/',
    'setting_yandexmarket2_files_path'          => 'Путь для сгенерированных файлов',
    'setting_yandexmarket2_files_path_desc'     => 'Можете указать от корня сервера, или использую набор переменных {core_path}, {base_path}, {assets_path} (стандартные MODX пути). По умолчанию "{assets_path}yandexmarket/"',
    'setting_yandexmarket2_images_url'          => 'URL адрес для изображений',
    'setting_yandexmarket2_images_url_desc'     => 'Если изображения с поддомена или как-то отличаются пути, заполните эту настройку. К изображениям из базы компонент автоматически добавляет site_url',
    'setting_yandexmarket2_site_url'            => 'URL адрес сайта',
    'setting_yandexmarket2_site_url_desc'       => 'Если используете несколько доменов или есть отличия при cron-работе компонента, то заполните (вместе с протоколом). Ссылки на товары и изображения будут относительно этого адреса',
]);
