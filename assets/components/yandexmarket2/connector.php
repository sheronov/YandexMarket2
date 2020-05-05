<?php
if (file_exists(dirname(dirname(dirname(__DIR__))) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(__DIR__))) . '/config.core.php';
} else {
    require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var YandexMarket2 $YandexMarket2 */
$YandexMarket2 = $modx->getService('YandexMarket2', 'YandexMarket2', MODX_CORE_PATH . 'components/yandexmarket2/model/');
$modx->lexicon->load('yandexmarket2:default');

// handle request
$corePath = $modx->getOption('yandexmarket2_core_path', null, $modx->getOption('core_path') . 'components/yandexmarket2/');
$path = $modx->getOption('processorsPath', $YandexMarket2->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);