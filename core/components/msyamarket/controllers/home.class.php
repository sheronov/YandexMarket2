<?php

/**
 * The home manager controller for msYaMarket.
 *
 */
class msYaMarketHomeManagerController extends modExtraManagerController
{
    /** @var msYaMarket $msYaMarket */
    public $msYaMarket;


    /**
     *
     */
    public function initialize()
    {
        $this->msYaMarket = $this->modx->getService('msYaMarket', 'msYaMarket',
            $this->modx->getOption('msyamarket_core_path', null,
                $this->modx->getOption('core_path') . 'components/msyamarket/') . '/model/');

        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['msyamarket:default'];
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
        return $this->modx->lexicon('msyamarket');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->msYaMarket->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->msYaMarket->config['jsUrl'] . 'mgr/msyamarket.js');
        $this->addJavascript($this->msYaMarket->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->msYaMarket->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->msYaMarket->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->msYaMarket->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->msYaMarket->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->msYaMarket->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        msYaMarket.config = ' . json_encode($this->msYaMarket->config) . ';
        msYaMarket.config.connector_url = "' . $this->msYaMarket->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "msyamarket-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="msyamarket-panel-home-div"></div>';

        return '';
    }
}