import '@mdi/font/css/materialdesignicons.css'
import Vue from 'vue';
import Vuetify from 'vuetify/lib';
import 'vuetify/src/styles/styles.sass'

Vue.use(Vuetify);

export default new Vuetify({
    icons: {
        iconfont: 'mdi', // default
    },
});
