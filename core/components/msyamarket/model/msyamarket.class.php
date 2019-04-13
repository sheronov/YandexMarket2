<?php

class msYaMarket
{
    /** @var modX $modx */
    public $modx;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX $modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = $modx->getOption('msyamarket_core_path', null,
            $modx->getOption('core_path') . 'components/msyamarket/');
        $assetsUrl = $modx->getOption('msyamarket_assets_url', null,
            $modx->getOption('assets_url') . 'components/msyamarket/');

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('msyamarket', $this->config['modelPath']);
        $this->modx->lexicon->load('msyamarket:default');
    }

}