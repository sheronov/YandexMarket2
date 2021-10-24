<?php

namespace YandexMarket;

use Exception;
use modTemplateVar;
use modX;
use msOption;
use PDO;
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
        $this->checkStat();
    }

    public static function debugInfo(xPDO $xpdo): array
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

    public static function hasMiniShop2(): bool
    {
        return file_exists(MODX_CORE_PATH.'components/minishop2/model/minishop2/msproduct.class.php');
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
            ['header' => 'Возможные элементы']
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
        $fields[] = ['header' => 'Вы также можете ввести любое название'];

        return $fields;
    }

    public function listClassKeys(bool $withHeaders = false, bool $withDividers = false): array
    {
        $groups = [];

        if ($resourceFields = $this->getModResourceFields()) {
            $groups[] = [
                'header' => 'Поля ресурса',
                'groups' => ['offers', 'categories'],
                'fields' => $resourceFields,
            ];
        }

        $groups[] = [
            'header' => 'Вспомогательные поля предложений',
            'groups' => ['offers'],
            'fields' => $this->getOfferFields(),
        ];

        if (self::hasMs2Gallery()) {
            $groups[] = [
                'header' => 'Изображения ресурса ms2Gallery',
                'groups' => ['offers', 'categories'],
                'fields' => [['value' => 'ms2Gallery.image', 'text' => 'Изображения ресурса ms2Gallery']]
            ];
        }

        if (self::hasMiniShop2()) {
            if ($productFields = $this->getMsProductFields()) {
                $groups[] = [
                    'header' => 'Поля товара miniShop2',
                    'groups' => ['offers'],
                    'fields' => $productFields,
                ];
                $groups[] = [
                    'header' => 'Галерея miniShop2',
                    'groups' => ['offers'],
                    'fields' => [['value' => 'msGallery.image', 'text' => 'Изображения товара miniShop2']],
                ];
            }

            if ($optionFields = $this->getMsOptionFields()) {
                $groups[] = [
                    'header' => 'Опции miniShop2',
                    'groups' => ['offers'],
                    'fields' => $optionFields,
                ];
            }

            if ($vendorFields = $this->getMsVendorFields()) {
                $groups[] = [
                    'header' => 'Производитель miniShop2',
                    'groups' => ['offers'],
                    'fields' => $vendorFields,
                ];
            }
        }

        if ($tvFields = $this->getTvFields()) {
            $groups[] = [
                'header' => 'Дополнительные поля (TV)',
                'groups' => ['offers', 'categories'],
                'fields' => $tvFields,
            ];
        }

        $groups[] = [
            'header' => 'Поля родительской категории (стандартные)',
            'groups' => ['offers'],
            'fields' => [
                ['value' => 'Category.pagetitle', 'text' => 'Заголовок родительской категории'],
                ['value' => 'Category.name', 'text' => 'Формат под любое другое поле родителя']
            ]
        ];

        $groups[] = [
            'header' => 'Дополнительные поля категории (TV)',
            'groups' => ['offers'],
            'fields' => [['value' => 'CategoryTV.name', 'text' => 'Формат для ТВ-полей категории']]
        ];

        if (self::hasMsOp2() && $modificationFields = $this->getMsOp2ModificationFields()) {
            $groups[] = [
                'header' => 'Поля модификации msOptionsPrice2',
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

    // TODO: в лексиконы перевести тексты
    protected function getOfferFields(string $columnPrefix = 'Offer.'): array
    {
        return [
            ['value' => $columnPrefix.'url', 'text' => 'Полная ссылка на товар'],
            ['value' => $columnPrefix.'price', 'text' => 'Цена с учётом плагинов ms2 и модификаций msOp2'],
            ['value' => $columnPrefix.'image', 'text' => 'Изображение товара с полной ссылкой (из поля image)']
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

    protected function getTvFields(string $columnPrefix = 'TV.', array $skip = []): array
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
            'images_url'  => $xpdo->getOption('yandexmarket2_images_url', null,
                $xpdo->getOption('site_url', null, MODX_SITE_URL)),
        ];
    }

    public static function preparePath(xPDO $xpdo, string $path, bool $collapseSlashes = false): string
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
        /** @var \modRegistry $registry */
        if (!$registry = $this->modx->getService('registry', 'registry.modRegistry')) {
            return;
        }
        if (!$register = $registry->getRegister('user', 'registry.modDbRegister')) {
            return;
        }
        $register->connect();
        $register->subscribe('/modstore/'.md5($key));
        if ($register->read(['poll_limit' => 1, 'remove_read' => false])) {
            return;
        }
        $c = $this->modx->newQuery('transport.modTransportProvider', ['service_url:LIKE' => '%modstore%']);
        $c->select('username,api_key');
        /** @var \modRest $rest */
        $rest = $this->modx->getService('modRest', 'rest.modRest', '', [
            'baseUrl'        => 'https://modstore.pro/extras',
            'suppressSuffix' => true,
            'timeout'        => 1,
            'connectTimeout' => 1,
        ]);

        if ($rest) {
            $level = $this->modx->getLogLevel();
            $this->modx->setLogLevel(modX::LOG_LEVEL_FATAL);

            $tpQuery = $this->modx->newQuery('transport.modTransportPackage');
            $tpQuery->where(['signature:LIKE' => 'yandexmarket2%']);
            $tpQuery->select('signature');
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
                'php_version'        => XPDO_PHP_VERSION,
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
                $qtv = $this->modx->newQuery('modTemplateVar');
                $qtv->where(['name' => $key]);
                if ($tv = $this->modx->getObject('modTemplateVar', $qtv)) {
                    $q = $this->modx->newQuery('modTemplateVarResource');
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
                $q = $this->modx->newQuery('modResource');
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
