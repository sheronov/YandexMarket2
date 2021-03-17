<?php

if (!defined('MODX_CORE_PATH')) {
    $path = __DIR__;
    while (!file_exists($path.'/core/config/config.inc.php') && (strlen($path) > 1)) {
        $path = dirname($path);
    }
    define('MODX_CORE_PATH', $path.'/core/');
}

return [
    'name'        => 'YandexMarket2',
    'name_lower'  => 'yandexmarket2',
    'modstore_id' => 2,
    'version'     => '1.0.2',
    'release'     => 'beta',
    // Install package to site right after build
    'install'     => true,
    // Which elements should be updated on package upgrade
    'update'      => [
        'menus'     => true,
        'plugins'   => true,
        'settings'  => false,
        'templates' => false,
        'widgets'   => false,
    ],
    // Which elements should be static by default
    'static'      => [
        'plugins'  => false,
        'snippets' => false,
        'chunks'   => false,
    ],
    // Log settings
    'log_level'   => !empty($_REQUEST['download']) ? 0 : 3,
    'log_target'  => php_sapi_name() === 'cli' ? 'ECHO' : 'HTML',
    // Download transport.zip after build
    'download'    => !empty($_REQUEST['download']),
];