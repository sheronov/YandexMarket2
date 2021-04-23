<?php

if (file_exists(dirname(__DIR__, 3).'/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__DIR__, 3).'/config.core.php';
} elseif (file_exists(dirname(__DIR__, 4).'/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__DIR__, 4).'/config.core.php';
} else {
    die;
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH.'index.php';
/** @var modX $modx */
$modx->lexicon->load('yandexmarket2:default');

// handle request
$corePath = $modx->getOption('yandexmarket2_core_path', null,
    $modx->getOption('core_path').'components/yandexmarket2/');
$modx->addPackage('yandexmarket2', $corePath.'model/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;

$request->handleRequest([
    'processors_path' => $corePath.'processors/',
    'location'        => '',
]);