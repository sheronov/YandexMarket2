import Vue from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify';

const appId = 'yandexmarket2-app';

const appResize = () => {
    let windowHeight = window.innerHeight;
    if (document.getElementById('modx-header')) {
        windowHeight -= document.getElementById('modx-header').offsetHeight;
    }

    const app = document.getElementById(appId);
    if(app) {
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
    Vue.prototype.$t = (key) => window.ym2Config && window.ym2Config.lang[key] || key;

    new Vue({
        vuetify,
        render: h => h(App)
    }).$mount(`#${appId}`)
}