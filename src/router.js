import Vue from 'vue'
import Router from 'vue-router'
import PriceLists from './pages/PriceLists'
import PriceList from './pages/PriceList'
import PriceListOffer from './pages/PriceListOffer'
import PriceListShop from './pages/PriceListShop'
import PriceListCategories from './pages/PriceListCategories'
import PriceListGenerate from './pages/PriceListGenerate'
import Main from './pages/Main'

Vue.use(Router);

const routes = [
    {
        path: '/',
        component: Main,
        meta: {title: 'Прайс-листы', to: 'pricelists'},
        children: [{
            path: '',
            component: PriceLists,
            name: 'pricelists',
        }, {
            path: ':id',
            component: PriceList,
            meta: {title: 'Настройки прайс-листа', to: 'pricelist', replaceable: true},
            children: [{
                path: '',
                component: PriceListShop,
                name: 'pricelist',
            }, {
                path: 'categories',
                component: PriceListCategories,
                name: 'pricelist.categories',
                meta: {title: 'Категории'}
            }, {
                path: 'offers',
                component: PriceListOffer,
                name: 'pricelist.offers',
                meta: {title: 'Данные'}
            }, {
                path: 'generate',
                component: PriceListGenerate,
                name: 'pricelist.generate',
                meta: {title: 'Выгрузка'}
            }]
        }, {
            path: '*', redirect: '/'
        }]
    }
];

export default new Router({
    routes
})