import Vue from 'vue'
import Vuex from 'vuex'
import field from './modules/field'
import pricelist from './modules/pricelist'
import marketplace from './modules/marketplace'
import api from "@/api";

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        field,
        marketplace,
        pricelist,
    },
    state: {
        classKeys: [],
    },
    getters: {
        dataColumns: state => state.classKeys.filter(ck => !ck.skipped),
        columnText:  state => value => {
            let found = state.classKeys.find(classKey => classKey.value === value);
            return found ? found.text : null;
        }
    },
    mutations: {
        setClassKeys(state, payload) {
            state.classKeys = payload.results;
        },
    },
    actions: {
        loadClassKeys({commit}) {
            api.post('lists/classkeys')
                .then(({data}) => commit('setClassKeys', data))
        }
    }
});