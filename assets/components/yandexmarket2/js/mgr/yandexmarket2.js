var YandexMarket2 = function (config) {
    config = config || {};
    YandexMarket2.superclass.constructor.call(this, config);
};
Ext.extend(YandexMarket2, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('yandexmarket2', YandexMarket2);

YandexMarket2 = new YandexMarket2();