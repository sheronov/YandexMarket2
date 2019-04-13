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
/** @var msYaMarket $msYaMarket */
$msYaMarket = $modx->getService('msYaMarket', 'msYaMarket', MODX_CORE_PATH . 'components/msyamarket/model/');
$modx->lexicon->load('msyamarket:default');

// handle request
$corePath = $modx->getOption('msyamarket_core_path', null, $modx->getOption('core_path') . 'components/msyamarket/');
$path = $modx->getOption('processorsPath', $msYaMarket->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);