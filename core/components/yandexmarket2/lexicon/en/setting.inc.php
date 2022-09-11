<?php

$_lang = array_merge($_lang ?? [], [
    'area_yandexmarket2_main'                   => 'Main',
    'setting_yandexmarket2_debug_mode'          => 'Debug mode',
    'setting_yandexmarket2_debug_mode_desc'     => 'Show number of requests and memory consumption in XML',
    'setting_yandexmarket2_strict_sql'          => 'SQL strict mode',
    'setting_yandexmarket2_strict_sql_desc'     => 'If enabled, then all columns of the query participate in grouping, otherwise only id (with a large number of fields, there may be problems in MySQL)',
    'setting_yandexmarket2_reduce_queries'      => 'Reduce SQL queries',
    'setting_yandexmarket2_reduce_queries_desc' => 'If yes, then experimental query methods for products are used',
    'setting_yandexmarket2_prepare_arrays'      => 'Automatically make arrays from multiple values',
    'setting_yandexmarket2_prepare_arrays_desc' => 'For Fenom code processing TV fields (where separator is ||) and ms2 options. Raw arrays will be delimited by ", "',
    'setting_yandexmarket2_files_url'           => 'URL path to files',
    'setting_yandexmarket2_files_url_desc'      => 'Needed only for the correct link to the finished file in the interface. If you change _files_path, do not forget to fix it here. Variables {site_url}, {assets_url} are available. By default {site_url}/{assets_url}/yandexmarket/',
    'setting_yandexmarket2_files_path'          => 'Path for generated files',
    'setting_yandexmarket2_files_path_desc'     => 'You can specify from the server root, or use the {core_path}, {base_path}, {assets_path} variable set (standard MODX paths). By default "{assets_path}yandexmarket/"',
    'setting_yandexmarket2_images_url'          => 'URL address for images',
    'setting_yandexmarket2_images_url_desc'     => 'If the images are from a subdomain or the paths are somehow different, fill in this setting. The component automatically adds site_url to images from the database',
    'setting_yandexmarket2_site_url'            => 'Website URL',
    'setting_yandexmarket2_site_url_desc'       => 'If you use several domains or there are differences in the cron-work of the component, then fill it in (along with the protocol). Links to products and images will be relative to this address',
]);
