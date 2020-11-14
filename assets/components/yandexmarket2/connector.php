<?php

// TODO: do not forget to remove it
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Cookie, Content-Type, Accept, modAuth, modCookie');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, POST, GET, PUT, DELETE');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    die;
}
if (isset($_SERVER['HTTP_MODCOOKIE'])) {
    $modCookie = array_map('trim', explode(';', $_SERVER['HTTP_MODCOOKIE']));
    foreach ($modCookie as $cookie) {
        [$key, $value] = explode('=', $cookie, 2);
        $_COOKIE[$key] = $value;
    }
}
// TODO: until here

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
/** @var yandexmarket2 $YandexMarket2 */
$YandexMarket2 = $modx->getService('YandexMarket2', 'YandexMarket2', MODX_CORE_PATH.'components/yandexmarket2/model/');
$modx->lexicon->load('yandexmarket2:default');

// handle request
$corePath = $modx->getOption('yandexmarket2_core_path', null,
    $modx->getOption('core_path').'components/yandexmarket2/');
$path = $modx->getOption('processorsPath', $YandexMarket2->config, $corePath.'processors/');

$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;

$request->handleRequest([
    'processors_path' => $path,
    'location'        => '',
]);