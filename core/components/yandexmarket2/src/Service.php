<?php

namespace YandexMarket;

use xPDO\xPDO as xPDO3;
use MODX\Revolution\modTemplateVar as modTemplateVar3;
use MODX\Revolution\modTemplateVarResource as modTemplateVarResource3;
use MODX\Revolution\Registry\modDbRegister as modDbRegister3;
use MODX\Revolution\Registry\modRegistry as modRegistry3;
use MODX\Revolution\Rest\modRest as modRest3;
use MODX\Revolution\Transport\modTransportPackage as modTransportPackage3;
use MODX\Revolution\Transport\modTransportProvider as modTransportProvider3;
use YandexMarket\Marketplaces\Marketplace;
use YandexMarket\Models\Field;
use Exception;
use PDO;

if (!defined('MODX3')) {
    define('MODX3', class_exists('MODX\Revolution\modX'));
}

class Service
{
    /** @var \MODX\Revolution\modX|\modX */
    protected $modx;
    protected $config = [];

    const LOG_LEVEL_FATAL = MODX3 ? xPDO3::LOG_LEVEL_FATAL : \xPDO::LOG_LEVEL_FATAL;
    const LOG_LEVEL_ERROR = MODX3 ? xPDO3::LOG_LEVEL_ERROR : \xPDO::LOG_LEVEL_ERROR;
    const LOG_LEVEL_WARN  = MODX3 ? xPDO3::LOG_LEVEL_WARN : \xPDO::LOG_LEVEL_WARN;
    const LOG_LEVEL_INFO  = MODX3 ? xPDO3::LOG_LEVEL_INFO : \xPDO::LOG_LEVEL_INFO;
    const LOG_LEVEL_DEBUG = MODX3 ? xPDO3::LOG_LEVEL_DEBUG : \xPDO::LOG_LEVEL_DEBUG;

    /**
     * @param  \MODX\Revolution\modX|\modX  $modx
     * @param  array  $config
     */
    public function __construct($modx, array $config = [])
    {
        $this->modx = $modx;
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');

        $this->config = array_merge($config, [
            'corePath'  => $corePath,
            'modelPath' => $corePath.'model/',
        ]);

        // $this->modx->addPackage('yandexmarket2', $corePath.'Model/');
        $this->modx->lexicon->load('yandexmarket2:default');
        $this->checkStat();
    }

    /**
     * @param  xPDO3|\xPDO  $xpdo
     *
     * @return array
     */
    public static function debugInfo($xpdo): array
    {
        if (!$xpdo->getOption('yandexmarket2_debug_mode')) {
            return [];
        }
        return [
            'queries'   => $xpdo->executedQueries,
            'queryTime' => sprintf("%2.4f s", $xpdo->queryTime),
            'totalTime' => sprintf("%2.4f s", (microtime(true) - $xpdo->startTime)),
            'memory'    => number_format(memory_get_usage(true) / 1024, 0, ",", " ").' kb'
        ];
    }

    public static function isMODX3(): bool
    {
        return MODX3;
    }

    public static function hasMiniShop2(): bool
    {
        return file_exists(MODX_CORE_PATH.'components/minishop2/model/minishop2/msproduct.class.php');
    }

    public static function hasPdoTools(): bool
    {
        return file_exists(MODX_CORE_PATH.'components/pdotools/model/pdotools/pdotools.class.php');
    }

    public static function hasMs2Gallery(): bool
    {
        return file_exists(MODX_CORE_PATH.'components/ms2gallery/model/ms2gallery/ms2gallery.class.php');
    }

    public static function hasMsOp2(): bool
    {
        return file_exists(MODX_CORE_PATH.'components/msoptionsprice/model/msoptionsprice/msoptionsprice.class.php');
    }

    protected function getLexicon(string $key, string $fallbackKey = null): string
    {
        if (($key !== $lexicon = $this->modx->lexicon($key))) {
            return $lexicon;
        }

        if ($fallbackKey && $fallbackKey !== $key && $fallbackKey !== $lexicon = $this->modx->lexicon($fallbackKey)) {
            return $lexicon;
        }

        return '';
    }

    public function getAvailableFields(): array
    {
        $fields = [];

        foreach (Field::TYPES_DATA as $field => $data) {
            $fields[] = array_merge($data, [
                'value' => $field,
                'text'  => $this->getLexicon('ym2_field_type_'.$field) ?: $field,
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
            if (!empty($marketplace['lexicon'])) {
                $this->modx->lexicon->load($marketplace['lexicon']);
            }
            $type = $marketplace['key'];
            $marketplace['value'] = $type;
            $marketplace['text'] = $this->getLexicon('ym2_marketplace_'.$type) ?: $type;
            unset($marketplace['class'], $marketplace['key']);

            $marketplace['shop_fields'] = $this->prepareFields($marketplace['shop_fields'], $type, 'shop');
            $marketplace['offer_fields'] = $this->prepareFields($marketplace['offer_fields'], $type, 'offer');

            return $marketplace;
        }, array_values(Marketplace::listMarketplaces()));
    }

    protected function prepareFields(array $keyFields, string $marketplace, string $parent = ''): array
    {
        $fields = [
            ['header' => $this->modx->lexicon('ym2_fields_possible_elements')]
        ];

        foreach ($keyFields as $key => $data) {
            $fields[] = array_merge($data, [
                'value' => $key,
                'text'  => $this->getLexicon('ym2_'.$marketplace.'_'.$parent.'_'.$key,
                    'ym2_'.$marketplace.'_'.$key) ?: $key,
                'type'  => $data['type'] ?? Field::TYPE_DEFAULT,
            ]);
        }
        $fields[] = ['divider' => true];
        $fields[] = ['header' => $this->modx->lexicon('ym2_fields_type_any_name')];

        return $fields;
    }

    public function listClassKeys(bool $withHeaders = false, bool $withDividers = false): array
    {
        $groups = [];

        if ($resourceFields = $this->getModResourceFields()) {
            $groups[] = [
                'header' => $this->modx->lexicon('ym2_resource_fields'),
                'groups' => ['offers', 'categories'],
                'fields' => $resourceFields,
            ];
        }

        $groups[] = [
            'header' => $this->modx->lexicon('ym2_resource_additional_fields'),
            'groups' => ['offers'],
            'fields' => $this->getOfferFields(),
        ];

        if (self::hasMs2Gallery()) {
            $groups[] = [
                'header' => $this->modx->lexicon('ym2_ms2gallery_header'),
                'groups' => ['offers', 'categories'],
                'fields' => [['value' => 'ms2Gallery.image', 'text' => $this->modx->lexicon('ym2_ms2gallery_header')]]
            ];
        }

        if (self::hasMiniShop2()) {
            if ($productFields = $this->getMsProductFields()) {
                $groups[] = [
                    'header' => $this->modx->lexicon('ym2_minishop2_fields'),
                    'groups' => ['offers'],
                    'fields' => $productFields,
                ];
                $groups[] = [
                    'header' => $this->modx->lexicon('ym2_minishop2_gallery'),
                    'groups' => ['offers'],
                    'fields' => [['value' => 'msGallery.image', 'text' => $this->modx->lexicon('ym2_minishop2_images')]],
                ];
            }

            if ($optionFields = $this->getMsOptionFields()) {
                $groups[] = [
                    'header' => $this->modx->lexicon('ym2_minishop2_options'),
                    'groups' => ['offers'],
                    'fields' => $optionFields,
                ];
            }

            if ($vendorFields = $this->getMsVendorFields()) {
                $groups[] = [
                    'header' => $this->modx->lexicon('ym2_minishop2_vendor'),
                    'groups' => ['offers'],
                    'fields' => $vendorFields,
                ];
            }
        }

        if ($tvFields = $this->getTvFields()) {
            $groups[] = [
                'header' => $this->modx->lexicon('ym2_tv_fields'),
                'groups' => ['offers', 'categories'],
                'fields' => $tvFields,
            ];
        }

        $groups[] = [
            'header' => $this->modx->lexicon('ym2_category_fields'),
            'groups' => ['offers'],
            'fields' => [
                ['value' => 'Category.pagetitle', 'text' => $this->modx->lexicon('ym2_category_pagetitle')],
                ['value' => 'Category.name', 'text' => $this->modx->lexicon('ym2_category_name')]
            ]
        ];

        $groups[] = [
            'header' => $this->modx->lexicon('ym2_category_tv'),
            'groups' => ['offers'],
            'fields' => [['value' => 'CategoryTV.name', 'text' => $this->modx->lexicon('ym2_category_tv_name')]]
        ];

        if (self::hasMsOp2() && $modificationFields = $this->getMsOp2ModificationFields()) {
            $groups[] = [
                'header' => $this->modx->lexicon('ym2_msop2_modification_fields'),
                'groups' => ['offers'],
                'fields' => $modificationFields,
            ];
        }

        return $groups;
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
            ['value' => $columnPrefix.'url', 'text' => $this->modx->lexicon('ym2_offer_field_url')],
            ['value' => $columnPrefix.'price', 'text' => $this->modx->lexicon('ym2_offer_field_price')],
            ['value' => $columnPrefix.'image', 'text' => $this->modx->lexicon('ym2_offer_field_image')]
        ];
    }

    protected function getMsOp2ModificationFields(
        string $columnPrefix = 'Modification.',
        array $skip = ['rid', 'sync_id', 'sync_service', 'rank']
    ): array {
        $path = $this->modx->getOption('core_path', null, MODX_CORE_PATH).'components/msoptionsprice/model/';
        if (!$this->modx->addPackage('msoptionsprice', $path)) {
            return [];
        }
        $fields = $this->modx->getFields('msopModification');
        $this->modx->lexicon->load('msoptionsprice:manager');

        return array_merge(array_map(function (string $key) use ($columnPrefix, $skip) {
            return [
                'value'   => $columnPrefix.$key,
                'text'    => $this->getLexicon('msoptionsprice_'.$key) ?: $key,
                'skipped' => in_array($key, $skip, true)
            ];
        }, array_keys($fields)), [
            [
                'value'   => $columnPrefix.'options',
                'text'    => $this->getLexicon('ym2_msoptionsprice_options') ?: 'options',
                'skipped' => in_array('options', $skip, true)
            ]
        ]);
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
            /** @var \msOption $option */
            $fields[] = [
                'value'   => $columnPrefix.$option->get('key'),
                'text'    => $option->get('caption'),
                'help'    => $this->getLexicon('ms2_ft_'.$option->get('type')),
                'skipped' => in_array($option->get('key'), $skip, true)
            ];
        }
        return $fields;
    }

    protected function getTvFields(string $columnPrefix = 'TV.', array $skip = []): array
    {
        $fields = [];

        $this->modx->lexicon->load('tv_input_types');
        $this->modx->lexicon->load('tv_widget');

        foreach ($this->modx->getIterator(MODX3 ? modTemplateVar3::class : \modTemplateVar::class) as $tv) {
            /** @var \modTemplateVar|modTemplateVar3 $tv */
            $fields[] = [
                'value'   => $columnPrefix.$tv->get('name'),
                'text'    => $tv->get('caption'),
                'help'    => $this->getLexicon($tv->get('type')),
                'skipped' => in_array($tv->get('name'), $skip, true)
            ];
        }

        return $fields;
    }

    /**
     * @param  xPDO3|\xPDO  $xpdo
     *
     * @return array
     */
    public static function getSitePaths($xpdo): array
    {
        $siteUrl = $xpdo->getOption('yandexmarket2_site_url', null,
            $xpdo->getOption('site_url', null, MODX_SITE_URL), true);
        return [
            'site_url'    => $siteUrl,
            'images_url'  => $xpdo->getOption('yandexmarket2_images_url', null, $siteUrl, true),
            'core_path'   => $xpdo->getOption('core_path', null, MODX_CORE_PATH),
            'base_path'   => $xpdo->getOption('base_path', null, MODX_BASE_PATH),
            'assets_path' => $xpdo->getOption('assets_path', null, MODX_ASSETS_PATH),
            'assets_url'  => $xpdo->getOption('assets_url', null, MODX_ASSETS_URL),
        ];
    }

    /**
     * @param  xPDO3|\xPDO  $xpdo
     * @param  string  $path
     * @param  bool  $collapseSlashes
     *
     * @return string
     */
    public static function preparePath($xpdo, string $path, bool $collapseSlashes = false): string
    {
        $paths = self::getSitePaths($xpdo);
        $path = str_replace(array_map(static function (string $key) {
            return '{'.$key.'}';
        }, array_keys($paths)), array_values($paths), $path);

        if ($collapseSlashes) {
            $path = preg_replace('/(?<!:)\/+/', '/', (string)$path);
        }

        return $path;
    }

    protected function checkStat()
    {
        $key = 'yandexmarket2';
        /** @var modRegistry3|\modRegistry $registry */
        if (!$registry = $this->modx->getService('registry', MODX3 ? modRegistry3::class : 'registry.modRegistry')) {
            return;
        }
        if (!$register = $registry->getRegister('user', MODX3 ? modDbRegister3::class : 'registry.modDbRegister')) {
            return;
        }
        $register->connect();
        $register->subscribe('/modstore/'.md5($key));
        if ($register->read(['poll_limit' => 1, 'remove_read' => false])) {
            return;
        }
        $c = $this->modx->newQuery(MODX3 ? modTransportProvider3::class : 'transport.modTransportProvider')
            ->where(['service_url:LIKE' => '%modstore%'])
            ->select('username,api_key');
        /** @var modRest3|\modRest $rest */
        $rest = $this->modx->getService('modRest', MODX3 ? modRest3::class : 'rest.modRest', '', [
            'baseUrl'        => 'https://modstore.pro/extras',
            'suppressSuffix' => true,
            'timeout'        => 1,
            'connectTimeout' => 1,
        ]);

        if ($rest) {
            $level = $this->modx->getLogLevel();
            $this->modx->setLogLevel(self::LOG_LEVEL_FATAL);

            $tpQuery = $this->modx->newQuery(MODX3 ? modTransportPackage3::class : 'transport.modTransportPackage')
                ->where(['signature:LIKE' => 'yandexmarket2%'])
                ->select('signature');
            $version = $this->modx->getValue($tpQuery->prepare()) ?: '1.0.0-beta';

            if (method_exists($this->modx, 'getVersionData')) {
                $modxVersion = $this->modx->getVersionData();
            } elseif (isset($this->modx->version)) {
                $modxVersion = $this->modx->version;
            } else {
                $modxVersion = [];
            }

            $rest->post('stat', [
                'package'            => $key,
                'version'            => str_replace('yandexmarket2-', '', $version),
                'keys'               => $c->prepare() && $c->stmt->execute()
                    ? $c->stmt->fetchAll(PDO::FETCH_ASSOC)
                    : [],
                'uuid'               => $this->modx->uuid,
                'database'           => $this->modx->config['dbtype'],
                'revolution_version' => $modxVersion['code_name'].'-'.$modxVersion['full_version'],
                'supports'           => $modxVersion['code_name'].'-'.$modxVersion['full_version'],
                'http_host'          => $this->modx->getOption('http_host'),
                'php_version'        => defined('XPDO_PHP_VERSION') ? XPDO_PHP_VERSION : PHP_VERSION,
                'language'           => $this->modx->getOption('manager_language'),
            ]);
            $this->modx->setLogLevel($level);
        }
        $register->subscribe('/modstore/');
        $register->send('/modstore/', [md5($key) => true], ['ttl' => 3600 * 24]);
    }

    public function getValues(string $column): array
    {
        if (mb_strpos($column, '.') !== false) {
            list($class, $key) = explode('.', $column, 2);
        } else {
            $class = 'modResource';
            $key = $column;
        }
        $mode = PDO::FETCH_COLUMN;
        $callback = static function ($value) {
            return $value;
        };

        switch (mb_strtolower($class)) {
            case 'vendor':
            case 'msvendor':
                $q = $this->modx->newQuery('msVendor');
                $q->select($key);
                break;
            case 'tv':
            case 'modtemplatevar':
            case 'modtemplatevarresource':
                $qtv = $this->modx->newQuery(MODX3 ? modTemplateVar3::class : \modTemplateVar::class);
                $qtv->where(['name' => $key]);
                if ($tv = $this->modx->getObject($qtv->getClass(), $qtv)) {
                    $q = $this->modx->newQuery(MODX3 ? modTemplateVarResource3::class : \modTemplateVarResource::class);
                    $q->where(['tmplvarid' => $tv->get('id')]);
                    $q->select('value');
                }
                break;
            case 'option';
            case 'msoption';
            case 'msproductoption';
                $q = $this->modx->newQuery('msProductOption');
                $q->where(['key' => $key]);
                $q->select('value');
                break;
            case 'offer':
            case 'resource':
            case 'modresource':
                $q = $this->modx->newQuery(MODX3 ? \MODX\Revolution\modResource::class : \modResource::class);
                $q->select($key);
                break;
            case 'data':
            case 'msproduct':
            case 'msproductdata':
                $q = $this->modx->newQuery('msProductData');
                $q->select($key);
                break;
        }

        if (isset($q)) {
            $q->limit(100);
            $q->distinct(true);
            if ($q->prepare() && $q->stmt->execute()) {
                return array_map($callback, $q->stmt->fetchAll($mode));
            }
        }
        return [];
    }

}
