<?php

// namespace MODX\Components\YandexMarket2;

// use modX;

require __DIR__.'/../vendor/autoload.php';

class yandexmarket2
{
    /** @var modX $modx */
    public $modx;

    public $config = [];


    /**
     * @param  modX  $modx
     * @param  array  $config
     */
    public function __construct(modX $modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');
        $assetsUrl = $modx->getOption('yandexmarket2_assets_url', null,
            $modx->getOption('assets_url').'components/yandexmarket2/');

        $this->config = array_merge([
            'corePath'       => $corePath,
            'modelPath'      => $corePath.'model/',
            'processorsPath' => $corePath.'processors/',

            'connectorUrl' => $assetsUrl.'connector.php',
            'assetsUrl'    => $assetsUrl,
            'cssUrl'       => $assetsUrl.'css/',
            'jsUrl'        => $assetsUrl.'js/',
        ], $config);

        $this->modx->addPackage('yandexmarket2', $this->config['modelPath']);
        $this->modx->lexicon->load('yandexmarket2:default');
    }

}