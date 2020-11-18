import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from './components/HelloWorld'
import Tree from './components/Tree'
import Main from './components/Main'

Vue.use(Router);

const routes = [
    {path: '/', component: Main},
    {path: '/tree', component: Tree},
    {path: '/hello', component: HelloWorld},
];

export default new Router({
    routes
})