<template>
  <div class="container-fluid bg-dark mb-3">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-dark">
        <!-- active-class="is-active"で、該当ディレクトリ内にいる時のみis-activeクラスを付与 -->
        <!-- home '/'のみ、子のディレクトリは内包しないよう、exactオプションを使用 -->
        <!-- サイトロゴ -->
        <RouterLink
          :to="{ name: 'top.index' }"
          active-class="active"
          exact
          class="navbar-brand"
        >
          CryptoTrend
        </RouterLink>

        <!-- トグルアイコン -->
        <button
          class="navbar-toggler"
          type="button"
          data-toggle="collapse"
          data-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- ログイン済みユーザー向けメニュー -->
        <div v-if="isLogin" class="collapse navbar-collapse" id="navbarNav">
          <ul class="p-navbar navbar-nav">
            <li class="p-navbar__item nav-item">
              <RouterLink
                :to="{ name: 'trend.index' }"
                active-class="active"
                class="nav-link"
              >
                トレンド一覧
              </RouterLink>
            </li>
            <li class="p-navbar__item nav-item">
              <RouterLink
                :to="{ name: 'twitter.index' }"
                active-class="active"
                class="nav-link"
              >
                Twitterフォロー
              </RouterLink>
            </li>
            <li class="p-navbar__item nav-item">
              <RouterLink
                :to="{ name: 'news.index' }"
                active-class="active"
                class="nav-link"
              >
                関連ニュース
              </RouterLink>
            </li>
            <li class="p-navbar__item nav-item">
              {{ username }}
            </li>
            <li class="p-navbar__item nav-item">
              <a class="nav-link" @click="logout">Logout</a>
            </li>
          </ul>
        </div>

        <!-- 未ログインユーザー向けメニュー -->
        <div v-if="!isLogin" class="collapse navbar-collapse" id="navbarNav">
          <ul class="p-navbar navbar-nav">
            <li class="p-navbar__item nav-item">
              <RouterLink
                :to="{ name: 'register' }"
                active-class="active"
                class="nav-link"
              >
                無料ユーザー登録
              </RouterLink>
            </li>
            <li class="p-navbar__item nav-item">
              <RouterLink
                :to="{ name: 'login' }"
                active-class="active"
                class="nav-link"
              >
                ログイン
              </RouterLink>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </div>
</template>

<script>
export default {
  computed: {
    apiStatus() {
      // ストアのapiStatusステートを参照し、API通信の成否ステータスを取得
      return this.$store.state.auth.apiStatus;
    },
    isLogin() {
      // authストアのcheckゲッターでユーザーのログイン状態をチェック
      return this.$store.getters["auth/check"];
    },
    username() {
      // authストアのusernameゲッターでユーザー名を取得
      return this.$store.getters["auth/username"];
    },
  },
  methods: {
    async logout() {
      // dispatch()でauthストアのlogoutアクションを呼び出す
      await this.$store.dispatch("auth/logout");

      // API通信が成功した場合
      if (this.apiStatus) {
        // VueRouterのpush()でトップ画面に遷移
        this.$router.push("/");
      }
      // context.commit("setUser", null);
    },
  },
};
</script>
