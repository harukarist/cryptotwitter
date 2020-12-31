import Vue from 'vue';
import VueRouter from 'vue-router';
import App from './App.vue';
// import HeaderComponent from './components/HeaderComponent';
import HomeComponent from './components/HomeComponent';
import NewsListComponent from './components/NewsListComponent';
import TrendListComponent from './components/TrendListComponent';
import TwitterListComponent from './components/TwitterListComponent';
import TwitterAuthComponent from './components/TwitterAuthComponent';
import AuthLinkComponent from './components/AuthLinkComponent';
import TickerListComponent from './components/TickerListComponent';

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.use(VueRouter);

// VueRouterのルーティング設定
const router = new VueRouter({
    mode: 'history', // URLにハッシュ #を付けない
    routes: [
        {
            path: '/',
            name: 'home.index',
            component: HomeComponent
        },
        {
            path: '/news',
            name: 'news.index',
            component: NewsListComponent
        },
        {
            path: '/trend',
            name: 'trend.index',
            component: TrendListComponent
        },
        {
            path: '/twitter',
            name: 'twitter.index',
            component: TwitterListComponent
        },
        {
            path: '/auth',
            name: 'twitter.auth',
            component: TwitterAuthComponent
        },
        {
            path: '/tickers',
            name: 'tickers.index',
            component: TickerListComponent
        },
    ]
})

// 常に表示するコンポーネント
// Vue.component('header-component', HeaderComponent);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    router, //Vue Routerを読み込む
    components: { App }, // ルートコンポーネントを宣言
    template: '<App />' // ルートコンポーネントを描画
});
