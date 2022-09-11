import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify';
import axios from "axios";
import router from "./router";
import store from './store';
import * as Sentry from "@sentry/vue";
import { Integrations } from "@sentry/tracing";

const appId = 'yandexmarket-app';

window.onload = function () {
    const ym2Config = window.ym2Config || (process.env.NODE_ENV !== 'production' ? {
        modAuth: process.env.VUE_APP_MOD_AUTH || '',
        apiUrl: process.env.VUE_APP_API_URL || '',
        sentry: process.env.VUE_APP_SENTRY || '',
        xmlLoaded: true,
        isMODX3: false,
        lang: {}
    } : {});

    axios.defaults.baseURL = ym2Config.apiUrl || '/assets/components/yandexmarket2/connector.php';
    axios.defaults.headers.common['modAuth'] = ym2Config.modAuth;
    if (process.env.NODE_ENV !== 'production' && process.env.VUE_APP_COOKIE) {
        axios.defaults.headers.common['modCookie'] = process.env.VUE_APP_COOKIE;
    }

    if(ym2Config.sentry) {
        Sentry.init({
            Vue,
            dsn: ym2Config.sentry,
            integrations: [new Integrations.BrowserTracing()],
            tracesSampleRate: 1.0,
        });
    }

    Vue.prototype.$xmlLoaded = ym2Config.xmlLoaded;
    Vue.prototype.$isMODX3 = ym2Config.isMODX3;
    Vue.prototype.$t = (key) => ym2Config.lang && ym2Config.lang[key] || key;

    new Vue({
        vuetify,
        router,
        store,
        render: h => h(App)
    }).$mount(`#${appId}`)
}
