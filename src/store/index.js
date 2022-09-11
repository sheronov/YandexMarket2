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
        operators: [
            {sign: '=', value: 'equals', select: true},
            {sign: '!=', value: 'not equals', select: true},
            {sign: '>', value: 'greater than'},
            {sign: '<', value: 'less than'},
            {sign: '>=', value: 'greater than or equal to'},
            {sign: '<=', value: 'less than or equal to'},
            {sign: 'LIKE', value: 'like'},
            {sign: 'NOT LIKE', value: 'not like'},
            {sign: 'IN', value: 'exists in', multiple: true, select: true},
            {sign: 'NOT IN', value: 'not exists in', multiple: true, select: true},
            {sign: 'IS NULL', value: 'is null', valueless: true},
            {sign: 'IS NOT NULL', value: 'is not null', valueless: true},
        ],
    },
    getters: {
        dataColumnsForGroup: state => group => {
            let classKeys = [];
            state.classKeys.forEach((data) => {
                if (!group || data.groups.indexOf(group) !== -1) {
                    if (data.header) {
                        classKeys.push({header: data.header});
                    }
                    if (data.fields) {
                        classKeys.push(...data.fields);
                    }
                    classKeys.push({divider: true});
                }
            });
            return classKeys;
        },
        operatorsList: state => state.operators,
    },
    mutations: {
        setClassKeys(state, payload) {
            state.classKeys = payload.results;
        },
    },
    actions: {
        loadClassKeys({commit}) {
            api.post('Lists/ClassKeys')
                .then(({data}) => commit('setClassKeys', data))
        },
    }
});
