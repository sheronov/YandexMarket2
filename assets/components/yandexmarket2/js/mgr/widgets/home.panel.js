YandexMarket2.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'yandexmarket2-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('yandexmarket2') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('yandexmarket2_items'),
                layout: 'anchor',
                items: [{
                    html: _('yandexmarket2_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'yandexmarket2-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    YandexMarket2.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(YandexMarket2.panel.Home, MODx.Panel);
Ext.reg('yandexmarket2-panel-home', YandexMarket2.panel.Home);
