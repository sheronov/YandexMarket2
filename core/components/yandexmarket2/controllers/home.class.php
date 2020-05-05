<?php

/**
 * The home manager controller for YandexMarket2.
 *
 */
class YandexMarket2HomeManagerController extends modExtraManagerController
{
    /** @var YandexMarket2 $YandexMarket2 */
    public $YandexMarket2;


    /**
     *
     */
    public function initialize()
    {
        $this->YandexMarket2 = $this->modx->getService('YandexMarket2', 'YandexMarket2',
            $this->modx->getOption('yandexmarket2_core_path', null,
                $this->modx->getOption('core_path') . 'components/yandexmarket2/') . '/model/');

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
        $this->addCss($this->YandexMarket2->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->YandexMarket2->config['jsUrl'] . 'mgr/yandexmarket2.js');
        $this->addJavascript($this->YandexMarket2->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->YandexMarket2->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->YandexMarket2->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->YandexMarket2->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->YandexMarket2->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->YandexMarket2->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        YandexMarket2.config = ' . json_encode($this->YandexMarket2->config) . ';
        YandexMarket2.config.connector_url = "' . $this->YandexMarket2->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "yandexmarket2-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="yandexmarket2-panel-home-div"></div>';

        return '';
    }
}