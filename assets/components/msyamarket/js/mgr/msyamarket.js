var msYaMarket = function (config) {
    config = config || {};
    msYaMarket.superclass.constructor.call(this, config);
};
Ext.extend(msYaMarket, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('msyamarket', msYaMarket);

msYaMarket = new msYaMarket();