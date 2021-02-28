import api from "@/api";

export default {
    namespaced: true,
    state: {
        types: [],
    },
    getters: {
        findByType: state => type => state.types.find(t => t.value === type),
        typeText: state => ({type}) => {
            let found = state.types.find((t) => t.value === type);
            return found ? found.text : null;
        },
        selectableTypes: state => state.types.filter(type => !type.hidden),
        isSingle: state => ({type}) => !!state.types.find(t => t.single && t.value === type), // одно на уровне
        isParent: state => ({type}) => !!state.types.find(t => t.value === type && t.parent), //может иметь узлы
        isUnique: state => ({type}) => !!state.types.find(t => t.unique && t.value === type), //иной обработчик
        isSimpleString: () => ({type}) => parseInt(type) === 0, //простое текстовое поле без подстановки значений
        isEmptyType: () => ({type}) => parseInt(type) === 20, //пустой элемент только для атрибутов
        isRoot: () => ({type}) => parseInt(type) === 1, // рут элемент
        isCategories: () => ({type}) => parseInt(type) === 4,
        isCurrencies: () => ({type}) => parseInt(type) === 3,
        isOffers: () => ({type}) => parseInt(type) === 5,
        isShop: () => ({type}) => parseInt(type) === 2,
        isPictures: () => ({type}) => parseInt(type) === 13,
        availableTypes: (state, getters) => (group, pricelist) => {
            return getters.selectableTypes.filter(type => {
                let typeGroup = type.group || [];
                if (!Array.isArray(typeGroup)) {
                    typeGroup = [typeGroup];
                }
                if (pricelist && pricelist.fields.find(field => type.unique && field.type === type.value)) {
                    return false;
                }
                return typeGroup.indexOf(group) !== -1;
            })
        }
    },
    mutations: {
        setFieldTypes(state, payload) {
            state.types = payload.results;
        },
    },
    actions: {
        loadListTypes({commit}) {
            api.post('lists/fields')
                .then(({data}) => commit('setFieldTypes', data))
        }
    }
}
