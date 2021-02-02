/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');

// window.Vue = require('vue');

import './bootstrap'; //bootstrap.jsの設定ファイルをインポート
import Vue from 'vue';
import router from './router'; // router.jsからルーティング定義をインポート
import store from './store'; // ストアをインポート
import AppComponent from './AppComponent.vue'; // コンテンツのルートコンポーネント
import MessageComponent from './components/MessageComponent.vue'; // フラッシュメッセージ
import HeaderComponent from "./components/HeaderComponent";
import FooterComponent from "./components/FooterComponent";
import LoaderComponent from "./components/LoaderComponent";
import '../sass/app.scss'; //Sassの起点ファイルをインポート

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))


/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// 画面リロード時にVueインスタンスを再生成してもユーザー情報が表示されるよう、生成前に非同期処理でログインチェックを行う
const createApp = async () => {
    const userLogin = store.dispatch("auth/currentUser");
    const userTwitter = store.dispatch("twitter/checkAuth");
    await Promise.all([userLogin, userTwitter]);

    // Vueインスタンスを生成
    new Vue({
        el: '#app',
        router, // Vue Routerを読み込む
        store, // Vuexのストアを読み込む
        components: {
            AppComponent,
            MessageComponent,
            HeaderComponent,
            FooterComponent,
            LoaderComponent
        },
    });
}
// 初回起動時のログインチェック、Vueインスタンス生成を呼び出し
createApp();
