import Vue from 'vue'
import App from './App.vue'
// import axios from 'axios';

const appId = 'yandexmarket2-app';

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
    Vue.prototype.$_ = (key) => window.ym2Config.lang[key] || key;

    new Vue({
        render: h => h(App),
    }).$mount(`#${appId}`)
}