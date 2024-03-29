<?php

if (class_exists('MODX\Revolution\modX')) {
    abstract class YandexMarket2HomeManagerControllerAbstract extends MODX\Revolution\modExtraManagerController { }
} else {
    abstract class YandexMarket2HomeManagerControllerAbstract extends modExtraManagerController { }
}

class YandexMarket2HomeManagerController extends YandexMarket2HomeManagerControllerAbstract
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
        $this->sentryDsn = $this->modx->getOption('yandexmarket2_sentry_dsn', null, '');
        $this->xmlLoaded = class_exists('XmlWriter');
        $this->isMODX3 = class_exists('MODX\Revolution\modX');
        // $this->lexicons = json_encode($this->modx->lexicon->fetch('yandexmarket2_', true));

        parent::initialize();
    }

    public function getLanguageTopics(): array
    {
        return ['yandexmarket2:vuetify'];
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

        $ym2ConfigJson = $this->modx->toJSON([
            'apiUrl'    => $this->connectorUrl,
            'modAuth'   => $this->modx->user->getUserToken($this->modx->context->key),
            'sentry'    => $this->sentryDsn,
            'xmlLoaded' => $this->xmlLoaded,
            'isMODX3'   => $this->isMODX3,
            'lang'      => $this->modx->lexicon->fetch('yandexmarket2_vuetify_', true)
        ]);
        $this->addHtml('<script type="text/javascript">window.ym2Config = '.$ym2ConfigJson.'</script>');
        $this->addJavascript($this->mgrAssetsUrl.'js/chunk-vendors.js');
        $this->addLastJavascript($this->mgrAssetsUrl.'js/app.js');
    }

}
