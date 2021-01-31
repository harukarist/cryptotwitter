<template>
  <nav class="p-navbar">
    <!-- active-class="is-active"で、該当ディレクトリ内にいる時のみis-activeクラスを付与 -->
    <!-- home '/'のみ、子のディレクトリは内包しないよう、exactオプションを使用 -->
    <!-- サイトロゴ -->
    <RouterLink :to="{ name: 'top' }" active-class="is-active" exact>
      <h1 class="p-navbar__title">CryptoTrend</h1>
    </RouterLink>

    <!-- ハンバーガーメニュー　spanタグで三本線を描写 -->
    <div
      class="p-navbar__toggle"
      @click="toggleSpMenu"
      :class="{ 'is-active': isActiveSpMenu }"
    >
      <span class="p-navbar__toggle--line"></span>
      <span class="p-navbar__toggle--line"></span>
      <span class="p-navbar__toggle--line"></span>
      <span class="p-navbar__toggle--text"></span>
    </div>

    <!-- ログイン済みユーザー向けメニュー -->
    <div
      v-if="isLogin"
      class="p-nav-menu"
      :class="{ 'is-active': isActiveSpMenu }"
    >
      <ul class="p-nav-menu__list">
        <li class="p-nav-menu__item" @click="toggleSpMenu">
          <RouterLink
            :to="{ name: 'trend.index' }"
            active-class="is-active"
            class="p-nav-menu__link"
          >
            <span class="p-nav-menu__text">トレンド一覧</span>
          </RouterLink>
        </li>
        <li class="p-nav-menu__item" @click="toggleSpMenu">
          <RouterLink
            :to="{ name: 'twitter.index' }"
            active-class="is-active"
            class="p-nav-menu__link"
          >
            Twitterフォロー
          </RouterLink>
        </li>
        <li class="p-nav-menu__item" @click="toggleSpMenu">
          <RouterLink
            :to="{ name: 'news.index' }"
            active-class="is-active"
            class="p-nav-menu__link"
          >
            関連ニュース
          </RouterLink>
        </li>
        <li class="p-nav-menu__item" @click="toggleSpMenu">
          {{ userName }}
        </li>
        <li class="p-nav-menu__item">
          <a class="p-nav-menu__link" @click="logout">Logout</a>
        </li>
      </ul>
    </div>

    <!-- 未ログインユーザー向けメニュー -->
    <div
      v-if="!isLogin"
      class="p-nav-menu"
      :class="{ 'is-active': isActiveSpMenu }"
    >
      <ul class="p-nav-menu__list">
        <li class="p-nav-menu__item">
          <a href="#" class="p-nav-menu__link" @click="toggleSpMenu"
            >CryptoTrendとは</a
          >
        </li>
        <li class="p-nav-menu__item">
          <a href="#" class="p-nav-menu__link" @click="toggleSpMenu"
            >サービスの特長</a
          >
        </li>

        <li class="p-nav-menu__item-btn" @click="toggleSpMenu">
          <RouterLink
            :to="{ name: 'register' }"
            active-class="is-active"
            class="c-btn__accent p-nav-menu__btn"
        >
            新規ユーザー登録
          </RouterLink>
        </li>
        <li class="p-nav-menu__item-btn" @click="toggleSpMenu">
          <RouterLink
            :to="{ name: 'login' }"
            active-class="is-active"
            class="c-btn__main-outline p-nav-menu__btn"
          >
            ログイン
          </RouterLink>
        </li>
      </ul>
    </div>
  </nav>
</template>

<script>
import { mapState, mapGetters } from "vuex"; // VuexのmapState関数,mapGetters関数をインポート

export default {
  data() {
    return {
      isActiveSpMenu: false,
    };
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.auth.apiStatus,
    }),
    ...mapGetters({
      // authストアのcheckゲッターでユーザーのログイン状態をチェック
      isLogin: "auth/check",
      // authストアのuserNameゲッターでユーザー名を取得
      userName: "auth/userName",
    }),
    // mapState,mapGettersを使わない場合は下記を記述
    // apiStatus() {
    //   return this.$store.state.auth.apiStatus;
    // },
    // isLogin() {
    //   return this.$store.getters["auth/check"];
    // },
    // userName() {
    //   return this.$store.getters["auth/userName"];
    // },
  },
  methods: {
    async logout() {
      // dispatch()でauthストアのlogoutアクションを呼び出す
      await this.$store.dispatch("auth/logout");

      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch(
          "message/showMessage",
          {
            text: "ログアウトしました。ご利用ありがとうございました。",
            type: "success",
            timeout: 3000,
          },
          { root: true }
        );
        this.isActiveSpMenu = false; //スマホメニューが開いていたら閉じる

        // VueRouterのpush()でトップ画面に遷移
        this.$router.push({ name: "top" });
      }
    },
    toggleSpMenu() {
      this.isActiveSpMenu = !this.isActiveSpMenu;
    },
  },
};
</script>
