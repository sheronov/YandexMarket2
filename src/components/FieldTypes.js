export const TYPE_ROOT = 0;
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

export default [
    {value: TYPE_ROOT, text: 'корневой узел', selectable: false},
    {value: TYPE_SHOP, text: 'магазин', selectable: false},
    {value: TYPE_CURRENCIES, text: 'валюта', selectable: false},
    {value: TYPE_CATEGORIES, text: 'категории', selectable: false},
    {value: TYPE_OFFERS, text: 'предложения', selectable: false},
    {value: TYPE_OFFER, text: 'предложение', selectable: false},
    {value: TYPE_OPTION, text: 'текст без обработки'},
    {value: TYPE_FEATURE, text: 'ещё не реализовано', selectable: false},
    {value: TYPE_STRING, text: 'строковое значение'},
    {value: TYPE_CDATA, text: 'текст в CDATA'},
    {value: TYPE_NUMBER, text: 'числовое значение'},
    {value: TYPE_BOOLEAN, text: 'да/нет значение'},
    {value: TYPE_PARAM, text: 'параметр'}, //TODO: можно убрать
    {value: TYPE_PICTURES, text: 'изображения'},
    {value: TYPE_PARENT, text: 'родительский'},
];