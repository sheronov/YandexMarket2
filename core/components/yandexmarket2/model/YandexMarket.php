<?php

namespace YandexMarket;

use modX;

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

}