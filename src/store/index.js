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
            {text: 'Равно', sign: '=', value: 'equals', select: true},
            {text: 'Не равно', sign: '!=', value: 'not equals', select: true},
            {text: 'Больше чем', sign: '>', value: 'greater than'},
            {text: 'Меньше чем', sign: '<', value: 'less than'},
            {text: 'Больше или равно', sign: '>=', value: 'greater than or equal to'},
            {text: 'Меньше или равно', sign: '<=', value: 'less than or equal to'},
            {text: 'В строке', sign: 'LIKE', value: 'like'},
            {text: 'Не в строке', sign: 'NOT LIKE', value: 'not like'},
            {text: 'В списке', sign: 'IN', value: 'exists in', multiple: true, select: true},
            {text: 'Не в списке', sign: 'NOT IN', value: 'not exists in', multiple: true, select: true},
            {text: 'Null-значение', sign: 'IS NULL', value: 'is null', valueless: true},
            {text: 'Не null', sign: 'IS NOT NULL', value: 'is not null', valueless: true},
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
