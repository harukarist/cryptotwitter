<template>
  <div class="container-fluid bg-dark mb-3">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-dark">
        <!-- active-class="is-active"で、該当ディレクトリ内にいる時のみis-activeクラスを付与 -->
        <!-- home '/'のみ、子のディレクトリは内包しないよう、exactオプションを使用 -->
        <!-- サイトロゴ -->
        <RouterLink
          :to="{ name: 'home.index' }"
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

        <!-- メニュー -->
        <div class="collapse navbar-collapse" id="navbarNav">
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
              <a class="nav-link" @click="auth()"> Login / Register </a>
            </li>
            <li class="p-navbar__item nav-item">
              <a class="nav-link" @click="logout()"> Logout </a>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    auth() {
      axios.get("/api/auth/twitter").then((res) => {
        // 現在のパスをgo()メソッドに渡してリロード
        // this.$router.go({ path: this.$router.currentRoute.path, force: true });
        console.log("oAuth Login success");
      });
    },
    logout() {
      axios.get("/api/auth/twitter/logout").then((res) => {
        // 現在のパスをgo()メソッドに渡してリロード
        // this.$router.go({ path: this.$router.currentRoute.path, force: true });
        console.log("oAuth Logout success");
      });
    },
  },
};
</script>
