export default {
    namespaced: true,
    state: {
        marketplaces: [
            {text: 'Яндекс Маркет', value: 'yandex.market'}
        ],
    },
    getters: {
        marketplaceText: state => value => {
            let found = state.marketplaces.find(m => m.value === value);
            return found ? found.text : null;
        },
    }
}