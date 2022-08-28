<?php

use MODX\Revolution\Error\modError;
use YandexMarket\Model\YmPricelist;
use YandexMarket\Models\Pricelist;
use YandexMarket\QueryService;
use YandexMarket\Xml\FileGenerator;

define('MODX_API_MODE', true);
/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 2).'/vendor/autoload.php');

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
/** @var modX $modx */
// Включаем обработку ошибок
$modx->getService('error', modError::class);
$modx->setLogLevel($modx->getOption('yandexmarket2_debug_mode', null,
    false) ? modX::LOG_LEVEL_INFO : modX::LOG_LEVEL_WARN);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

$corePath = $modx->getOption('yandexmarket2_core_path', null,
    $modx->getOption('core_path').'components/yandexmarket2/');
// $modx->addPackage('yandexmarket2', $corePath.'model/');

$q = $modx->newQuery(YmPricelist::class);
$q->where(['active' => 1, 'generate_mode:!=' => Pricelist::GENERATE_MODE_MANUALLY]);
if ($pricelistIds = $argv[1] ?? '') {
    $q->where(['id:IN' => explode(',', $pricelistIds)]);
}
if (!$modx->getCount(YmPricelist::class, $q)) {
    echo "Not found pricelists to generate\n";
}
foreach ($modx->getIterator(YmPricelist::class, $q) as $ymPricelist) {
    $pricelist = new Pricelist($modx, $ymPricelist);

    if (!$pricelist->generated_on) {
        $pricelist->need_generate = true;
    } elseif ($minutes = $pricelist->generate_interval) {
        $lastDate = (new DateTimeImmutable())->sub(DateInterval::createFromDateString($minutes.' minutes'));
        $generatedDate = $pricelist->generated_on instanceof DateTimeImmutable
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

    $generator = new FileGenerator(new QueryService($pricelist, $modx));
    try {
        $generator->makeFile();
        echo "Succeed generated file for pricelist id = {$pricelist->id} to {$pricelist->getFilePath(true)}\n";
    } catch (Exception $e) {
        echo "Error with pricelist id = {$pricelist->id}: {$e->getMessage()}\n";
    }
}
