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
import NotFound from './errors/NotFound';

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
      name: 'top',
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
      name: 'home',
      component: HomeComponent,
      meta: { requiresAuth: true }, //認証必須
    },
    {
      path: '/news',
      name: 'news.index',
      component: NewsListComponent,
      meta: { requiresAuth: true }, //認証必須
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
      component: TrendListComponent,
      meta: { requiresAuth: true }, //認証必須
    },
    {
      path: '/twitter',
      name: 'twitter.index',
      component: TwitterList,
      meta: { requiresAuth: true }, //認証必須,
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
      // ページコンポーネントが切り替わる直前のナビゲーションガード
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
      // ページコンポーネントが切り替わる直前のナビゲーションガード
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
      component: TickerListComponent,
      meta: { requiresAuth: true }, //認証必須
    },
    {
      path: '/error',
      name: 'errors.system',
      component: SystemError,
    },
    {
      path: '*', //定義されたルート以外のパス
      name: 'errors.notfound',
      component: NotFound,
    },
  ]
})

// ルーターナビゲーションの前にフック
router.beforeEach((to, from, next) => {
  // 認証必須のルートで認証チェックがfalseならログイン画面へ
  if (to.matched.some(record => record.meta.requiresAuth) && !store.getters['auth/check']) {
    next({ name: 'login' });
  } else {
    // ローディング表示をオン
    store.commit('loader/start')
    next();
  }
})
// ルーターナビゲーションの後にフック
router.afterEach(() => {
  // ローディング表示をオフ
  store.commit('loader/end')
})

// VueRouterインスタンスをエクスポートし、app.jsでインポートする
export default router
