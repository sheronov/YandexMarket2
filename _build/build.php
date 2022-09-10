<?php

use MODX\Revolution\modX as modX3;
use MODX\Revolution\modEvent as modEvent3;
use MODX\Revolution\Error\modError as modError3;
use MODX\Revolution\modCategory as modCategory3;
use MODX\Revolution\Processors\System\ClearCache as ClearCache3;
use MODX\Revolution\Rest\modRest as modRest3;
use MODX\Revolution\Transport\modPackageBuilder as modPackageBuilder3;
use MODX\Revolution\Transport\modTransportPackage as modTransportPackage3;
use MODX\Revolution\modSystemSetting as modSystemSetting3;
use MODX\Revolution\modMenu as modMenu3;
use MODX\Revolution\modPlugin as modPlugin3;
use MODX\Revolution\modPluginEvent as modPluginEvent3;
use MODX\Revolution\Transport\modTransportProvider as modTransportProvider3;
use xPDO\Om\xPDOManager as xPDOManager3;
use xPDO\Transport\xPDOTransport as xPDOTransport3;
use xPDO\Transport\xPDOFileVehicle as xPDOFileVehicle3;
use xPDO\Transport\xPDOScriptVehicle as xPDOScriptVehicle3;
use xPDO\xPDO as xPDO3;

/** @var array $config */
if (!file_exists(__DIR__.'/config.inc.php')) {
    exit('Could not load MODX config. Please specify correct MODX_CORE_PATH constant in config file!');
}
$config = require(__DIR__.'/config.inc.php');
require_once MODX_CORE_PATH.'model/modx/modx.class.php';

if (!defined('MODX3')) {
    define('MODX3', class_exists('MODX\Revolution\modX'));
}

if (!MODX3) {
    require_once MODX_CORE_PATH.'model/modx/transport/modpackagebuilder.class.php';
    require_once MODX_CORE_PATH.'xpdo/xpdo.class.php';
    require_once MODX_CORE_PATH.'xpdo/transport/xpdotransport.class.php';
    require_once MODX_CORE_PATH.'xpdo/transport/xpdovehicle.class.php';
    require_once MODX_CORE_PATH.'xpdo/transport/xpdofilevehicle.class.php';
    require_once MODX_CORE_PATH.'xpdo/transport/xpdoscriptvehicle.class.php';
    require_once MODX_CORE_PATH.'xpdo/transport/xpdoobjectvehicle.class.php';
}
require_once __DIR__.'/helpers/encryptedvehicle.class.php';

if (!defined('LOG_LEVEL_INFO')) {
    define('LOG_LEVEL_INFO', MODX3 ? xPDO3::LOG_LEVEL_INFO : xPDO::LOG_LEVEL_INFO);
}

if (!defined('LOG_LEVEL_ERROR')) {
    define('LOG_LEVEL_ERROR', MODX3 ? xPDO3::LOG_LEVEL_ERROR : xPDO::LOG_LEVEL_ERROR);
}

if (!defined('XPDO_PHP_VERSION')) {
    define('XPDO_PHP_VERSION', PHP_VERSION);
}

class YandexMarket2Package
{
    /** @var modX|modX3 $modx */
    public $modx;
    /** @var array $config */
    public $config = [];

    /** @var modPackageBuilder|modPackageBuilder3 $builder */
    public $builder;
    /** @var modCategory|modCategory3 $vehicle */
    public $category;
    public $category_attributes = [];

    protected $_idx = 1;

    /**
     * YandexMarket2Package constructor.
     *
     * @param  modX|modX3  $modX
     * @param  array  $config
     */
    public function __construct($modX, array $config = [])
    {
        $this->modx = $modX;
        $this->modx->initialize('mgr');
        $this->modx->getService('error', MODX3 ? modError3::class : 'error.modError');

        $root = dirname(__FILE__, 2).'/';
        $assets = $root.'assets/components/'.$config['name_lower'].'/';
        $core = $root.'core/components/'.$config['name_lower'].'/';

        $this->config = array_merge([
            'log_level'  => LOG_LEVEL_INFO,
            'log_target' => XPDO_CLI_MODE ? 'ECHO' : 'HTML',

            'root'      => $root,
            'build'     => $root.'_build/',
            'helpers'   => $root.'_build/helpers/',
            'elements'  => $root.'_build/elements/',
            'resolvers' => $root.'_build/resolvers/',

            'assets' => $assets,
            'core'   => $core,
        ], $config);
        $this->modx->setLogLevel($this->config['log_level']);
        $this->modx->setLogTarget($this->config['log_target']);

        $this->initialize();
    }

    /**
     * Initialize package builder
     */
    protected function initialize()
    {
        $this->defineEncodeKey();

        $this->builder = MODX3 ? new modPackageBuilder3($this->modx) : new modPackageBuilder($this->modx);
        $this->builder->createPackage($this->config['name_lower'], $this->config['version'], $this->config['release']);

        $this->addEncryptionHelpers();
        // $this->addSchemeFile();

        $this->builder->registerNamespace($this->config['name_lower'], false, true,
            '{core_path}components/'.$this->config['name_lower'].'/');

        $this->modx->log(LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

        $this->category = $this->modx->newObject(MODX3 ? modCategory3::class : modCategory::class);
        $this->category->set('category', $this->config['name']);
        $this->category_attributes = [
            'vehicle_class'                                                                                      => EncryptedVehicle::class,
            'vehicle_package'                                                                                    => '',
            MODX3 ? xPDOTransport3::ABORT_INSTALL_ON_VEHICLE_FAIL : xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            MODX3 ? xPDOTransport3::UNIQUE_KEY : xPDOTransport::UNIQUE_KEY                                       => 'category',
            MODX3 ? xPDOTransport3::PRESERVE_KEYS : xPDOTransport::PRESERVE_KEYS                                 => false,
            MODX3 ? xPDOTransport3::UPDATE_OBJECT : xPDOTransport::UPDATE_OBJECT                                 => true,
            MODX3 ? xPDOTransport3::RELATED_OBJECTS : xPDOTransport::RELATED_OBJECTS                             => true,
            MODX3 ? xPDOTransport3::RELATED_OBJECT_ATTRIBUTES : xPDOTransport::RELATED_OBJECT_ATTRIBUTES         => [],
        ];
        $this->modx->log(LOG_LEVEL_INFO, 'Created main Category.');
    }

    protected function addEncryptionHelpers()
    {
        $this->builder->package->put(MODX3 ? new xPDOFileVehicle3() : new xPDOFileVehicle(), [
            'vehicle_class'                                                          => MODX3 ? xPDOFileVehicle3::class : xPDOFileVehicle::class,
            'vehicle_package'                                                        => '',
            'namespace'                                                              => 'yandexmarket2',
            MODX3 ? xPDOTransport3::UNINSTALL_FILES : xPDOTransport::UNINSTALL_FILES => false,
            'object'                                                                 => [
                'source' => $this->config['helpers'].'encryptedvehicle.class.php',
                'target' => "return MODX_CORE_PATH . 'components/yandexmarket2/';",
            ]
        ]);

        $this->builder->package->put(MODX3 ? new xPDOScriptVehicle3() : new xPDOScriptVehicle(), [
            'vehicle_class'   => MODX3 ? xPDOScriptVehicle3::class : xPDOScriptVehicle::class,
            'vehicle_package' => '',
            'namespace'       => 'yandexmarket2',
            'object'          => [
                'source' => $this->config['helpers'].'encryption.php'
            ]
        ]);

        // $this->builder->package->put([
        //     'source' => $this->config['core'],
        //     'target' => "return MODX_CORE_PATH . 'components/';",
        // ], [
        //     'vehicle_class' => 'xPDOFileVehicle',
        //     'resolve'       => [
        //         [
        //             'type'   => 'php',
        //             'source' => $this->config['resolvers'].'encryption.php',
        //         ],
        //     ],
        // ]);
        $this->modx->log(LOG_LEVEL_INFO, 'Initialized encryption');
    }

    protected function defineEncodeKey(): string
    {
        $key = '';
        /** @var modTransportProvider|modTransportProvider3 $provider */
        if ($provider = $this->modx->getObject(MODX3 ? modTransportProvider3::class : 'transport.modTransportProvider',
            $this->config['modstore_id'])) {
            $provider->xpdo->setOption('contentType', 'default');
            $modxVersion = $this->modx->getVersionData();

            $params = [
                'package'            => $this->config['name_lower'],
                'version'            => $this->config['version'].'-'.$this->config['release'],
                'username'           => $provider->username,
                'api_key'            => $provider->api_key,
                'vehicle_version'    => '2.0.0',
                'database'           => $this->modx->config['dbtype'],
                'revolution_version' => $modxVersion['code_name'].'-'.$modxVersion['full_version'],
                'supports'           => $modxVersion['code_name'].'-'.$modxVersion['full_version'],
                'http_host'          => $this->modx->getOption('http_host'),
                'php_version'        => XPDO_PHP_VERSION,
                'language'           => $this->modx->getOption('manager_language'),
            ];

            /** @var modRest|modRest3 $rest */
            $rest = $this->modx->getService('modRest', MODX3 ? modRest3::class : 'rest.modRest', '', [
                'baseUrl'        => rtrim($provider->get('service_url'), '/'),
                'suppressSuffix' => true,
                'timeout'        => 10,
                'connectTimeout' => 10,
                'format'         => 'xml',
            ]);

            $response = $rest->post('package/encode', $params);
            if ($response->responseError) {
                $this->modx->log(LOG_LEVEL_ERROR, $response->responseError);
            } else {
                $data = $response->process();
                if (!empty($data['key'])) {
                    $key = $data['key'];
                    $this->modx->log(LOG_LEVEL_INFO, 'Received key from modstore: '.$key);
                } elseif (!empty($data->message)) {
                    $this->modx->log(LOG_LEVEL_ERROR, $data->message);
                }
            }
        }

        if (!empty($key)) {
            define('PKG_ENCODE_KEY', $key);
        } else {
            $this->modx->log(LOG_LEVEL_INFO, 'Problem with getting encode key from modstore');
        }
        return $key;
    }

    /**
     * Update the model
     */
    protected function model()
    {
        $schemaFile = $this->config['core'].'schema/'.$this->config['name_lower'].'.mysql.schema.xml';
        $outputDir = MODX3 ? $this->config['core'].'src/' : $this->config['core'].'model/';
        $schemaContent = file_get_contents($schemaFile);

        if (!file_exists($schemaFile) || empty($schemaContent)) {
            return;
        }

        $tmpSchemaFile = tmpfile();
        $schemaXML = new SimpleXMLElement($schemaContent);
        if (!MODX3) {
            $schemaXML->attributes()->package = 'yandexmarket2';
            $schemaXML->attributes()->version = '1.1';
            $schemaXML->attributes()->baseClass = 'xPDOObject';
            foreach ($schemaXML->children() as $child) {
                $child->attributes()->extends = 'xPDOSimpleObject';
                foreach ($child->children() as $field) {
                    if ($field->attributes()->class) {
                        $field->attributes()->class = array_slice(explode('\\', (string)$field->attributes()->class),
                            -1)[0];
                    }
                }
            }
        }
        $schemaXML->asXML(stream_get_meta_data($tmpSchemaFile)['uri']);

        // if ($cache = $this->modx->getCacheManager()) {
        //     $cache->deleteTree(
        //         $outputDir.'mysql',
        //         ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
        //     );
        // }

        /** @var xPDOManager|xPDOManager3 $manager */
        $manager = $this->modx->getManager();
        $generator = $manager->getGenerator();
        $generator->parseSchema(
            stream_get_meta_data($tmpSchemaFile)['uri'],
            $outputDir,
            MODX3 ? [
                "compile"         => 0,
                "update"          => 1,
                "regenerate"      => 1,
                "namespacePrefix" => "YandexMarket\\"
            ] : false
        );
        $this->modx->log(LOG_LEVEL_INFO, 'Model updated');
    }

    /**
     * Install nodejs and update assets
     */
    protected function assets()
    {
        $output = [];
        if (!file_exists($this->config['build'].'node_modules')) {
            putenv('PATH='.trim(shell_exec('echo $PATH')).':'.dirname(MODX_BASE_PATH).'/');
            if (file_exists($this->config['build'].'package.json')) {
                $this->modx->log(LOG_LEVEL_INFO, 'Trying to install or update nodejs dependencies');
                $output = [
                    shell_exec('cd '.$this->config['build'].' && npm config set scripts-prepend-node-path true && npm install'),
                ];
            }
            if (file_exists($this->config['build'].'gulpfile.js')) {
                $output = array_merge($output, [
                    shell_exec('cd '.$this->config['build'].' && npm link gulp'),
                    shell_exec('cd '.$this->config['build'].' && gulp copy'),
                ]);
            }
            if ($output) {
                $this->modx->log(LOG_LEVEL_INFO, implode("\n", array_map('trim', $output)));
            }
        }
        if (file_exists($this->config['build'].'gulpfile.js')) {
            $output = shell_exec('cd '.$this->config['build'].' && gulp default 2>&1');
            $this->modx->log(LOG_LEVEL_INFO, 'Compile scripts and styles '.trim($output));
        }
    }

    /**
     * Add settings
     */
    protected function settings()
    {
        $settings = include($this->config['elements'].'settings.php');
        if (!is_array($settings)) {
            $this->modx->log(LOG_LEVEL_ERROR, 'Could not package in System Settings');
            return;
        }
        $attributes = [
            'vehicle_class'                                                                                      => EncryptedVehicle::class,
            'vehicle_package'                                                                                    => '',
            MODX3 ? xPDOTransport3::ABORT_INSTALL_ON_VEHICLE_FAIL : xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            MODX3 ? xPDOTransport3::UNIQUE_KEY : xPDOTransport::UNIQUE_KEY                                       => 'key',
            MODX3 ? xPDOTransport3::PRESERVE_KEYS : xPDOTransport::PRESERVE_KEYS                                 => true,
            MODX3 ? xPDOTransport3::UPDATE_OBJECT : xPDOTransport::UPDATE_OBJECT                                 => !empty($this->config['update']['settings']),
            MODX3 ? xPDOTransport3::RELATED_OBJECTS : xPDOTransport::RELATED_OBJECTS                             => false,
        ];
        foreach ($settings as $name => $data) {
            /** @var modSystemSetting|modSystemSetting3 $setting */
            $setting = $this->modx->newObject(MODX3 ? modSystemSetting3::class : modSystemSetting::class);
            $setting->fromArray(array_merge([
                'key'       => $this->config['name_lower'].'_'.$name,
                'namespace' => $this->config['name_lower'],
            ], $data), '', true, true);
            $vehicle = $this->builder->createVehicle($setting, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(LOG_LEVEL_INFO, 'Packaged in '.count($settings).' System Settings');
    }

    /**
     * Add menus
     */
    protected function menus()
    {
        $menus = include($this->config['elements'].'menus.php');
        if (!is_array($menus)) {
            $this->modx->log(LOG_LEVEL_ERROR, 'Could not package in Menus');
            return;
        }
        $attributes = [
            'vehicle_class'                                                                                      => EncryptedVehicle::class,
            'vehicle_package'                                                                                    => '',
            MODX3 ? xPDOTransport3::ABORT_INSTALL_ON_VEHICLE_FAIL : xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            MODX3 ? xPDOTransport3::PRESERVE_KEYS : xPDOTransport::PRESERVE_KEYS                                 => true,
            MODX3 ? xPDOTransport3::UPDATE_OBJECT : xPDOTransport::UPDATE_OBJECT                                 => !empty($this->config['update']['menus']),
            MODX3 ? xPDOTransport3::UNIQUE_KEY : xPDOTransport::UNIQUE_KEY                                       => 'text',
            MODX3 ? xPDOTransport3::RELATED_OBJECTS : xPDOTransport::RELATED_OBJECTS                             => true,
        ];

        foreach ($menus as $name => $data) {
            /** @var modMenu|modMenu3 $menu */
            $menu = $this->modx->newObject(MODX3 ? modMenu3::class : modMenu::class);
            $menu->fromArray(array_merge([
                'text'      => $name,
                'parent'    => 'components',
                'namespace' => $this->config['name_lower'],
                'icon'      => '',
                'menuindex' => 0,
                'params'    => '',
                'handler'   => '',
            ], $data), '', true, true);
            $vehicle = $this->builder->createVehicle($menu, $attributes);
            $this->builder->putVehicle($vehicle);
        }

        $this->modx->log(LOG_LEVEL_INFO, 'Packaged in '.count($menus).' Menus');
    }

    /**
     * Add plugins
     */
    protected function plugins()
    {
        $plugins = include($this->config['elements'].'plugins.php');
        if (!is_array($plugins)) {
            $this->modx->log(LOG_LEVEL_ERROR, 'Could not package in Plugins');

            return;
        }
        $this->category_attributes[MODX3 ? xPDOTransport3::RELATED_OBJECT_ATTRIBUTES : xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = [
            'vehicle_class'                                                                                      => EncryptedVehicle::class,
            'vehicle_package'                                                                                    => '',
            MODX3 ? xPDOTransport3::ABORT_INSTALL_ON_VEHICLE_FAIL : xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            MODX3 ? xPDOTransport3::UNIQUE_KEY : xPDOTransport::UNIQUE_KEY                                       => 'name',
            MODX3 ? xPDOTransport3::PRESERVE_KEYS : xPDOTransport::PRESERVE_KEYS                                 => false,
            MODX3 ? xPDOTransport3::UPDATE_OBJECT : xPDOTransport::UPDATE_OBJECT                                 => !empty($this->config['update']['plugins']),
            MODX3 ? xPDOTransport3::RELATED_OBJECTS : xPDOTransport::RELATED_OBJECTS                             => true,
            MODX3 ? xPDOTransport3::RELATED_OBJECT_ATTRIBUTES : xPDOTransport::RELATED_OBJECT_ATTRIBUTES         => [
                'PluginEvents' => [
                    MODX3 ? xPDOTransport3::PRESERVE_KEYS : xPDOTransport::PRESERVE_KEYS => true,
                    MODX3 ? xPDOTransport3::UPDATE_OBJECT : xPDOTransport::UPDATE_OBJECT => true,
                    MODX3 ? xPDOTransport3::UNIQUE_KEY : xPDOTransport::UNIQUE_KEY       => ['pluginid', 'event'],
                ],
            ],
        ];
        $objects = [];
        foreach ($plugins as $name => $data) {
            /** @var modPlugin|modPlugin3 $plugin */
            $plugin = $this->modx->newObject(MODX3 ? modPlugin3::class : modPlugin::class);
            $plugin->fromArray(array_merge([
                'name'        => $name,
                'category'    => 0,
                'description' => @$data['description'],
                'plugincode'  => $this::_getContent($this->config['core'].'elements/plugins/'.$data['file'].'.php'),
                'static'      => !empty($this->config['static']['plugins']),
                'source'      => 1,
                'static_file' => 'core/components/'.$this->config['name_lower'].'/elements/plugins/'.$data['file'].'.php',
            ], $data), '', true, true);

            $events = [];
            if (!empty($data['events'])) {
                foreach ($data['events'] as $event_name => $event_data) {
                    /** @var modPluginEvent|modPluginEvent3 $event */
                    $event = $this->modx->newObject(MODX3 ? modPluginEvent3::class : modPluginEvent::class);
                    $event->fromArray(array_merge([
                        'event'       => $event_name,
                        'priority'    => 0,
                        'propertyset' => 0,
                    ], $event_data), '', true, true);
                    $events[] = $event;
                }
            }
            if (!empty($events)) {
                $plugin->addMany($events);
            }
            $objects[] = $plugin;
        }
        $this->category->addMany($objects);
        $this->modx->log(LOG_LEVEL_INFO, 'Packaged in '.count($objects).' Plugins');
    }

    protected function events()
    {
        $eventNames = include($this->config['elements'].'events.php');
        if (!is_array($eventNames)) {
            $this->modx->log(LOG_LEVEL_ERROR, 'Could not find events');
            return;
        }

        $attributes = [
            'vehicle_class'                                                                                      => EncryptedVehicle::class,
            'vehicle_package'                                                                                    => '',
            MODX3 ? xPDOTransport3::ABORT_INSTALL_ON_VEHICLE_FAIL : xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            MODX3 ? xPDOTransport3::PRESERVE_KEYS : xPDOTransport::PRESERVE_KEYS                                 => true,
            MODX3 ? xPDOTransport3::UPDATE_OBJECT : xPDOTransport::UPDATE_OBJECT                                 => !empty($this->config['update']['events']),
            MODX3 ? xPDOTransport3::UNIQUE_KEY : xPDOTransport::UNIQUE_KEY                                       => 'name',
        ];

        foreach ($eventNames as $eventName) {
            /** @var modEvent|modEvent3 $event */
            $event = $this->modx->newObject(MODX3 ? modEvent3::class : modEvent::class, [
                'service'   => 6, // 6 - какая-то магическая константа
                'groupname' => $this->config['name'],
            ]);
            $event->name = $eventName;
            $vehicle = $this->builder->createVehicle($event, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(LOG_LEVEL_INFO, 'Packaged in '.count($eventNames).' Events');
    }

    /**
     * @param $filename
     *
     * @return string
     */
    static public function _getContent($filename)
    {
        if (file_exists($filename)) {
            $file = trim(file_get_contents($filename));

            return preg_match('#<\?php(.*)#is', $file, $data)
                ? rtrim(rtrim(trim(@$data[1]), '?>'))
                : $file;
        }

        return '';
    }

    /**
     *  Install package
     */
    protected function install()
    {
        $signature = $this->builder->getSignature();
        $sig = explode('-', $signature);
        $versionSignature = explode('.', $sig[1]);

        /** @var modTransportPackage|modTransportPackage3 $package */
        if (!$package = $this->modx->getObject(MODX3 ? modTransportPackage3::class : 'transport.modTransportPackage',
            ['signature' => $signature])) {
            $package = $this->modx->newObject(MODX3 ? modTransportPackage3::class : 'transport.modTransportPackage');
            $package->set('signature', $signature);
            $package->fromArray([
                'created'       => date('Y-m-d h:i:s'),
                'updated'       => null,
                'state'         => 1,
                'workspace'     => 1,
                'provider'      => $this->config['modstore_id'],
                'source'        => $signature.'.transport.zip',
                'package_name'  => $this->config['name'],
                'version_major' => $versionSignature[0],
                'version_minor' => !empty($versionSignature[1]) ? $versionSignature[1] : 0,
                'version_patch' => !empty($versionSignature[2]) ? $versionSignature[2] : 0,
            ]);
            if (!empty($sig[2])) {
                $r = preg_split('#([0-9]+)#', $sig[2], -1, PREG_SPLIT_DELIM_CAPTURE);
                if (is_array($r) && !empty($r)) {
                    $package->set('release', $r[0]);
                    $package->set('release_index', (isset($r[1]) ? $r[1] : '0'));
                } else {
                    $package->set('release', $sig[2]);
                }
            }
            $package->save();
        }
        if ($package->install()) {
            $this->modx->runProcessor(MODX3 ? ClearCache3::class : 'system/clearcache');
        }
    }

    /**
     * @return modPackageBuilder
     */
    public function process()
    {
        $this->model();
        // $this->assets();

        // Add elements
        $elements = scandir($this->config['elements']);
        foreach ($elements as $element) {
            if (in_array($element[0], ['_', '.'])) {
                continue;
            }
            $name = preg_replace('#\.php$#', '', $element);
            if (method_exists($this, $name)) {
                $this->{$name}();
            }
        }
        // Create main vehicle
        $vehicle = $this->builder->createVehicle($this->category, $this->category_attributes);

        // Files resolvers
        $vehicle->resolve('file', [
            'source' => $this->config['core'],
            'target' => "return MODX_CORE_PATH . 'components/';",
        ]);
        $vehicle->resolve('file', [
            'source' => $this->config['assets'],
            'target' => "return MODX_ASSETS_PATH . 'components/';",
        ]);

        // Add resolvers into vehicle
        $resolvers = scandir($this->config['resolvers']);
        foreach ($resolvers as $resolver) {
            if (mb_strpos($resolver, '_') === 0 || in_array($resolver, ['.', '..'], true)) {
                continue;
            }
            if ($vehicle->resolve('php', ['source' => $this->config['resolvers'].$resolver])) {
                $this->modx->log(LOG_LEVEL_INFO, 'Added resolver '.preg_replace('#\.php$#', '', $resolver));
            } else {
                $this->modx->log(LOG_LEVEL_INFO, 'Could not add resolver "'.$resolver.'" to category.');
            }
        }

        $this->builder->putVehicle($vehicle);

        //encryption resolver
        $guid = md5(uniqid(mt_rand(), true));
        $this->builder->package->put(MODX3 ? new xPDOScriptVehicle3() : new xPDOScriptVehicle(), [
            'vehicle_class'   => MODX3 ? xPDOScriptVehicle3::class : xPDOScriptVehicle::class,
            'vehicle_package' => '',
            'namespace'       => 'yandexmarket2',
            'guid'            => $guid,
            'native_key'      => $guid,
            'object'          => [
                'source' => $this->config['helpers'].'encryption.php',
            ]
        ]);

        $this->builder->setPackageAttributes([
            'changelog' => file_get_contents($this->config['core'].'docs/changelog.txt'),
            'license'   => file_get_contents($this->config['core'].'docs/license.txt'),
            'readme'    => file_get_contents($this->config['core'].'docs/readme.txt'),
        ]);
        $this->modx->log(LOG_LEVEL_INFO, 'Added package attributes and setup options.');

        $this->modx->log(LOG_LEVEL_INFO, 'Packing up transport package zip...');
        $this->builder->pack();

        if (!empty($this->config['install'])) {
            $this->install();
        }

        return $this->builder;
    }

}

$modx = MODX3 ? new modX3() : new modX();
$install = new YandexMarket2Package($modx, $config);
$builder = $install->process();

// if (!empty($config['download'])) {
//     $name = $builder->getSignature() . '.transport.zip';
//     if ($content = file_get_contents(MODX_CORE_PATH . '/packages/' . $name)) {
//         header('Content-Description: File Transfer');
//         header('Content-Type: application/octet-stream');
//         header('Content-Disposition: attachment; filename=' . $name);
//         header('Content-Transfer-Encoding: binary');
//         header('Expires: 0');
//         header('Cache-Control: must-revalidate');
//         header('Pragma: public');
//         header('Content-Length: ' . strlen($content));
//         exit($content);
//     }
// }
