<?php

/**
 * The home manager controller for YandexMarket2.
 */
class YandexMarket2HomeManagerController extends modExtraManagerController
{
    /** @var yandexmarket2 $YandexMarket2 */
    public $YandexMarket2;

    /**
     *
     */
    public function initialize()
    {
        $this->YandexMarket2 = $this->modx->getService('YandexMarket2', 'YandexMarket2',
            $this->modx->getOption('yandexmarket2_core_path', null,
                $this->modx->getOption('core_path').'components/yandexmarket2/').'/model/');

        parent::initialize();
    }

    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['yandexmarket2:default'];
    }

    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }

    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('yandexmarket2');
    }

    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->YandexMarket2->config['mgrAssetsUrl'].'css/chunk-vendors.css');
        $this->addCss($this->YandexMarket2->config['mgrAssetsUrl'].'css/app.css');

        $this->addHtml('<script type="text/javascript">
        window.ym2Config = {
            // config: '.json_encode($this->YandexMarket2->config).',
            apiUrl: "'.$this->YandexMarket2->config['connectorUrl'].'",
            modAuth: "'.$this->modx->user->getUserToken($this->modx->context->key).'",
            lang: '.json_encode($this->modx->lexicon->fetch('yandexmarket2_', true)).'
        }
        </script>');

        $this->addJavascript($this->YandexMarket2->config['mgrAssetsUrl'].'js/chunk-vendors.js');
        $this->addLastJavascript($this->YandexMarket2->config['mgrAssetsUrl'].'js/app.js');
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="yandexmarket2-app"></div>';

        return '';
    }
}