<?php

class YandexMarket2Package
{
    /** @var modX $modx */
    public $modx;
    /** @var array $config */
    public $config = [];

    /** @var modPackageBuilder $builder */
    public $builder;
    /** @var modCategory $vehicle */
    public $category;
    public $category_attributes = [];

    protected $_idx = 1;

    /**
     * YandexMarket2Package constructor.
     *
     * @param $core_path
     * @param  array  $config
     *
     * @noinspection  PhpIncludeInspection
     */
    public function __construct($core_path, array $config = [])
    {
        require_once $core_path.'model/modx/modx.class.php';
        require_once $core_path.'model/modx/transport/modpackagebuilder.class.php';
        require_once $core_path.'xpdo/xpdo.class.php';
        require_once $core_path.'xpdo/transport/xpdotransport.class.php';
        require_once $core_path.'xpdo/transport/xpdovehicle.class.php';
        require_once $core_path.'xpdo/transport/xpdofilevehicle.class.php';
        require_once $core_path.'xpdo/transport/xpdoscriptvehicle.class.php';
        require_once $core_path.'xpdo/transport/xpdoobjectvehicle.class.php';
        require_once __DIR__.'/helpers/encryptedvehicle.class.php';

        /** @var modX $modx */
        $this->modx = new modX();
        $this->modx->initialize('mgr');
        $this->modx->getService('error', 'error.modError');

        $root = dirname(__FILE__, 2).'/';
        $assets = $root.'assets/components/'.$config['name_lower'].'/';
        $core = $root.'core/components/'.$config['name_lower'].'/';

        $this->config = array_merge([
            'log_level'  => modX::LOG_LEVEL_INFO,
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

        $this->builder = new modPackageBuilder($this->modx);
        $this->builder->createPackage($this->config['name_lower'], $this->config['version'], $this->config['release']);

        $this->addEncryptionHelpers();
        // $this->addSchemeFile();

        $this->builder->registerNamespace($this->config['name_lower'], false, true,
            '{core_path}components/'.$this->config['name_lower'].'/');

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Created Transport Package and Namespace.');

        $this->category = $this->modx->newObject('modCategory');
        $this->category->set('category', $this->config['name']);
        $this->category_attributes = [
            'vehicle_class'                              => EncryptedVehicle::class,
            xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            xPDOTransport::UNIQUE_KEY                    => 'category',
            xPDOTransport::PRESERVE_KEYS                 => false,
            xPDOTransport::UPDATE_OBJECT                 => true,
            xPDOTransport::RELATED_OBJECTS               => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES     => [],
        ];
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Created main Category.');
    }

    protected function addEncryptionHelpers()
    {
        // /** @noinspection PhpIncludeInspection */
        // require_once $this->config['core'].'model/encryptedvehicle.class.php';
        $this->builder->package->put(new xPDOFileVehicle(), [
            'vehicle_class' => xPDOFileVehicle::class,
            'namespace'     => 'yandexmarket2',
            xPDOTransport::UNINSTALL_FILES => false,
            'object'        => [
                'source' => $this->config['helpers'].'encryptedvehicle.class.php',
                'target' => "return MODX_CORE_PATH . 'components/yandexmarket2/';",
            ]
        ]);

        $this->builder->package->put(new xPDOScriptVehicle(), [
            'vehicle_class' => xPDOScriptVehicle::class,
            'namespace'     => 'yandexmarket2',
            'object'        => [
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
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Initialized encryption');
    }

    protected function defineEncodeKey(): string
    {
        $key = '';
        /** @var modTransportProvider $provider */
        if ($provider = $this->modx->getObject('transport.modTransportProvider', $this->config['modstore_id'])) {
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

            /** @var modRest $rest */
            $rest = $this->modx->getService('modRest', 'rest.modRest', '', [
                'baseUrl'        => rtrim($provider->get('service_url'), '/'),
                'suppressSuffix' => true,
                'timeout'        => 10,
                'connectTimeout' => 10,
                'format'         => 'xml',
            ]);

            $response = $rest->post('package/encode', $params);
            if ($response->responseError) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, $response->responseError);
            } else {
                $data = $response->process();
                if (!empty($data['key'])) {
                    $key = $data['key'];
                    $this->modx->log(xPDO::LOG_LEVEL_INFO, 'Received key from modstore: '.$key);
                } elseif (!empty($data->message)) {
                    $this->modx->log(xPDO::LOG_LEVEL_ERROR, $data->message);
                }
            }
        }

        if (!empty($key)) {
            define('PKG_ENCODE_KEY', $key);
        } else {
            $this->modx->log(xPDO::LOG_LEVEL_INFO, 'Problem with getting encode key from modstore');
        }
        return $key;
    }

    /**
     * Update the model
     */
    protected function model()
    {
        $model_file = $this->config['core'].'model/schema/'.$this->config['name_lower'].'.mysql.schema.xml';
        $isEmpty = file_get_contents($model_file);
        if (!file_exists($model_file) || empty($isEmpty)) {
            return;
        }
        /** @var xPDOCacheManager $cache */
        if ($cache = $this->modx->getCacheManager()) {
            $cache->deleteTree(
                $this->config['core'].'model/'.$this->config['name_lower'].'/mysql',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
        }

        /** @var xPDOManager $manager */
        $manager = $this->modx->getManager();
        $generator = $manager->getGenerator();
        $generator->parseSchema(
            $this->config['core'].'model/schema/'.$this->config['name_lower'].'.mysql.schema.xml',
            $this->config['core'].'model/'
        );
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Model updated');
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
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Trying to install or update nodejs dependencies');
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
                $this->modx->log(xPDO::LOG_LEVEL_INFO, implode("\n", array_map('trim', $output)));
            }
        }
        if (file_exists($this->config['build'].'gulpfile.js')) {
            $output = shell_exec('cd '.$this->config['build'].' && gulp default 2>&1');
            $this->modx->log(xPDO::LOG_LEVEL_INFO, 'Compile scripts and styles '.trim($output));
        }
    }

    /**
     * Add settings
     */
    protected function settings()
    {
        /** @noinspection PhpIncludeInspection */
        $settings = include($this->config['elements'].'settings.php');
        if (!is_array($settings)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in System Settings');
            return;
        }
        $attributes = [
            'vehicle_class'                              => EncryptedVehicle::class,
            xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            xPDOTransport::UNIQUE_KEY                    => 'key',
            xPDOTransport::PRESERVE_KEYS                 => true,
            xPDOTransport::UPDATE_OBJECT                 => !empty($this->config['update']['settings']),
            xPDOTransport::RELATED_OBJECTS               => false,
        ];
        foreach ($settings as $name => $data) {
            /** @var modSystemSetting $setting */
            $setting = $this->modx->newObject('modSystemSetting');
            $setting->fromArray(array_merge([
                'key'       => $this->config['name_lower'].'_'.$name,
                'namespace' => $this->config['name_lower'],
            ], $data), '', true, true);
            $vehicle = $this->builder->createVehicle($setting, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($settings).' System Settings');
    }

    /**
     * Add menus
     */
    protected function menus()
    {
        /** @noinspection PhpIncludeInspection */
        $menus = include($this->config['elements'].'menus.php');
        if (!is_array($menus)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Menus');
            return;
        }
        $attributes = [
            'vehicle_class'                              => EncryptedVehicle::class,
            xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            xPDOTransport::PRESERVE_KEYS                 => true,
            xPDOTransport::UPDATE_OBJECT                 => !empty($this->config['update']['menus']),
            xPDOTransport::UNIQUE_KEY                    => 'text',
            xPDOTransport::RELATED_OBJECTS               => true,
        ];

        foreach ($menus as $name => $data) {
            /** @var modMenu $menu */
            $menu = $this->modx->newObject('modMenu');
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

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($menus).' Menus');
    }

    /**
     * Add Dashboard Widgets
     */
    protected function widgets()
    {
        /** @noinspection PhpIncludeInspection */
        $widgets = include($this->config['elements'].'widgets.php');
        if (!is_array($widgets)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Dashboard Widgets');

            return;
        }
        $attributes = [
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['widgets']),
            xPDOTransport::UNIQUE_KEY    => 'name',
        ];
        foreach ($widgets as $name => $data) {
            /** @var modDashboardWidget $widget */
            $widget = $this->modx->newObject('modDashboardWidget');
            $widget->fromArray(array_merge([
                'name'      => $name,
                'namespace' => 'core',
                'lexicon'   => 'core:dashboards',
            ], $data), '', true, true);
            $vehicle = $this->builder->createVehicle($widget, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($widgets).' Dashboard Widgets');
    }

    /**
     * Add resources
     */
    protected function resources()
    {
        /** @noinspection PhpIncludeInspection */
        $resources = include($this->config['elements'].'resources.php');
        if (!is_array($resources)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Resources');

            return;
        }
        $attributes = [
            xPDOTransport::UNIQUE_KEY      => 'id',
            xPDOTransport::PRESERVE_KEYS   => true,
            xPDOTransport::UPDATE_OBJECT   => !empty($this->config['update']['resources']),
            xPDOTransport::RELATED_OBJECTS => false,
        ];
        $objects = [];
        foreach ($resources as $context => $items) {
            $menuindex = 0;
            foreach ($items as $alias => $item) {
                if (!isset($item['id'])) {
                    $item['id'] = $this->_idx++;
                }
                $item['alias'] = $alias;
                $item['context_key'] = $context;
                $item['menuindex'] = $menuindex++;
                $addResources = $this->_addResource($item, $alias);
                foreach ($addResources as $addResource) {
                    $objects[] = $addResource;
                }
            }
        }

        /** @var modResource $resource */
        foreach ($objects as $resource) {
            $vehicle = $this->builder->createVehicle($resource, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($objects).' Resources');
    }

    /**
     * Add plugins
     */
    protected function plugins()
    {
        /** @noinspection PhpIncludeInspection */
        $plugins = include($this->config['elements'].'plugins.php');
        if (!is_array($plugins)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Plugins');

            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = [
            'vehicle_class'                              => EncryptedVehicle::class,
            xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            xPDOTransport::UNIQUE_KEY                    => 'name',
            xPDOTransport::PRESERVE_KEYS                 => false,
            xPDOTransport::UPDATE_OBJECT                 => !empty($this->config['update']['plugins']),
            xPDOTransport::RELATED_OBJECTS               => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES     => [
                'PluginEvents' => [
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY    => ['pluginid', 'event'],
                ],
            ],
        ];
        $objects = [];
        foreach ($plugins as $name => $data) {
            /** @var modPlugin $plugin */
            $plugin = $this->modx->newObject('modPlugin');
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
                    /** @var modPluginEvent $event */
                    $event = $this->modx->newObject('modPluginEvent');
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
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($objects).' Plugins');
    }

    protected function events()
    {
        /** @noinspection PhpIncludeInspection */
        $eventNames = include($this->config['elements'].'events.php');
        if (!is_array($eventNames)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not find events');
            return;
        }

        $attributes = [
            'vehicle_class'                              => EncryptedVehicle::class,
            xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL => true,
            xPDOTransport::PRESERVE_KEYS                 => true,
            xPDOTransport::UPDATE_OBJECT                 => !empty($this->config['update']['events']),
            xPDOTransport::UNIQUE_KEY                    => 'name',
        ];

        foreach ($eventNames as $eventName) {
            /** @var modEvent $event */
            $event = $this->modx->newObject('modEvent', [
                'service'   => 6, //какая-то магическая константа
                'groupname' => $this->config['name'],
            ]);
            $event->name = $eventName;
            $vehicle = $this->builder->createVehicle($event, $attributes);
            $this->builder->putVehicle($vehicle);
        }
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($eventNames).' Events');
    }

    /**
     * Add snippets
     */
    protected function snippets()
    {
        /** @noinspection PhpIncludeInspection */
        $snippets = include($this->config['elements'].'snippets.php');
        if (!is_array($snippets)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Snippets');

            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['snippets']),
            xPDOTransport::UNIQUE_KEY    => 'name',
        ];
        $objects = [];
        foreach ($snippets as $name => $data) {
            /** @var modSnippet[] $objects */
            $objects[$name] = $this->modx->newObject('modSnippet');
            $objects[$name]->fromArray(array_merge([
                'id'          => 0,
                'name'        => $name,
                'description' => @$data['description'],
                'snippet'     => $this::_getContent($this->config['core'].'elements/snippets/'.$data['file'].'.php'),
                'static'      => !empty($this->config['static']['snippets']),
                'source'      => 1,
                'static_file' => 'core/components/'.$this->config['name_lower'].'/elements/snippets/'.$data['file'].'.php',
            ], $data), '', true, true);
            $properties = [];
            foreach (@$data['properties'] as $k => $v) {
                $properties[] = array_merge([
                    'name'    => $k,
                    'desc'    => $this->config['name_lower'].'_prop_'.$k,
                    'lexicon' => $this->config['name_lower'].':properties',
                ], $v);
            }
            $objects[$name]->setProperties($properties);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($objects).' Snippets');
    }

    /**
     * Add chunks
     */
    protected function chunks()
    {
        /** @noinspection PhpIncludeInspection */
        $chunks = include($this->config['elements'].'chunks.php');
        if (!is_array($chunks)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Chunks');

            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Chunks'] = [
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => !empty($this->config['update']['chunks']),
            xPDOTransport::UNIQUE_KEY    => 'name',
        ];
        $objects = [];
        foreach ($chunks as $name => $data) {
            /** @var modChunk[] $objects */
            $objects[$name] = $this->modx->newObject('modChunk');
            $objects[$name]->fromArray(array_merge([
                'id'          => 0,
                'name'        => $name,
                'description' => @$data['description'],
                'snippet'     => $this::_getContent($this->config['core'].'elements/chunks/'.$data['file'].'.tpl'),
                'static'      => !empty($this->config['static']['chunks']),
                'source'      => 1,
                'static_file' => 'core/components/'.$this->config['name_lower'].'/elements/chunks/'.$data['file'].'.tpl',
            ], $data), '', true, true);
            $objects[$name]->setProperties(@$data['properties']);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($objects).' Chunks');
    }

    /**
     * Add templates
     */
    protected function templates()
    {
        /** @noinspection PhpIncludeInspection */
        $templates = include($this->config['elements'].'templates.php');
        if (!is_array($templates)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not package in Templates');

            return;
        }
        $this->category_attributes[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Templates'] = [
            xPDOTransport::UNIQUE_KEY      => 'templatename',
            xPDOTransport::PRESERVE_KEYS   => false,
            xPDOTransport::UPDATE_OBJECT   => !empty($this->config['update']['templates']),
            xPDOTransport::RELATED_OBJECTS => false,
        ];
        $objects = [];
        foreach ($templates as $name => $data) {
            /** @var modTemplate[] $objects */
            $objects[$name] = $this->modx->newObject('modTemplate');
            $objects[$name]->fromArray(array_merge([
                'templatename' => $name,
                'description'  => $data['description'],
                'content'      => $this::_getContent($this->config['core'].'elements/templates/'.$data['file'].'.tpl'),
                'static'       => !empty($this->config['static']['templates']),
                'source'       => 1,
                'static_file'  => 'core/components/'.$this->config['name_lower'].'/elements/templates/'.$data['file'].'.tpl',
            ], $data), '', true, true);
        }
        $this->category->addMany($objects);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packaged in '.count($objects).' Templates');
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
     * @param  array  $data
     * @param  string  $uri
     * @param  int  $parent
     *
     * @return array
     */
    protected function _addResource(array $data, $uri, $parent = 0)
    {
        $file = $data['context_key'].'/'.$uri;
        /** @var modResource $resource */
        $resource = $this->modx->newObject('modResource');
        $resource->fromArray(array_merge([
            'parent'       => $parent,
            'published'    => true,
            'deleted'      => false,
            'hidemenu'     => false,
            'createdon'    => time(),
            'template'     => 1,
            'isfolder'     => !empty($data['isfolder']) || !empty($data['resources']),
            'uri'          => $uri,
            'uri_override' => false,
            'richtext'     => false,
            'searchable'   => true,
            'content'      => $this::_getContent($this->config['core'].'elements/resources/'.$file.'.tpl'),
        ], $data), '', true, true);

        if (!empty($data['groups'])) {
            foreach ($data['groups'] as $group) {
                $resource->joinGroup($group);
            }
        }
        $resources = [$resource];

        if (!empty($data['resources'])) {
            $menuindex = 0;
            foreach ($data['resources'] as $alias => $item) {
                if (!isset($item['id'])) {
                    $item['id'] = $this->_idx++;
                }
                $item['alias'] = $alias;
                $item['context_key'] = $data['context_key'];
                $item['menuindex'] = $menuindex++;
                $addedResources = $this->_addResource($item, $uri.'/'.$alias, $data['id']);
                foreach ($addedResources as $addedResource) {
                    $resources[] = $addedResource;
                }
            }
        }

        return $resources;
    }

    /**
     *  Install package
     */
    protected function install()
    {
        $signature = $this->builder->getSignature();
        $sig = explode('-', $signature);
        $versionSignature = explode('.', $sig[1]);

        /** @var modTransportPackage $package */
        if (!$package = $this->modx->getObject('transport.modTransportPackage', ['signature' => $signature])) {
            /** @var modTransportPackage $package */
            $package = $this->modx->newObject('transport.modTransportPackage');
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
            $this->modx->runProcessor('system/clearcache');
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
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Added resolver '.preg_replace('#\.php$#', '', $resolver));
            } else {
                $this->modx->log(modX::LOG_LEVEL_INFO, 'Could not add resolver "'.$resolver.'" to category.');
            }
        }

        $this->builder->putVehicle($vehicle);

        //encryption resolver
        $guid = md5(uniqid(rand(), true));
        $this->builder->package->put(new xPDOScriptVehicle(), [
            'vehicle_class' => xPDOScriptVehicle::class,
            'namespace'     => 'yandexmarket2',
            'guid'          => $guid,
            'native_key'    => $guid,
            'object'        => [
                'source' => $this->config['helpers'].'encryption.php',
            ]
        ]);

        $this->builder->setPackageAttributes([
            'changelog' => file_get_contents($this->config['core'].'docs/changelog.txt'),
            'license'   => file_get_contents($this->config['core'].'docs/license.txt'),
            'readme'    => file_get_contents($this->config['core'].'docs/readme.txt'),
        ]);
        $this->modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');

        $this->modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
        $this->builder->pack();

        if (!empty($this->config['install'])) {
            $this->install();
        }

        return $this->builder;
    }

}

/** @var array $config */
if (!file_exists(__DIR__.'/config.inc.php')) {
    exit('Could not load MODX config. Please specify correct MODX_CORE_PATH constant in config file!');
}
$config = require(__DIR__.'/config.inc.php');
$install = new YandexMarket2Package(MODX_CORE_PATH, $config);
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
