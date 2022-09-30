<?php

use MODX\Revolution\modX;
use MODX\Revolution\Error\modError;
use YandexMarket\Model\YmPricelist;
use YandexMarket\Models\Pricelist;
use YandexMarket\QueryService;
use YandexMarket\Service;
use YandexMarket\Xml\FileGenerator;

define('MODX_API_MODE', true);
/** @noinspection PhpIncludeInspection */

if (file_exists(dirname(__FILE__, 5).'/index.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__FILE__, 5).'/index.php';
} elseif (file_exists(dirname(__FILE__, 6).'/index.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(__FILE__, 6).'/index.php';
} else {
    echo "Could not load MODX!\n";
    return;
}

/** @var modX|\modX $modx */
$isMODX3 = class_exists('MODX\Revolution\modX');
$modx->getService('error', $isMODX3 ? modError::class : 'error.modError');
$modx->setLogLevel(filter_var($modx->getOption('yandexmarket2_debug_mode', null, false))
    ? $modx::LOG_LEVEL_DEBUG : $modx::LOG_LEVEL_WARN);

$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

$corePath = $modx->getOption('yandexmarket2_core_path', null,
    $modx->getOption('core_path').'components/yandexmarket2/');

if (!$isMODX3) {
    require_once(dirname(__FILE__, 2).'/vendor/autoload.php');
    $modx->addPackage('yandexmarket2', $corePath.'model/');
}

$q = $modx->newQuery(Service::isMODX3() ? YmPricelist::class : \YmPricelist::class)
    ->where(['active' => 1, 'generate_mode:!=' => Pricelist::GENERATE_MODE_MANUALLY]);

if ($pricelistIds = $argv[1] ?? '') {
    $q->where(['id:IN' => explode(',', $pricelistIds)]);
}
if (!$modx->getCount($q->getClass(), $q)) {
    echo "Not found pricelists to generate\n";
}
foreach ($modx->getIterator($q->getClass(), $q) as $ymPricelist) {
    $pricelist = new Pricelist($modx, $ymPricelist);

    if (!$pricelist->generated_on) {
        $pricelist->need_generate = true;
    } elseif ($minutes = $pricelist->generate_interval) {
        $lastDate = (new DateTimeImmutable())->sub(DateInterval::createFromDateString($minutes.' minutes'));
        $generatedDate = $pricelist->generated_on instanceof DateTimeInterface
            ? $pricelist->generated_on
            : DateTime::createFromFormat('Y-m-d H:i:s', $pricelist->generated_on);
        if ($lastDate > $generatedDate) {
            $pricelist->need_generate = true;
        }
    }
    if (!$pricelist->need_generate) {
        echo "Skipped pricelist id = {$pricelist->id}\n";
        continue;
    }
    echo "Writing pricelist id = {$pricelist->id}\n";

    $generator = new FileGenerator(new QueryService($pricelist, $modx));
    try {
        $generator->makeFile();
        echo "Succeed generated file for pricelist id = {$pricelist->id} to {$pricelist->getFilePath(true)}\n";
    } catch (Exception $e) {
        echo "Error with pricelist id = {$pricelist->id}: {$e->getMessage()}\n";
    }
}
