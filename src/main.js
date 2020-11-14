import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify';
import axios from "axios";

const appId = 'yandexmarket2-app';

const appResize = () => {
    let windowHeight = window.innerHeight;
    if (document.getElementById('modx-header')) {
        windowHeight -= document.getElementById('modx-header').offsetHeight;
    }

    const app = document.getElementById(appId);
    if (app) {
        app.style.cssText = `height:${windowHeight}px;overflow-y: auto; overflow-x: hidden;`;
    }
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
    let ym2Config = window.ym2Config || (process.env.NODE_ENV !== 'production' ? {
        modAuth: process.env.VUE_APP_MOD_AUTH || '',
        apiUrl: process.env.VUE_APP_API_URL || '',
        lang: {}
    } : {});

    axios.defaults.baseURL = ym2Config.apiUrl || '/assets/components/yandexmarket2/connector.php';
    axios.defaults.headers.common['modAuth'] = ym2Config.modAuth;
    if (process.env.NODE_ENV !== 'production' && process.env.VUE_APP_COOKIE) {
        axios.defaults.headers.common['modCookie'] = process.env.VUE_APP_COOKIE;
    }

    Vue.prototype.$t = (key) => ym2Config.lang && ym2Config.lang[key] || key;

    new Vue({
        vuetify,
        render: h => h(App)
    }).$mount(`#${appId}`)
}