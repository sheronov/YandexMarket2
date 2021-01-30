<?php

namespace YandexMarket;

use modX;
use xPDO;

class YandexMarket
{
    protected $modx;
    protected $config = [];

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');
        $assetsUrl = $modx->getOption('yandexmarket2_assets_url', null,
            $modx->getOption('assets_url').'components/yandexmarket2/');

        $this->config = array_merge([
            'corePath'       => $corePath,
            'modelPath'      => $corePath.'model/orm/',
            'processorsPath' => $corePath.'processors/',
            'assetsUrl'      => $assetsUrl,
            'mgrAssetsUrl'   => $assetsUrl.'mgr/',

        ], $config);

        $this->modx->addPackage('yandexmarket2', $this->config['modelPath']);
        $this->modx->lexicon->load('yandexmarket2:default');
    }

    public static function debugInfo(xPDO $xpdo): ?array
    {
        if(!$xpdo->getOption('yandexmarket_debug_mode')) {
            return null;
        }
        return [
            'queries'   => $xpdo->executedQueries,
            'queryTime' => sprintf("%2.4f s", $xpdo->queryTime),
            'totalTime' => sprintf("%2.4f s", (microtime(true) - $xpdo->startTime)),
            'memory'    => number_format(memory_get_usage(true) / 1024, 0, ",", " ").' kb'
        ];
    }

}