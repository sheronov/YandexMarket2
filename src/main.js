import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify';
import axios from "axios";
import router from "./router";
import store from './store';

const appId = 'yandexmarket-app';

const appResize = () => {
    let windowHeight = window.innerHeight;
    let header = document.getElementById('modx-header');
    if (header) {
        windowHeight -= header.offsetHeight;
    }

    const app = document.getElementById(appId);
    if (app) {
        app.style.cssText = `height:${windowHeight}px;overflow-y: auto; overflow-x: hidden;`;
    }
}

window.onload = function () {
    Vue.mixin({
        mounted: function () {
            appResize();
            window.addEventListener('resize', appResize);
        },
        destroyed() {
            window.removeEventListener('resize', appResize);
        }
    });

    let ym2Config = window.ym2Config || (process.env.NODE_ENV !== 'production' ? {
        modAuth: process.env.VUE_APP_MOD_AUTH || '',
        apiUrl: process.env.VUE_APP_API_URL || '',
        xmlLoaded: true,
        lang: {}
    } : {});

    axios.defaults.baseURL = ym2Config.apiUrl || '/assets/components/yandexmarket2/connector.php';
    axios.defaults.headers.common['modAuth'] = ym2Config.modAuth;
    if (process.env.NODE_ENV !== 'production' && process.env.VUE_APP_COOKIE) {
        axios.defaults.headers.common['modCookie'] = process.env.VUE_APP_COOKIE;
    }

    Vue.prototype.$xmlLoaded = ym2Config.xmlLoaded;
    Vue.prototype.$t = (key) => ym2Config.lang && ym2Config.lang[key] || key;

    new Vue({
        vuetify,
        router,
        store,
        render: h => h(App)
    }).$mount(`#${appId}`)
}