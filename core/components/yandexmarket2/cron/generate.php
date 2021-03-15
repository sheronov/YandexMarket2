<?php

use YandexMarket\Models\Pricelist;
use YandexMarket\Xml\Generate;

define('MODX_API_MODE', true);
/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 2).'/vendor/autoload.php');

if (file_exists(dirname(__FILE__, 5).'/index.php')) {
    require_once dirname(__FILE__, 5).'/index.php';
} elseif (file_exists(dirname(__FILE__, 6).'/index.php')) {
    require_once dirname(__FILE__, 6).'/index.php';
} else {
    echo 'Could not load MODX!';
    return;
}
/** @var modX $modx */

$corePath = $modx->getOption('yandexmarket2_core_path', null,
    $modx->getOption('core_path').'components/yandexmarket2/');
$modx->addPackage('yandexmarket2', $corePath.'model/');

$q = $modx->newQuery('ymPricelist');
$q->where(['active' => 1]);
if ($pricelistIds = explode(',', $argv[1] ?? '')) {
    $q->where(['id:IN' => $pricelistIds]);
}
foreach ($modx->getIterator('ymPricelist', $q) as $ymPricelist) {
    $pricelist = new Pricelist($modx, $ymPricelist);
    $generator = new Generate($pricelist, $this->modx);
    try {
        $generator->makeFile();
        echo "Succeed generated file for pricelist id = {$pricelist->id} to {$pricelist->getFilePath(true)}";
    } catch (Exception $e) {
        echo "Error with pricelist id = {$pricelist->id}: {$e->getMessage()}";
    }
}

