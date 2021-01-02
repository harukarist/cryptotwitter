import Vue from 'vue'
import VueRouter from 'vue-router'
import store from './store' //ストアをインポート

// ページコンポーネントをインポート
import HomeComponent from './components/HomeComponent';
import NewsListComponent from './components/NewsListComponent';
import TrendListComponent from './components/TrendListComponent';
import TwitterListComponent from './components/TwitterListComponent';
import TickerListComponent from './components/TickerListComponent';
import RegisterComponent from './components/RegisterComponent';
import LoginComponent from './components/LoginComponent';
import TwitterLoginComponent from './components/TwitterLoginComponent';
import SystemError from './errors/SystemError';


// VueRouterを使用してVueコンポーネントを切り替える
Vue.use(VueRouter)

// VueRouterインスタンスのルーティング設定
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
      component: TwitterLoginComponent
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterComponent,
      // ログイン済みユーザーがアクセスした場合は
      beforeEnter(to, from, next) {
        // authストアのcheckゲッターでログイン状態をチェック
        if (store.getters['auth/check']) {
          // ログイン済みの場合はトップページに遷移
          next('/')
        } else {
          // 未ログインの場合はユーザー登録画面を表示
          next()
        }
      }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginComponent,
      beforeEnter(to, from, next) {
        // authストアのcheckゲッターでログイン状態をチェック
        if (store.getters['auth/check']) {
          // ログイン済みの場合はトップページに遷移
          next('/')
        } else {
          // 未ログインの場合はログイン画面を表示
          next()
        }
      }
    },
    {
      path: '/tickers',
      name: 'tickers.index',
      component: TickerListComponent
    },
    {
      path: '/500',
      name: 'errors.system',
      component: SystemError
    },
  ]
})

// VueRouterインスタンスをエクスポートし、app.jsでインポートする
export default router
