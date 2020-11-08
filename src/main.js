import Vue from 'vue'
import App from './App.vue'
// import axios from 'axios';

let appId = 'yandexmarket2-app';
Vue.config.productionTip = false

const appResize = () => {
    let windowHeight = window.innerHeight;
    if (document.getElementById('modx-header')) {
        windowHeight -= document.getElementById('modx-header').offsetHeight;
    }

    const app = document.getElementById(appId);
    app.style.cssText = `height:${windowHeight}px;overflow-y: auto; overflow-x: hidden;`;
}


Vue.mixin({
    mounted: function () {
        appResize();
        window.addEventListener('resize', appResize);
    },
    destroyed() {
        window.removeEventListener('resize', appResize);
    }
});

window.onload = function () {
    let ym2Config = window.ym2Config || {
        modAuth: process.env.VUE_APP_MOD_AUTH || '',
        apiUrl: process.env.VUE_APP_API_URL || '',
        lang: {}
    };

    // axios.defaults.withCredentials = true
    // axios.defaults.headers.common['modAuth'] = ym2Config.modAuth;
    let httpConfig = {
        headers: {
            modAuth: ym2Config.modAuth,
        },
    };

    if (process.env.VUE_APP_COOKIE) {
        let cookies = process.env.VUE_APP_COOKIE.split(';');
        cookies.forEach(cookie => {
            document.cookie = cookie  + '; expires=Sun, 1 Jan 2023 00:00:00 UTC; path=http://s9767.h8.modhost.pro/';
        })
        // document.cookie = process.env.VUE_APP_COOKIE + '; expires=Sun, 1 Jan 2023 00:00:00 UTC; path=/';
        httpConfig.headers['Cookie'] = process.env.VUE_APP_COOKIE;
    }

    console.log(ym2Config, httpConfig);

    Vue.prototype.$_ = (key) => ym2Config.lang[key] || key;
    Vue.prototype.$apiUrl = ym2Config.apiUrl;
    Vue.prototype.$httpConfig = httpConfig.headers;

    new Vue({
        render: h => h(App),
    }).$mount(`#${appId}`)
}