<?php

$lexicon = [
    'name'                       => 'Short store name',
    'name_help'                  => 'You cannot use words in the name that are not related to the name of the store (for example, "best", "cheap"), indicate a phone number, etc.\n\nThe name of the store must match the actual name that is published on the site. If the requirement is not met, Yandex.Market may independently change the name without notifying the store.',
    'company'                    => 'Company full name',
    'company_help'               => 'The full name of the company that owns the store. Not published.',
    'url'                        => 'URL of the main store page',
    'url_help'                   => 'The maximum length of a link is 2048 characters. Cyrillic links are allowed. The URL is generated based on RFC 3986',
    'platform'                   => 'Shop CMS',
    'version'                    => 'CMS version',
    'agency'                     => 'Agency providing technical support',
    'email'                      => 'Contact address of developers',
    'categories'                 => 'List of store categories',
    'categories_help'            => "Categories from the price list are used to distribute products into categories on Yandex.Market. This takes into account not only the end category of the offer, but also all parent categories",
    'category'                   => 'Category element with attributes',
    'category_help'              => 'The final category the product belongs to must match the product\n
The list of categories should reflect the names of real product categories, and not contain «stubs» that are understandable only to the store itself.\n
If your store does not have its own list of categories or you are in doubt about how to specify a category, you can use the Names from the Market category tree https://download.cdn.yandex.net/market/market_categories.xls',
    'delivery-options'           => 'Courier delivery costs and terms for the region where the store is located.',
    'delivery-options_help'      => 'Required if all shipping data is sent in the price list.',
    'pickup-options'             => 'Prices and pickup times by region where the store is located.',
    'enable_auto_discounts'      => 'Automatic calculation and display of discounts',
    'enable_auto_discounts_help' => 'For the entire price list',
    'offers'                     => 'List of store offers',
    'offers_help'                => "Each offer is described in a separate offer element. The list of elements included in the offer depends on the offer type. For most product categories, the following types are suitable:\n
Simplified type\nArbitrary type",
    'offer'                      => 'Shop offer (product)',
    'offer_help'                 => 'The offer element can be of a simplified type (the full name of the product - type, manufacturer and model - is specified in one element) or of an arbitrary type (the name of the product is made up of three different elements). ',
    'gifts'                      => 'Gifts that are not placed on the Market (for the «Gift with purchase» promotion)',
    'promos'                     => 'Information about store promotions',
    'shop'                       => 'All about the shop',
    'shop_help'                  => "The shop element contains:\n
Elements with information about the store: name, list of categories, shipping terms, etc.\n
The offers element with a list of store offers.\n
The promos element with a list of store promotions and the gifts element with a list of gifts (which are not placed on the Market as separate products) for the «Gift with Purchase» promotions. In the promos element, you need to insert one promo element for each promotion, and in the gifts element, one gift element for each gift. .",
    'yml_catalog'                => 'XML root element',
    'yml_catalog_help'           => "The YML format uses the <yml_catalog> element with the date attribute as its root.\n
In the date attribute, the date and time of the moment at which the data in the file is relevant.",

    'currencies'           => 'List of store currencies (primary first)',
    'currencies_help'      => 'The first one in the list will be the primary currency in which prices are quoted. You can click on the gray one to make it the main one',
    'currencies_value_RUB' => 'rubles',
    'currencies_value_UAH' => 'hryvnia',
    'currencies_value_BYN' => 'Belarusian rubles',
    'currencies_value_KZT' => 'tenge',
    'currencies_value_USD' => 'dollars',
    'currencies_value_EUR' => 'euro',

    'param_attr_name' => 'Parameter name',
    'param_attr_unit' => 'Unit (for numeric)',

    'offer_attr_id'        => 'offer ID',
    'offer_attr_type'      => 'Offer type',
    'offer_attr_available' => 'Definite delivery date',
    'offer_attr_group_id'  => 'offer group ID',
    'offer_attr_bid'       => 'Bid in USD',

    'offer_attr_type_value_'             => 'Simplified type',
    'offer_attr_type_value_vendor.model' => 'Arbitrary type',
    'offer_attr_type_value_alco'         => 'Alcohol',
    'offer_attr_type_value_audiobook'    => 'Audiobooks',
    'offer_attr_type_value_event-ticket' => 'Event tickets',
    'offer_attr_type_value_book'         => 'Books',
    'offer_attr_type_value_medicine'     => 'Medicines',
    'offer_attr_type_value_artist.title' => 'Music and video production',
    'offer_attr_type_value_tour'         => 'Tours',

    'offer_name'       => 'Full name of the offer',
    'offer_name_help'  => "The full name of the offer, which includes: product type, manufacturer, model and product name, important characteristics. \nCompose according to the scheme: what (product type) + who (manufacturer) + product (model, name) + important characteristics.\n\nThe data in the name affects the binding to the product card.",
    'offer_url'        => 'URL of the product page on the shop website.',
    'offer_url_help'   => 'The maximum link length is 2048 characters',
    'offer_param'      => 'Important characteristics of the product',
    'offer_param_help' => "Color, size, volume, material, weight, age, gender, etc. The element must not be empty.\n\nIn YML, an offer element can contain multiple param elements (one param element equals one characteristic ).",

    'offer_model'                      => 'Product model and name',
    'offer_vendor'                     => 'Vendor',
    'offer_typePrefix'                 => 'Product type / category',
    'offer_typePrefix_help'            => "For example, 'mobile phone', 'washing machine', 'corner sofa'.\n\nNote: The vendor, model, typePrefix elements form the name of the offer on the Market and affect the link to the product card.",
    'offer_vendorCode'                 => 'Product code assigned by the manufacturer',
    'offer_price'                      => 'Actual product price',
    'offer_price_help'                 => "Format: integer or fractional number.\nThe separator between integer and fractional part is a dot.\nThe price must correspond to the cost of the product, otherwise the store will receive an error during the check.\n\nIn some categories (if the price list is sent in the format YML), it is permissible to specify the initial price «from» - using the attribute from=\"true\".",
    'offer_oldprice'                   => 'Old product price',
    'offer_oldprice_help'              => 'Must be higher than current price. The market automatically calculates the difference and shows users the discount.',
    'offer_purchase_price'             => 'Product purchase price',
    'offer_purchase_price_help'        => 'This is needed to calculate the markup and set up a margin strategy in PriceLabs.',
    'offer_enable_auto_discount'       => 'Automatic calculation and display of discounts',
    'offer_currencyId'                 => 'Product currency',
    'offer_currencyId_help'            => 'Currency in which the price of the product is indicated: RUR, USD, EUR, UAH, KZT, BYN.',
    'offer_categoryId'                 => 'Product category ID',
    'offer_picture'                    => 'Product image URL',
    'offer_supplier'                   => 'OGRN or OGRNIP of a third-party seller',
    'offer_supplier_help'              => "OGRN must be 13 characters, OGRN is 15 characters.\nOnly used with the ogrn attribute.",
    'offer_delivery'                   => 'Possibility of courier delivery',
    'offer_delivery_help'              => "(for all regions the store delivers to).\n\nPossible values:\ntrue — courier delivery available;\nfalse — no courier delivery.",
    'offer_pickup'                     => 'Possibility of pickup from pickup points',
    'offer_pickup_help'                => "(in all regions the store delivers to).\n\nPossible values:\ntrue — pickup available;\nfalse — no pickup.",
    'offer_delivery-options'           => 'Terms of courier delivery',
    'offer_delivery-options_help'      => "by region of the store (types of delivery, terms, cost)\n\nMore details https://yandex.ru/support/partnermarket/elements/delivery-options.html\n\nAlso delivery terms in your own way region can be configured in your account.",
    'offer_pickup-options'             => 'Pickup terms by store region',
    'offer_pickup-options_help'        => "By store region (terms, cost).m\nLearn more https://yandex.ru/support/partnermarket/elements/pickup-options.html\n\nYou can also set up pickup conditions for your region in your account.",
    'offer_store'                      => 'Possibility to buy without prior order',
    'offer_store_help'                 => "Possible values:\ntrue - item can be bought without pre-order.\nfalse - item cannot be bought without pre-order.",
    'offer_description'                => 'Offer Description',
    'offer_description_help'           => "It is displayed:\n
on the Prices page of the product card - in full;\n
in the Market search results - in an abbreviated form (description no longer than 300 characters).\n
Text length is limited to 3000 characters (including punctuation).\n\n
Some xhtml tags are allowed in YML format, provided they are enclosed in a CDATA block and the general rules of the XHTML standard are followed.",
    'offer_sales_notes'                => 'Terms of sale of goods',
    'offer_sales_notes_help'           => "This item is required if you have purchase restrictions (such as a minimum number of items or a prepayment requirement).\n
You can also specify payment options, promotions and sales. In this case, the element is optional.\n
Do not include information in the element that forces the buyer to leave the Market. For example, «Discount in the VK group - 2%».\n
The allowed text length is 50 characters.",
    'offer_min-quantity'               => 'Minimum order quantity',
    'offer_min-quantity_help'          => "Minimum number of identical items in an order (for cases where the purchase is only possible as a set, not individually).\n
The element is used only in the Tires, Truck Tires, Motorcycle Tires, Rims categories.\n
If the element is not specified, the default value is 1.",
    'offer_manufacturer_warranty'      => 'Offer manufacturer warranty',
    'offer_manufacturer_warranty_help' => "Possible values:\n
true — the product has an official manufacturer's warranty;\n
false — the product does not have an official manufacturer's warranty.",
    'offer_country_of_origin'          => 'Country of origin',
    'offer_adult'                      => 'Product for adults',
    'offer_adult_help'                 => 'The product is related to the satisfaction of sexual needs, or otherwise exploits the interest in sex. Possible values are true, false.',
    'offer_barcode'                    => 'Product barcode',
    'offer_barcode_help'               => "Product barcode from the manufacturer in one of the following formats: EAN-13, EAN-8, UPC-A, UPC-E.\n
In YML, an offer element can contain multiple barcode elements.\n
The data in the barcode affects the binding of the offer to the product card and the display of the correct characteristics that correspond to the modification of the product on its card",
    'offer_condition'                  => 'Used or discounted item',
    'offer_condition_help'             => "Use the element for second-hand goods and goods discounted due to defects. Specify the condition of the goods in the type attribute:\n
likenew - like new (the product was not in use, discounted due to flaws);\n
used — used (the item was used).\n
In the reason element, be sure to include the reason for the markdown and describe the shortcomings in detail. The length of the text is no more than 3000 characters (including punctuation).",
    'offer_credit-template'            => 'Loan program ID',
    'offer_credit-template_help'       => 'If the product is covered by a separate credit program, get its ID in your account and specify it in the id attribute',
    'offer_expiry'                     => 'Expiration date or expiry',
    'offer_expiry_help'                => 'Item value must conform to the ISO 8601 standard. For example: P1Y2M10DT2H - 1 year, 2 months, 10 days, 2 hours.',
    'offer_weight'                     => 'Product weight in kilograms',
    'offer_weight_help'                => "(including packaging)\nSome categories have restrictions on the minimum or maximum weight value.\nIn any category, the weight can be specified to the nearest thousandths (for example, 1.001 kg; the separator between integer and fractional part is a dot). ",
    'offer_dimensions'                 => 'Product dimensions',
    'offer_dimensions_help'            => "Product dimensions (length, width, height) in the package. Specify the dimensions in centimeters.\n
Format: three positive numbers with a precision of 0.001, the separator of the integer and fractional parts is a dot. Numbers must be separated by «/» without spaces.",
    'offer_downloadable'               => 'Product can be downloaded',
    'offer_downloadable_help'          => 'If set to true, the offer will be shown in all regions, but no payment methods will be shown.',
    'offer_age'                        => 'Product age category',
    'offer_age_help'                   => "
Years are specified using the unit attribute with the value year. Valid values for the age parameter with unit=\"year\": 0, 6, 12, 16, 18.
\n
Months are specified using the unit attribute with the value month. Valid values for the age parameter with unit=\"month\": 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12.",
];

$_lang = array_merge($_lang ?? [], array_combine(array_map(static function (string $key) {
    return 'ym2_yandex.market_'.$key;
}, array_keys($lexicon)), $lexicon));