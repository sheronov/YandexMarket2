// import '@mdi/font/css/materialdesignicons.css'
import 'font-awesome/css/font-awesome.min.css'
import Vue from 'vue';
import Vuetify from 'vuetify/lib';
import 'vuetify/src/styles/styles.sass'
import {MODX_ICONS} from "@/icons";
import { Ripple } from 'vuetify/lib/directives'

Vue.use(Vuetify, {
    directives: {
        Ripple
    }
});

export default new Vuetify({
    icons: {
        values: MODX_ICONS
    },
});