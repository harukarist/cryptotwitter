import Vue from 'vue'
import VueRouter from 'vue-router'
import store from './store' //ストアをインポート

// ページコンポーネントをインポート
import TopComponent from './pages/TopComponent';
import HomeComponent from './pages/HomeComponent';
import NewsListComponent from './pages/NewsListComponent';
import TrendListComponent from './pages/TrendListComponent';
import TwitterListComponent from './pages/TwitterListComponent';
import RegisterComponent from './pages/RegisterComponent';
import LoginComponent from './pages/LoginComponent';
import PassRequestComponent from './pages/PassRequestComponent';
import PassResetComponent from './pages/PassResetComponent';
import EditAccountComponent from './pages/EditAccountComponent';
import WithdrawComponent from './pages/WithdrawComponent';
import PrivacyPolicyComponent from './pages/PrivacyPolicyComponent';
import TermsComponent from './pages/TermsComponent';
import ContactComponent from './pages/ContactComponent';
import ContactForm from './components/ContactForm';
import ContactConfirm from './components/ContactConfirm';
import ContactSent from './components/ContactSent';
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
      meta: { onlyGuest: true }, //ログイン前のみアクセス可能
    },
    {
      path: '/home',
      name: 'home',
      component: HomeComponent,
      meta: { requiresAuth: true }, //認証必須
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
      component: TwitterListComponent,
      meta: { requiresAuth: true }, //認証必須,
      // ページネーションのクエリパラメータpageをrouteから取り出し、propsでコンポーネントに渡す
      props: route => {
        const page = route.query.page
        // 整数以外が渡された場合は1に変換して返却
        return { page: /^[1-9][0-9]*$/.test(page) ? page * 1 : 1 }
      }
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
      path: '/edit',
      name: 'edit',
      component: EditAccountComponent,
      meta: { requiresAuth: true }, //認証必須,
    },
    {
      path: '/withdraw',
      name: 'withdraw',
      component: WithdrawComponent,
      meta: { requiresAuth: true }, //認証必須,
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterComponent,
      meta: { onlyGuest: true }, //ログイン前のみアクセス可能
    },
    {
      path: '/login',
      name: 'login',
      component: LoginComponent,
      meta: { onlyGuest: true }, //ログイン前のみアクセス可能
    },
    {
      path: '/pass/request',
      name: 'password.request',
      component: PassRequestComponent,
      meta: { onlyGuest: true }, //ログイン前のみアクセス可能
    },
    {
      path: '/pass/reset/:token',
      name: 'password.reset',
      component: PassResetComponent,
      meta: { onlyGuest: true }, //ログイン前のみアクセス可能
    },
    {
      path: '/error',
      name: 'errors.system',
      component: SystemError,
    },
    {
      path: '/terms',
      name: 'terms',
      component: TermsComponent,
    },
    {
      path: '/privacy',
      name: 'privacy',
      component: PrivacyPolicyComponent,
    },
    {
      path: "/contact",
      name: 'contact',
      component: ContactComponent,
    },
    {
      path: '*', //定義されたルート以外のパス
      name: 'errors.notfound',
      component: NotFound,
    },
  ]
})

// ルーターナビゲーションの前にフック（ページコンポーネントが切り替わる直前のナビゲーションガード）
router.beforeEach((to, from, next) => {
  // // ローディング表示をオン
  // store.commit('loader/setIsLoading', true)
  // 認証必須のルートで認証チェックがfalseならログイン画面へ
  if (to.matched.some(record => record.meta.requiresAuth) && !store.getters['auth/check']) {
    next({ name: 'login' });
    // ログイン前のみアクセス可のルートで認証チェックがtrueならホーム画面へ
  } else if (to.matched.some(record => record.meta.onlyGuest) && store.getters['auth/check']) {
    next({ name: 'home' });
  } else {
    // その他の場合はそのままルーティング先へ遷移
    next();
  }
})
// ルーターナビゲーションの後にフック
router.afterEach(() => {
  // // ローディング表示をオフ
  // store.commit('loader/setIsLoading', false)
})


// VueRouterインスタンスをエクスポートし、app.jsでインポートする
export default router
