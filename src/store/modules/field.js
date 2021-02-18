export const TYPE_ROOT = 0; //самый корневой
export const TYPE_PARENT = 1; //обёртка без своего собственного значения
export const TYPE_SHOP = 2; // поле магазин (сюда будут прокинуты SHOP_FIELDS)
export const TYPE_CURRENCIES = 4; // валюта
export const TYPE_CATEGORIES = 5; // категории
export const TYPE_OFFERS = 6; // предложения (почти бесполезно, но нужно, чтобы пропускать)
export const TYPE_OFFER = 7; // предложение
export const TYPE_OPTION = 8; // чисто текстовое значение (не будет как-либо обрабатываться)
export const TYPE_FEATURE = 9; // ещё не реализовано
export const TYPE_STRING = 10; //просто строковое поле (подходит всегда)
export const TYPE_CDATA = 11; //большой текст, обернуть в CDATA
export const TYPE_NUMBER = 12; //числовое предложения
export const TYPE_BOOLEAN = 13; //выбор да/нет (игнорировать null - intermediate)
export const TYPE_PARAM = 14; // параметр предложения
export const TYPE_PICTURES = 15; // изображения предложения

export default {
    namespaced: true,
    state: {
        types: [
            {header: 'Для товара'},
            {value: TYPE_STRING, text: 'строковое'},
            {value: TYPE_CDATA, text: 'текст в CDATA'},
            {value: TYPE_NUMBER, text: 'числовое'},
            {value: TYPE_BOOLEAN, text: 'да/нет'},
            {value: TYPE_PARAM, text: 'параметр'}, //TODO: можно убрать
            {value: TYPE_PICTURES, text: 'изображения', unique: true},
            {value: TYPE_PARENT, text: 'родительский', parent: true},
            {divider: true},
            {header: 'Для настроек'},
            {value: TYPE_OPTION, text: 'текст без обработки'},
            {divider: true},
            {header: 'Специальные поля'},
            {value: TYPE_ROOT, text: 'корневой', disabled: true, unique: true, root: true, parent: true, hidden: true},
            {value: TYPE_SHOP, text: 'магазин', disabled: true, unique: true, root: true, parent: true, hidden: true},
            {value: TYPE_CURRENCIES, text: 'список валют', disabled: true, unique: true, required: true},
            {value: TYPE_CATEGORIES, text: 'категории', disabled: true, unique: true, required: true},
            {value: TYPE_OFFERS, text: 'предложения', disabled: true, unique: true, required: true},
            {value: TYPE_OFFER, text: 'оффер', disabled: true, unique: true, root: true, parent: true, hidden: true},
            {value: TYPE_FEATURE, text: 'ещё не реализовано', disabled: true, hidden: true},
        ]
    },
    getters: {
        selectableTypes: state => state.types.filter(type => !type.hidden),
        typeText: state => ({type}) => {
            let found = state.types.find((t) => t.value === type);
            return found ? found.text : null;
        },
        isRoot: state => ({type}) => !!state.types.find(t => t.root && t.value === type), // одно на уровне
        isParent: state => ({type}) => !!state.types.find(t => t.parent && t.value === type), //может иметь узлы
        isUnique: state => ({type}) => !!state.types.find(t => t.unique && t.value === type), //иной обработчик
        isSimpleString: () => ({type}) => type === TYPE_OPTION, //простое текстовое поле без подстановки значений
        isCategories: () => ({type}) => type === TYPE_CATEGORIES, //простое текстовое поле без подстановки значений
        isCurrencies: () => ({type}) => type === TYPE_CURRENCIES, //простое текстовое поле без подстановки значений
        isOffers: () => ({type}) => type === TYPE_OFFERS, //простое текстовое поле без подстановки значений
        isPictures: () => ({type}) => type === TYPE_PICTURES, //простое текстовое поле без подстановки значений
    },
    mutations: {},
    actions: {},
}
