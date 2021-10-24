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
]);
