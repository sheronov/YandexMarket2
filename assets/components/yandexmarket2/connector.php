<?php
if (file_exists(dirname(__DIR__, 3). '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__DIR__, 3). '/config.core.php';
} else {
    require_once dirname(__DIR__, 4). '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var yandexmarket2 $YandexMarket2 */
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