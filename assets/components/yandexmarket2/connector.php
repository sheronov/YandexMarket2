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
/** @var modX $modx */
$modx->lexicon->load('yandexmarket2:default');

$modx->getRequest();

/** @var MODX\Revolution\modConnectorRequest $request */
$request = $modx->request;

$_POST['register'] = 'yandexmarket2'; //for logging (uses in XmlPreview) (do not forget provide topic param)
$request->handleRequest([
    'action'   => str_replace('/', '\\', 'YandexMarket/Processors/'.$_REQUEST['action']),
    'location' => ''
]);
