msYaMarket.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'msyamarket-panel-home',
            renderTo: 'msyamarket-panel-home-div'
        }]
    });
    msYaMarket.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(msYaMarket.page.Home, MODx.Component);
Ext.reg('msyamarket-page-home', msYaMarket.page.Home);