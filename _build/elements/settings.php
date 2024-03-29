<?php

return [
    'debug_mode'     => [
        'xtype' => 'combo-boolean',
        'value' => false,
        'area'  => 'yandexmarket2_main',
    ],
    'strict_sql'     => [
        'xtype' => 'combo-boolean',
        'value' => false,
        'area'  => 'yandexmarket2_main',
    ],
    'reduce_queries' => [
        'xtype' => 'combo-boolean',
        'value' => false,
        'area'  => 'yandexmarket2_main'
    ],
    'prepare_arrays' => [
        'xtype' => 'combo-boolean',
        'value' => false,
        'area'  => 'yandexmarket2_main'
    ],
    'images_url'     => [
        'xtype' => 'textfield',
        'value' => '',
        'area'  => 'yandexmarket2_main'
    ],
    'site_url'       => [
        'xtype' => 'textfield',
        'value' => '',
        'area'  => 'yandexmarket2_main'
    ],
    'files_path'     => [
        'xtype' => 'textfield',
        'value' => '{assets_path}yandexmarket/',
        'area'  => 'yandexmarket2_main'
    ],
    'files_url'      => [
        'xtype' => 'textfield',
        'value' => '{site_url}/{assets_url}/yandexmarket/',
        'area'  => 'yandexmarket2_main'
    ]
];
