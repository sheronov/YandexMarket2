<?php

/**
 * @var MODX\Revolution\modX $modx
 * @var array{name: string, path: string, assets_path: string} $namespace
 */

require_once MODX_CORE_PATH . 'components/yandexmarket2/vendor/autoload.php';

// Load the classes
$modx->addPackage('YandexMarket\Model', $namespace['path'] . 'src/', null, 'YandexMarket\\');
