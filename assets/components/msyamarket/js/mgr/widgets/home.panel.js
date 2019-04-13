msYaMarket.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'msyamarket-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('msyamarket') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('msyamarket_items'),
                layout: 'anchor',
                items: [{
                    html: _('msyamarket_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'msyamarket-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    msYaMarket.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(msYaMarket.panel.Home, MODx.Panel);
Ext.reg('msyamarket-panel-home', msYaMarket.panel.Home);
