import api from "@/api";

export default {
    namespaced: true,
    state: {
        marketplaces: [
            {text: 'Яндекс Маркет', value: 'yandex.market'} //default
        ],
    },
    getters: {
        marketplaceText: state => value => {
            let found = state.marketplaces.find(m => m.value === value);
            return found ? found.text : null;
        },
        getFields: state => (type, group) => {
            let marketplace = state.marketplaces.find(m => m.value === type);
            let fields = [];
            if (marketplace && marketplace[group + '_fields']) {
                fields = marketplace[group + '_fields'];
            }
            return fields;
        }
    },
    mutations: {
        setMarketplaces(state, payload) {
            state.marketplaces = payload.results;
        },

    },
    actions: {
        loadList({commit}) {
            api.post('lists/marketplaces')
                .then(({data}) => commit('setMarketplaces', data))
        }
    }
}