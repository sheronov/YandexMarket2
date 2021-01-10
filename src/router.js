import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from './components/HelloWorld'
import PriceLists from './pages/PriceLists'
import PriceList from './pages/PriceList'
import PriceListColumns from './pages/PriceListColumns'
import PriceListSettings from './pages/PriceListSettings'
import PriceListCategories from './pages/PriceListCategories'
import Main from './pages/Main'

Vue.use(Router);

const routes = [
    {
        path: '/',
        component: Main,
        meta: {title: 'Прайс-листы'},
        children: [{
            path: '',
            component: PriceLists,
            name: 'pricelists',
        }, {
            path: ':id',
            component: PriceList,
            meta: {title: 'Настройки прайс-листа'},
            children: [{
                path: '',
                component: PriceListSettings,
                name: 'pricelist',
            }, {
                path: 'categories',
                component: PriceListCategories,
                name: 'pricelist.categories'
            }, {
                path: 'offers',
                component: PriceListColumns,
                name: 'pricelist.offers'
            }]
        }, {
            path: '404', name: '404', component: HelloWorld, meta: {title: 'Ошибка 404'}
        }, {
            path: '*', redirect: '404'
        }]
    }
];

export default new Router({
    routes
})