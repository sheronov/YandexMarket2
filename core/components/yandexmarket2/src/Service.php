<?php

namespace YandexMarket;

use Exception;
use modTemplateVar;
use modX;
use msOption;
use xPDO;
use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Models\Field;

class Service
{
    protected $modx;
    protected $config = [];

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');

        $this->config = array_merge($config, [
            'corePath'  => $corePath,
            'modelPath' => $corePath.'model/',
        ]);

        $this->modx->addPackage('yandexmarket2', $corePath.'model/');
        $this->modx->lexicon->load('yandexmarket2:default');
    }

    public static function debugInfo(xPDO $xpdo): ?array
    {
        if (!$xpdo->getOption('yandexmarket_debug_mode')) {
            return null;
        }
        return [
            'queries'   => $xpdo->executedQueries,
            'queryTime' => sprintf("%2.4f s", $xpdo->queryTime),
            'totalTime' => sprintf("%2.4f s", (microtime(true) - $xpdo->startTime)),
            'memory'    => number_format(memory_get_usage(true) / 1024, 0, ",", " ").' kb'
        ];
    }

    public static function hasMiniShop2(): bool
    {
        return file_exists(MODX_CORE_PATH.'components/minishop2/model/minishop2/msproduct.class.php');
    }

    public static function hasMs2Gallery(): bool
    {
        return file_exists(MODX_CORE_PATH.'components/ms2gallery/model/ms2gallery/ms2gallery.class.php');
    }

    protected function getLexicon(string $key, ?string $fallbackKey = null): ?string
    {
        if (($key !== $lexicon = $this->modx->lexicon($key))) {
            return $lexicon;
        }

        if ($fallbackKey && $fallbackKey !== $key && $fallbackKey !== $lexicon = $this->modx->lexicon($fallbackKey)) {
            return $lexicon;
        }

        return null;
    }

    public function getAvailableFields(): array
    {
        $fields = [];

        foreach (Field::TYPES_DATA as $field => $data) {
            $fields[] = array_merge($data, [
                'value' => $field,
                'text'  => $this->getLexicon('ym_field_type_'.$field) ?: $field,
            ]);
        }

        return $fields;
    }

    /**
     * @throws Exception
     */
    public function getMarketplaces(): array
    {
        return array_map(function (array $marketplace) {
            $type = $marketplace['key'];
            $marketplace['value'] = $type;
            $marketplace['text'] = $this->getLexicon('ym_marketplace_'.$type) ?: $type;
            unset($marketplace['class'], $marketplace['key']);

            $marketplace['shop_fields'] = $this->prepareFields($marketplace['shop_fields'], $type, 'shop');
            $marketplace['offer_fields'] = $this->prepareFields($marketplace['offer_fields'], $type, 'offer');

            return $marketplace;
        }, array_values(Marketplace::listMarketplaces()));
    }

    protected function prepareFields(array $keyFields, string $marketplace, string $parent = ''): array
    {
        $fields = [
            ['header' => 'Возможные элементы']
        ];

        foreach ($keyFields as $key => $data) {
            $fields[] = array_merge($data, [
                'value' => $key,
                'text'  => $this->getLexicon('ym_'.$marketplace.'_'.$parent.'_'.$key,
                    'ym_'.$marketplace.'_'.$key) ?: $key,
                'type'  => $data['type'] ?? Field::TYPE_DEFAULT,
            ]);
        }
        $fields[] = ['divider' => true];
        $fields[] = ['header' => 'Вы также можете ввести любое название'];

        return $fields;
    }

    public function listClassKeys(bool $withHeaders = false, bool $withDividers = false): array
    {
        $list = [];
        if ($resourceFields = $this->getModResourceFields()) {
            $list = array_merge($list,
                $withHeaders ? [['header' => 'Поля ресурса']] : [],
                $resourceFields,
                $withDividers ? [['divider' => true]] : []
            );
        }

        $list = array_merge($list,
            $withHeaders ? [['header' => 'Вспомогательные поля компонента']] : [],
            $this->getOfferFields(),
            $withDividers ? [['divider' => true]] : []
        );

        if (self::hasMiniShop2()) {
            if ($productFields = $this->getMsProductFields()) {
                $list = array_merge($list,
                    $withHeaders ? [['header' => 'Поля товара miniShop2']] : [],
                    $productFields,
                    $withDividers ? [['divider' => true]] : []
                );
            }

            if ($galleryFields = $this->getMsGalleryFields()) {
                $list = array_merge($list,
                    $withHeaders ? [['header' => 'Галерея товара miniShop2']] : [],
                    $galleryFields,
                    $withDividers ? [['divider' => true]] : []
                );
            }

            if ($optionFields = $this->getMsOptionFields()) {
                $list = array_merge($list,
                    $withHeaders ? [['header' => 'Опции miniShop2']] : [],
                    $optionFields,
                    $withDividers ? [['divider' => true]] : []
                );
            }

            if ($vendorFields = $this->getMsVendorFields()) {
                $list = array_merge($list,
                    $withHeaders ? [['header' => 'Производитель miniShop2']] : [],
                    $vendorFields,
                    $withDividers ? [['divider' => true]] : []
                );
            }
        }

        if (self::hasMs2Gallery() && $ms2GalleryFields = $this->getMs2GalleryFields()) {
            $list = array_merge($list,
                $withHeaders ? [['header' => 'Изображения ms2Gallery']] : [],
                $ms2GalleryFields,
                $withDividers ? [['divider' => true]] : []
            );
        }

        if ($tvFields = $this->getTvFields()) {
            $list = array_merge($list,
                $withHeaders ? [['header' => 'Дополнительные поля (TV)']] : [],
                $tvFields,
                $withDividers ? [['divider' => true]] : []
            );
        }

        if ($withDividers) {
            array_pop($list); //remove last divider
        }

        return $list;
    }

    protected function getModResourceFields(
        string $columnPrefix = '',
        array $skip = [
            'type',
            'contentType',
            'alias_visible',
            'link_attributes',
            'pub_date',
            'unpub_date',
            'isfolder',
            'richtext',
            'menuindex',
            'searchable',
            'cacheable',
            'createdby',
            'createdon',
            'editedby',
            'editedon',
            'deleted',
            'deletedon',
            'deletedby',
            'publishedon',
            'publishedby',
            'donthit',
            'privateweb',
            'privatemgr',
            'content_dispo',
            'hidemenu',
            'class_key',
            'hide_children_in_tree',
            'show_in_tree',
            'properties',
            'context_key',
            'content_type',
            'uri_override'
        ]
    ): array {
        $fields = $this->modx->getFields('modResource');

        $this->modx->lexicon->load('resource');

        return array_map(function (string $key) use ($columnPrefix, $skip) {
            return [
                'value'   => $columnPrefix.$key,
                'text'    => $this->getLexicon('resource_'.$key, $key) ?: $key,
                'skipped' => in_array($key, $skip, true)
            ];
        }, array_keys($fields));
    }

    protected function getOfferFields(string $columnPrefix = 'Offer.'): array
    {
        return [
            ['value' => $columnPrefix.'url', 'text' => 'Полная ссылка на товар'],
            ['value' => $columnPrefix.'price', 'text' => 'Цена с учётом плагинов miniShop2']
        ];
    }

    protected function getMsProductFields(
        string $columnPrefix = 'Data.',
        array $skip = ['id', 'source', 'color', 'size', 'tags']
    ): array {
        $fields = $this->modx->getFields('msProductData');

        $this->modx->lexicon->load('minishop2:product');

        return array_map(function (string $key) use ($columnPrefix, $skip) {
            return [
                'value'   => $columnPrefix.$key,
                'text'    => $this->getLexicon('ms2_product_'.$key, 'resource_'.$key) ?: $key,
                'skipped' => in_array($key, $skip, true)
            ];
        }, array_keys($fields));
    }

    protected function getMsGalleryFields(string $columnPrefix = 'msProductFile.'): array
    {
        return [
            [
                'value' => $columnPrefix.'url',
                'text'  => 'Url изображения',
            ],
            [
                'value' => $columnPrefix.'name',
                'text'  => 'Название изображения',
            ],
            [
                'value' => $columnPrefix.'file',
                'text'  => 'Имя файла изображения',
            ],
        ];
    }

    protected function getMs2GalleryFields(string $columnPrefix = 'msResourceFile.'): array
    {
        return $this->getMsGalleryFields($columnPrefix);
    }

    protected function getMsVendorFields(string $columnPrefix = 'Vendor.', array $skip = ['id', 'properties']): array
    {
        $fields = $this->modx->getFields('msVendor');

        $this->modx->lexicon->load('minishop2:manager');

        return array_map(function (string $key) use ($columnPrefix, $skip) {
            return [
                'value'   => $columnPrefix.$key,
                'text'    => $this->getLexicon('ms2_'.$key, 'ms2_product_'.$key) ?: $key,
                'skipped' => in_array($key, $skip, true)
            ];
        }, array_keys($fields));
    }

    protected function getMsOptionFields(string $columnPrefix = 'Option.', array $skip = []): array
    {
        $fields = [];
        foreach (['color', 'size', 'tags'] as $key) {
            $fields[] = [
                'value'   => $columnPrefix.$key,
                'text'    => $this->getLexicon('ms2_product_'.$key, 'resource_'.$key),
                'skipped' => in_array($key, $skip, true)
            ];
        }

        foreach ($this->modx->getIterator('msOption') as $option) {
            /** @var msOption $option */
            $fields[] = [
                'value'   => $columnPrefix.$option->get('key'),
                'text'    => $option->get('caption'),
                'help'    => $this->getLexicon('ms2_ft_'.$option->get('type')),
                'skipped' => in_array($option->get('key'), $skip, true)
            ];
        }
        return $fields;
    }

    protected function getTvFields(string $columnPrefix = 'Tv.', array $skip = []): array
    {
        $fields = [];

        $this->modx->lexicon->load('tv_input_types');
        $this->modx->lexicon->load('tv_widget');

        foreach ($this->modx->getIterator('modTemplateVar') as $tv) {
            /** @var modTemplateVar $tv */
            $fields[] = [
                'value'   => $columnPrefix.$tv->get('name'),
                'text'    => $tv->get('caption'),
                'help'    => $this->getLexicon($tv->get('type')),
                'skipped' => in_array($tv->get('name'), $skip, true)
            ];
        }

        return $fields;
    }

    public static function getSitePaths(xPDO $xpdo): array
    {
        return [
            'site_url'    => $xpdo->getOption('site_url', null, MODX_SITE_URL),
            'core_path'   => $xpdo->getOption('core_path', null, MODX_CORE_PATH),
            'base_path'   => $xpdo->getOption('base_path', null, MODX_BASE_PATH),
            'assets_path' => $xpdo->getOption('assets_path', null, MODX_ASSETS_PATH),
            'assets_url'  => $xpdo->getOption('assets_url', null, MODX_ASSETS_URL),
        ];
    }

    public static function preparePath(xPDO $xpdo, string $path, bool $collapseSlashes = false): string
    {
        $paths = self::getSitePaths($xpdo);
        $path = str_replace(array_map(static function (string $key) {
            return '{'.$key.'}';
        }, array_keys($paths)), array_values($paths), $path);

        if ($collapseSlashes) {
            $path = preg_replace('/(?<!:)\/+/', '/', $path);
        }

        return $path;
    }

}