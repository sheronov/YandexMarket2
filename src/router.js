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
        meta: {title: 'Pricelists', to: 'pricelists'},
        children: [{
            path: '',
            component: PriceLists,
            name: 'pricelists',
        }, {
            path: ':id',
            component: PriceList,
            meta: {title: 'Shop settings', to: 'pricelist', replaceable: true},
            children: [{
                path: '',
                component: PriceListShop,
                name: 'pricelist',
            }, {
                path: 'categories',
                component: PriceListCategories,
                name: 'pricelist.categories',
                meta: {title: 'Categories'}
            }, {
                path: 'offers',
                component: PriceListOffer,
                name: 'pricelist.offers',
                meta: {title: 'Offers'}
            }, {
                path: 'generate',
                component: PriceListGenerate,
                name: 'pricelist.generate',
                meta: {title: 'File generation'}
            }]
        }, {
            path: '*', redirect: '/'
        }]
    }
];

export default new Router({
    routes
})