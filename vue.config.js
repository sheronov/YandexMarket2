module.exports = {
    outputDir: "assets/components/yandexmarket2/mgr",
    publicPath: "",
    filenameHashing: false,
    pages: {
        index: {
            entry: "src/main.js",
            template: "_develop/index.html",
            filename: 'index.html',
            title: "YandexMarket2",
            chunks: ["chunk-vendors", "chunk-common", "index"]
        }
    },
    chainWebpack: config => {
        if (process.env.NODE_ENV === 'production') {
            config.plugins.delete('html-index')
            config.plugins.delete('preload-index')
            config.plugins.delete('prefetch-index')
        }
    }
}