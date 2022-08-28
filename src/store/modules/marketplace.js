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
        availableFields: state => (group, pricelist) => {
            let marketplace = state.marketplaces.find(m => m.value === pricelist.type);
            let fields = [];
            if (marketplace && marketplace[group + '_fields']) {
                fields = marketplace[group + '_fields'];
            }
            let parent = pricelist.fields.find(p => p.type === (group === 'offer' ? 6 : 2));
            return fields.filter(field => field.multiple ||
                !pricelist.fields.find(pf => pf.name === field.value && pf.parent === parent.id)
            );
        }
    },
    mutations: {
        setMarketplaces(state, payload) {
            state.marketplaces = payload.results;
        },

    },
    actions: {
        loadList({commit}) {
            api.post('Lists/Marketplaces')
                .then(({data}) => commit('setMarketplaces', data))
        }
    }
}
