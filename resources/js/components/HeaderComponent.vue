<template>
  <nav class="p-navbar">
    <!-- active-class="is-active"で、該当ディレクトリ内にいる時のみis-activeクラスを付与 -->
    <!-- home '/'のみ、子のディレクトリは内包しないよう、exactオプションを使用 -->
    <!-- サイトロゴ -->
    <h1 class="p-navbar__title">
      <RouterLink :to="{ name: 'top' }" active-class="is-active" exact>
        CryptoTrend
      </RouterLink>
    </h1>

    <!-- ログイン済みユーザー向けメニュー -->
    <div
      v-if="isLogin"
      class="p-nav-menu"
      :class="{ 'is-active': isActiveSpMenu }"
    >
      <ul class="p-nav-menu__list">
        <li class="p-nav-menu__item" @click="closeSpMenu">
          <RouterLink
            :to="{ name: 'trend.index' }"
            active-class="is-active"
            class="p-nav-menu__link"
          >
            トレンド一覧
          </RouterLink>
        </li>
        <li class="p-nav-menu__item" @click="closeSpMenu">
          <RouterLink
            :to="{ name: 'twitter.index' }"
            active-class="is-active"
            class="p-nav-menu__link"
          >
            Twitterフォロー
          </RouterLink>
        </li>
        <li class="p-nav-menu__item" @click="closeSpMenu">
          <RouterLink
            :to="{ name: 'news.index' }"
            active-class="is-active"
            class="p-nav-menu__link"
          >
            関連ニュース
          </RouterLink>
        </li>
      </ul>
      <ul class="p-nav-menu__list" v-if="isActiveSpMenu">
        <li class="p-nav-menu__item" @click="closeSpMenu">
          <RouterLink
            :to="{ name: 'edit' }"
            active-class="is-active"
            class="p-nav-menu__link"
          >
            アカウント設定
          </RouterLink>
        </li>
        <li class="p-nav-menu__item">
          <a class="p-nav-menu__link" @click="logout">ログアウト</a>
        </li>
      </ul>

      <ul class="p-nav-menu__dropdown" v-if="!isActiveSpMenu">
        <li class="p-nav-menu__dropdown-head" @click="toggleDropdown">
          <img
            :src="usersAvatar"
            class="p-nav-menu__avatar"
            :alt="`${userName}'s avatar`"
            @error="noImage"
          />
          <span class="p-nav-menu__username">{{ userName }}</span>
          <i
            class="fas fa-caret-down p-nav-menu__dropdown-icon"
            v-if="!isActiveSpMenu"
          ></i>
        </li>
        <div
          class="p-nav-menu__dropdown-menu"
          v-if="isActiveDropdown && !isActiveSpMenu"
        >
          <ul>
            <li class="p-nav-menu__dropdown-item">
              <RouterLink
                :to="{ name: 'edit' }"
                active-class="is-active"
                class="p-nav-menu__dropdown-link"
              >
                アカウント設定
              </RouterLink>
            </li>
            <li class="p-nav-menu__dropdown-item">
              <a class="p-nav-menu__dropdown-link" @click="logout"
                >ログアウト</a
              >
            </li>
          </ul>
        </div>
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
          <a href="#" class="p-nav-menu__link" @click="closeSpMenu"
            >CryptoTrendとは</a
          >
        </li>
        <li class="p-nav-menu__item">
          <a href="#" class="p-nav-menu__link" @click="closeSpMenu"
            >サービスの特長</a
          >
        </li>

        <li class="p-nav-menu__item-btn" @click="closeSpMenu">
          <RouterLink
            :to="{ name: 'register' }"
            active-class="is-active"
            class="c-btn__accent p-nav-menu__btn"
          >
            新規ユーザー登録
          </RouterLink>
        </li>
        <li class="p-nav-menu__item-btn" @click="closeSpMenu">
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
  </nav>
</template>

<script>
import { mapState, mapGetters } from "vuex"; // VuexのmapState関数,mapGetters関数をインポート

export default {
  data() {
    return {
      isActiveSpMenu: false,
      isActiveDropdown: false,
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
      // authストアのuserNameゲッターでユーザー名を取得
      usersAvatar: "twitter/usersAvatar",
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
    // APIで取得したアバターがリンク切れの場合
    noImage(element) {
      // 代替画像を表示
      element.target.src = "../img/avatar_noimage.png";
    },
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
        this.closeDropdown(); //スマホメニューが開いていたら閉じる

        // VueRouterのpush()でトップ画面に遷移
        this.$router.push({ name: "top" });
      }
    },
    toggleSpMenu() {
      this.isActiveSpMenu = !this.isActiveSpMenu;
    },
    closeSpMenu() {
      this.isActiveSpMenu = false;
    },
    toggleDropdown() {
      this.isActiveDropdown = !this.isActiveDropdown;
    },
    closeDropdown() {
      this.isActiveDropdown = false;
    },
  },
  created() {
    // ページ読み込み時にフラグを初期化
    this.closeSpMenu();
    this.closeDropdown();
  },
};
</script>
