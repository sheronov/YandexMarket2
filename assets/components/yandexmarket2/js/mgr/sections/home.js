YandexMarket2.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'yandexmarket2-panel-home',
            renderTo: 'yandexmarket2-panel-home-div'
        }]
    });
    YandexMarket2.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(YandexMarket2.page.Home, MODx.Component);
Ext.reg('yandexmarket2-page-home', YandexMarket2.page.Home);