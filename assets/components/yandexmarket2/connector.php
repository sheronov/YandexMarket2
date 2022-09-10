<?php

if (file_exists(dirname(__DIR__, 3).'/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__DIR__, 3).'/config.core.php';
} elseif (file_exists(dirname(__DIR__, 4).'/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__DIR__, 4).'/config.core.php';
} elseif (file_exists(dirname(__DIR__, 5).'/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__DIR__, 5).'/config.core.php';
} else {
    die;
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH.'index.php';
/** @var MODX\Revolution\modX|modX $modx */
$modx->lexicon->load('yandexmarket2:default');

$isMODX3 = class_exists('MODX\Revolution\modX');
$corePath = $modx->getOption('yandexmarket2_core_path', null, $modx->getOption('core_path').'components/yandexmarket2/');
if (!$isMODX3) {
    $modx->addPackage('yandexmarket2', $corePath.'model/');
    require_once($corePath.'/vendor/autoload.php');
}

$modx->getRequest();

/** @var MODX\Revolution\modConnectorRequest|modConnectorRequest $request */
$request = $modx->request;

$_POST['register'] = 'yandexmarket2'; //for logging (uses in XmlPreview) (do not forget provide topic param)
$request->handleRequest($isMODX3 ? [
    'action'   => str_replace('/', '\\', 'YandexMarket/Processors/'.$_REQUEST['action']),
    'location' => ''
]: [
    'action' => mb_strtolower($_REQUEST['action']),
    'processors_path' => $corePath.'processors/',
    'location' => ''
]);
