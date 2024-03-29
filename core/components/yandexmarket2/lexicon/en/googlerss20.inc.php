<?php

$lexicon = [
    'channel'                      => 'Data feed',
    'channel_help'                 => 'More about requirements https://support.google.com/merchants/answer/7052112?hl=ru',
    'title'                        => 'Data feed name',
    'link'                         => 'Link',
    'description'                  => 'Description',
    'items'                        => 'Helper for element <item>',
    'item'                         => 'Item',
    'g:id'                         => 'Item ID',
    'g:id_help'                    => 'Required. The unique identifier for your item, may contain letters. No more than 50 characters',
    'g:title'                      => 'Item name',
    'g:title_help'                 => 'Required. The product name must match what is listed on the landing page. No more than 150 characters',
    'g:description'                => 'Item description',
    'g:description_help'           => 'Required. The description of the product in the feed must match the one on the landing page. No more than 5000 characters',
    'g:link'                       => 'Link to the item',
    'g:link_help'                  => 'Required. The link must contain a verified domain name. The URL must be prefixed with http or https',
    'g:image_link'                 => 'Item image link',
    'g:image_link_help'            => 'Required. The file must be in JPEG, WebP, PNG, GIF (no animation), BMP, or TIFF format. URL must be with domain',
    'g:additional_image_link'      => 'Link to additional item image',
    'g:additional_image_link_help' => 'Up to 10 additional images. Link to each in a separate attribute',
    'g:mobile_link'                => 'Mobile item link',
    'g:availability'               => 'Item availability',
    'g:availability_help'          => 'Valid values are: (for FB, replace _ with spaces) in_stock, out_of_stock, preorder, backorder',
    'g:availability_date'          => 'Date the item was in stock',
    'g:availability_date_help'     => 'Required (if the item is set to preorder[preorder] or backorder[sub_order]). Example: 2016-02-24T11:07+0100',
    'g:price'                      => 'Product price with currency',
    'g:price_help'                 => 'Mandatory, the price must be specified in the currency of the country of sale according to the ISO 4217 standard. Example: 1500.00 RUB',
    'g:sale_price'                 => 'Product price including discount',
    'g:sale_price_help'            => 'Example: 1500.00 RUB',
    'g:condition'                  => 'Product condition',
    'g:brand'                      => 'Product brand',
    'g:brand_help'                 => 'Required (for all new products, except movies, books, and music). No more than 70 characters',
    'g:gtin'                       => 'International marking code',
    'g:gtin_help'                  => 'Mandatory (for all new products that have a GTIN). No more than 50 characters',
    'g:mpn'                        => 'Product manufacturer code',
    'g:mpn_help'                   => 'Mandatory (for new products that do not have a GTIN code). You can only specify MPN codes assigned by the manufacturer',
    'g:identifier_exists'          => 'Has an ID',
    'g:identifier_exists_help'     => 'You can specify if the product has a unique identifier, such as GTIN, MPN, or brand. Values: yes or no',
    'g:adult'                      => 'Product for adults',
    'g:adult_help'                 => 'Required (for products that contain adult-only content). Values: yes or no',
    'g:age_group'                  => 'Age group',
    'g:age_group_help'             => 'Valid values: newborn, infant, toddler, kids, adult',
    'g:color'                      => 'Product color',
    'g:color_help'                 => 'No more than 100 characters in total. For each of the colors - no more than 40 characters, separator "/". Example: "red/black"',
    'g:gender'                     => 'Gender of the people for whom the product is intended',
    'g:gender_help'                => 'Valid values: male, female, unisex',
    'g:material'                   => 'The material from which the product is made',
    'g:material_help'              => 'Mandatory (for products that have variants that differ in material). No more than 200 characters',
    'g:pattern'                    => 'Pattern or design on the product',
    'g:pattern_help'               => 'Mandatory (for products that have variants that differ in pattern). No more than 100 characters',
    'g:size'                       => 'Item size',
    'g:size_help'                  => 'Mandatory for products that have different size options. No more than 100 characters',

    'g:google_product_category'        => 'Product category according to Google classification',
    'g:google_product_category_help'   => "Optional. Specify the ID or path to the category. Example: 2271 or \"Apparel & Accessories > Clothing > Dresses\".\nGoogle Categories: https://www.google.com/basepages/producttype/taxonomy-with-ids.ru-RU.xls",
    'g:product_type'                   => 'Product category by seller classification',
    'g:product_type_help'              => 'Optional. The category must be specified in full, for example "Home > Women Clothing > Dresses > Long Dresses", and not just "Dresses". Max 750 characters',
    'g:item_group_id'                  => 'Common identifier for variants of one product',
    'g:item_group_id_help'             => 'Required to display free extended information about product variants. No more than 50 characters',
    'g:sale_price_effective_date'      => 'Date range to apply sale_price',
    'g:sale_price_effective_date_help' => 'This attribute must be specified together with the sale_price attribute. Example: "2016-02-24T11:07+0100/2016-02-29T23:07+0100"',
];

$_lang = array_merge($_lang ?? [], array_combine(array_map(static function (string $key) {
    return 'ym2_google.rss20_'.$key;
}, array_keys($lexicon)), $lexicon));
