<?php

$_lang = array_merge($_lang ?? [], [
    'yandexmarket2'           => 'YandexMarket2',
    'yandexmarket2_menu_desc' => 'Экспорт предложений в XML',

    'ym2_pricelist'                 => 'Прайс-лист',
    'ym2_field'                     => 'Элемент',
    'ym2_attribute'                 => 'Атрибут',
    'ym2_category'                  => 'Категория',
    'ym2_condition'                 => 'Условие',
    'ym2_pricelist_err_nf'          => 'Прайс-лист не найден',
    'ym2_attribute_err_nf'          => 'Атрибут не найден',
    'ym2_category_err_nf'           => 'Категория не найдена',
    'ym2_condition_err_nf'          => 'Условие не найдено',
    'ym2_field_err_nf'              => 'Элемент не найден',
    'ym2_category_err_ae'           => 'Категория уже существует',
    'ym2_condition_column_err_ns'   => 'Нужно указать столбец',
    'ym2_condition_operator_err_ns' => 'Нужно выбрать оператор',
    'ym2_pricelist_name_err_ns'     => 'Нужно ввести название',
    'ym2_pricelist_type_err_ns'     => 'Нужно выбрать тип прайс-листа',
    'ym2_pricelist_file_err_ae'     => 'Прайс-лист с таким файлом уже существует',
    'ym2_field_err_valid'           => 'Невалидные значения элемента',
    'ym2_attribute_err_valid'       => 'Невалидное значение атрибута',
    'ym2_pricelist_err_method'      => 'Неизвестный метод',

    'ym2_field_type_0'  => 'текст (без обработки)',
    'ym2_field_type_1'  => 'корневой элемент',
    'ym2_field_type_2'  => 'элемент магазина',
    'ym2_field_type_3'  => 'список валют',
    'ym2_field_type_4'  => 'список категорий',
    'ym2_field_type_5'  => 'список предложений',
    'ym2_field_type_6'  => 'элемент предложения',
    'ym2_field_type_7'  => 'элемент категории',
    'ym2_field_type_10' => 'родительский элемент',
    'ym2_field_type_11' => 'значение из столбца',
    'ym2_field_type_12' => 'столбец в CDATA (с html тегами)',
    'ym2_field_type_13' => 'изображения товара',
    'ym2_field_type_14' => 'обёртка для категорий',
    'ym2_field_type_15' => 'обёртка для товаров',
    'ym2_field_type_19' => 'сырой XML (без обработки)',
    'ym2_field_type_20' => 'только атрибуты (пустой элемент)',

    'ym2_msoptionsprice_options'     => 'Массив опций со значениями',
    'ym2_fields_possible_elements'   => 'Возможные элементы',
    'ym2_fields_type_any_name'       => 'Вы также можете ввести любое название',
    'ym2_resource_fields'            => 'Поля ресурса',
    'ym2_resource_additional_fields' => 'Вспомогательные поля предложений',
    'ym2_ms2gallery_header'          => 'Изображения ресурса ms2Gallery',
    'ym2_minishop2_fields'           => 'Поля товара miniShop2',
    'ym2_minishop2_gallery'          => 'Галерея miniShop2',
    'ym2_minishop2_images'           => 'Изображения товара miniShop2',
    'ym2_minishop2_options'          => 'Опции miniShop2',
    'ym2_minishop2_vendor'           => 'Производитель miniShop2',
    'ym2_tv_fields'                  => 'Дополнительные поля (TV)',
    'ym2_mscategory_id'              => 'Мультикатегории товара ms2 (для условий)',
    'ym2_category_fields'            => 'Поля родительской категории (стандартные)',
    'ym2_category_pagetitle'         => 'Заголовок родительской категории',
    'ym2_category_name'              => 'Формат под любое другое поле родителя',
    'ym2_category_tv'                => 'Дополнительные поля категории (TV)',
    'ym2_category_tv_name'           => 'Формат для ТВ-полей категории',
    'ym2_msop2_modification_fields'  => 'Поля модификации msOptionsPrice2',

    'ym2_offer_field_url'   => 'Полная ссылка на товар',
    'ym2_offer_field_price' => 'Цена с учётом плагинов ms2 и модификаций msOp2',
    'ym2_offer_field_image' => 'Изображение товара с полной ссылкой (из поля image)',

    'ym2_xml_file_existed' => 'Файл %s уже существует и будет перезаписан',
    'ym2_xml_file_writing' => 'Запущен процесс записи в файл',
    'ym2_xml_file_written' => 'Файл %s успешно записан',

    'ym2_debug_mode_enabled'             => 'Включён режим отладки. Лог будет более подробный',
    'ym2_debug_categories_written'       => 'Записано категорий: %d',
    'ym2_debug_offers_written'           => 'Записано товаров: %d',
    'ym2_debug_undefined_type'           => 'Неизвестный тип "%s" для поля "%s" (ID: %d)',
    'ym2_debug_empty_value'              => 'Пустое значение для обязательного элемента %s',
    'ym2_debug_empty_raw'                => 'Пустой сырой XML, пропущен элемент %s',
    'ym2_debug_undefined_attribute_type' => 'Неизвестный тип "%s" для атрибута "%s" (ID: %d)',
    'ym2_debug_empty_categories'         => 'Пустой список категорий в поле "%s" (ID: %d)',
    'ym2_debug_empty_offers'             => 'Пустой список товаров в поле "%s" (ID: %d)',

    'ym2_debug_offers_not_found'            => ' Не найдено подходящих предложений ',
    'ym2_debug_suitable_offers'             => ' Подходящих предложений: ',
    'ym2_debug_suitable_categories'         => ' Подходящих категорий: ',
    'ym2_debug_possible_categories_plugins' => ' Возможно используются условия для категорий из плагинов ',
    'ym2_debug_possible_offers_plugins'     => ' Возможно используются условия для предложений из плагинов ',
    'ym2_debug_element_not_found'           => ' Не найден элемент ',

    'ym2_debug_pdotools_not_found'        => 'Не найден pdoTools. Код будет обработан парсером MODX',
    'ym2_debug_add_offers_condition'      => 'Добавлено условие id IN (offers ids) для категорий',
    'ym2_debug_joined_table_with_columns' => 'Приджойнена таблица `%s` со столбцами "%s" к %s',
    'ym2_debug_ms2gallery_error'          => 'Не удалось загрузить ms2Gallery. Проверьте настройки полей.',
    'ym2_debug_msop2_modification'        => 'Для модификаций msOptionsPrice2 в прайс-листе в настройке предложений добавьте к классу ресурса ":msop2", пример "msProduct:msop2"',
    'ym2_debug_add_categories_condition'  => 'Добавлено условие %s IN (select id from parentsQuery) для товаров',
    'ym2_debug_unknown_class'             => 'Неизвестный класс %s. Загрузите модель в своём плагине на событие ym2OnBeforeOffersQuery или обратитесь в поддержку.',
    'ym2_debug_unknown_operator'          => 'Неизвестный оператор для условия',
    'ym2_debug_query_add_categories'      => 'К запросу категорий добавлены недостающие категории: %s',

    'ym2_marketplace_yandex.market' => 'Яндекс Маркет',
    'ym2_marketplace_google.rss20'  => 'Google RSS 2.0 (Merchant center, FB)',
]);
