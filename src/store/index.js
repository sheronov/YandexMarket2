import Vue from 'vue'
import Vuex from 'vuex'
import field from './modules/field'
import pricelist from './modules/pricelist'
import marketplace from './modules/marketplace'

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        field,
        marketplace,
        pricelist,
    },
    mutations: {},
});