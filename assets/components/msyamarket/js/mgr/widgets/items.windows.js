msYaMarket.window.CreateItem = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msyamarket-item-window-create';
    }
    Ext.applyIf(config, {
        title: _('msyamarket_item_create'),
        width: 550,
        autoHeight: true,
        url: msYaMarket.config.connector_url,
        action: 'mgr/item/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    msYaMarket.window.CreateItem.superclass.constructor.call(this, config);
};
Ext.extend(msYaMarket.window.CreateItem, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('msyamarket_item_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('msyamarket_item_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('msyamarket_item_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('msyamarket-item-window-create', msYaMarket.window.CreateItem);


msYaMarket.window.UpdateItem = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'msyamarket-item-window-update';
    }
    Ext.applyIf(config, {
        title: _('msyamarket_item_update'),
        width: 550,
        autoHeight: true,
        url: msYaMarket.config.connector_url,
        action: 'mgr/item/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    msYaMarket.window.UpdateItem.superclass.constructor.call(this, config);
};
Ext.extend(msYaMarket.window.UpdateItem, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('msyamarket_item_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('msyamarket_item_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%',
            height: 150,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('msyamarket_item_active'),
            name: 'active',
            id: config.id + '-active',
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('msyamarket-item-window-update', msYaMarket.window.UpdateItem);