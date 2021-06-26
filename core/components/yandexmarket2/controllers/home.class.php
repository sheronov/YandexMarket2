<?php

/**
 * The home manager controller for YandexMarket2.
 */
class YandexMarket2HomeManagerController extends modExtraManagerController
{

    protected $connectorUrl;
    protected $mgrAssetsUrl;
    // protected $lexicons;
    protected $xmlLoaded;
    protected $sentryDsn;

    public function initialize()
    {
        $assetsUrl = $this->modx->getOption('yandexmarket2_assets_url', null,
            $this->modx->getOption('assets_url').'components/yandexmarket2/');
        $this->connectorUrl = $assetsUrl.'connector.php';
        $this->mgrAssetsUrl = $assetsUrl.'mgr/';
        $this->xmlLoaded = class_exists('XmlWriter');
        $this->sentryDsn = $this->modx->getOption('yandexmarket2_sentry_dsn', null,'');
        // $this->lexicons = json_encode($this->modx->lexicon->fetch('yandexmarket2_', true));

        parent::initialize();
    }

    public function getLanguageTopics(): array
    {
        return ['yandexmarket2:default'];
    }

    public function getPageTitle(): string
    {
        return $this->modx->lexicon('yandexmarket2') ?? 'YandexMarket2';
    }

    public function getTemplateFile(): string
    {
        $this->content .= '<div id="yandexmarket-app"></div>';
        return '';
    }

    public function loadCustomCssJs()
    {
        $this->addCss($this->mgrAssetsUrl.'css/chunk-vendors.css');
        $this->addCss($this->mgrAssetsUrl.'css/app.css');

        $this->addHtml("<script type=\"text/javascript\">
        window.ym2Config = {
            apiUrl: \"{$this->connectorUrl}\",
            modAuth: \"{$this->modx->user->getUserToken($this->modx->context->key)}\",
            sentry: \"{$this->sentryDsn}\",
            xmlLoaded: {$this->xmlLoaded}, 
            lang: {}
        }
        </script>");
        //  lang: {$this->lexicons} // TODO: добавить лексиконы для фронта

        $this->addJavascript($this->mgrAssetsUrl.'js/chunk-vendors.js');
        $this->addLastJavascript($this->mgrAssetsUrl.'js/app.js');
    }

}