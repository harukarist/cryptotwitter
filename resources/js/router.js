import Vue from 'vue'
import VueRouter from 'vue-router'
import store from './store' //ストアをインポート

// ページコンポーネントをインポート
import TopComponent from './pages/TopComponent';
import HomeComponent from './pages/HomeComponent';
import NewsListComponent from './pages/NewsListComponent';
import TrendListComponent from './pages/TrendListComponent';
import TwitterList from './pages/TwitterList';
import TickerListComponent from './pages/TickerListComponent';
import RegisterComponent from './pages/RegisterComponent';
import LoginComponent from './pages/LoginComponent';
import SystemError from './errors/SystemError';

// VueRouterを使用してVueコンポーネントを切り替える
Vue.use(VueRouter)

// VueRouterインスタンスのルーティング設定
const router = new VueRouter({
  mode: 'history', // URLにハッシュ #を付けない
  scrollBehavior() {
    return { x: 0, y: 0 } //ページ遷移時にスクロール位置を先頭に戻す
  },
  routes: [
    {
      path: '/',
      name: 'top.index',
      component: TopComponent,
      beforeEnter(to, from, next) {
        // authストアのcheckゲッターでログイン状態をチェック
        if (store.getters['auth/check']) {
          // ログイン済みの場合はホーム画面に遷移
          next('/home')
        } else {
          // 未ログインの場合はトップ画面を表示
          next()
        }
      }
    },
    {
      path: '/home',
      name: 'home.index',
      component: HomeComponent
    },
    {
      path: '/news',
      name: 'news.index',
      component: NewsListComponent,
      // ページネーションのクエリパラメータpageをrouteから取り出し、propsでコンポーネントに渡す
      props: route => {
        const page = route.query.page
        // 整数以外が渡された場合は1に変換して返却
        return { page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1 }
      }
    },
    {
      path: '/trend',
      name: 'trend.index',
      component: TrendListComponent
    },
    {
      path: '/twitter',
      name: 'twitter.index',
      component: TwitterList,
      // ページネーションのクエリパラメータpageをrouteから取り出し、propsでコンポーネントに渡す
      props: route => {
        const page = route.query.page
        // 整数以外が渡された場合は1に変換して返却
        return { page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1 }
      }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterComponent,
      // ログイン済みユーザーがアクセスした場合は
      beforeEnter(to, from, next) {
        // authストアのcheckゲッターでログイン状態をチェック
        if (store.getters['auth/check']) {
          // ログイン済みの場合はホーム画面に遷移
          next('/home')
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
          // ログイン済みの場合はホーム画面に遷移
          next('/home')
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
