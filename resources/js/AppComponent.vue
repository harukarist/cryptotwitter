<template>
  <div>
    <!-- <header id="header">
      <HeaderComponent />
    </header> -->

    <main id="main">
      <page-top />
      <inner-message />
      <loader-component v-show="isLoading" />
      <router-view v-show="!isLoading" />
    </main>
    <footer id="footer">
      <footer-component />
    </footer>
  </div>
</template>

<script>
// app.blade.phpで読み込むvue.jsのルートコンポーネント
// VueRouterでページコンポーネントを切り替える
import { NOT_FOUND, UNAUTHORIZED, INTERNAL_SERVER_ERROR } from "./utility";
import HeaderComponent from "./components/HeaderComponent";
import FooterComponent from "./components/FooterComponent";
import LoaderComponent from "./components/LoaderComponent";
import PageTop from "./components/PageTop";
import InnerMessage from "./components/InnerMessage";

export default {
  components: {
    HeaderComponent, //ヘッダー
    FooterComponent, //フッター
    LoaderComponent, //ページ読み込み中のローディングアニメーション
    PageTop, //ページトップへ戻るボタン
    InnerMessage, //Vuexで管理するフラッシュメッセージ
  },
  computed: {
    errorCode() {
      // ストアのerrorモジュールのステートを参照
      return this.$store.state.error.code;
    },
    isLoading() {
      return this.$store.state.loader.isLoading;
    },
  },
  watch: {
    // watchプロパティでerrorCodeの値の変更を監視
    errorCode: {
      async handler(val) {
        // サーバー内部エラーの場合はエラーメッセージを表示
        if (val === INTERNAL_SERVER_ERROR) {
          this.$store.dispatch("message/showMessage", {
            text:
              "システムエラーが発生しました。お手数ですが、時間を置いて再度お試しください。",
            type: "danger",
            timeout: 2000,
          });
        } else if (val === UNAUTHORIZED) {
          // セッション切れなど認証エラーの場合
          // CSRFトークンのリフレッシュ処理
          await axios.get("/api/refresh-token");
          // ストアの古いuserDataをクリア
          this.$store.commit("auth/setUserData", null);
          // ログイン画面へ遷移
          this.$router.push({ name: "login" });
        } else if (val === NOT_FOUND) {
          // 404エラーの場合はNotFoundページを表示
          this.$router.push({ name: "errors.notfound" });
        }
      },
      immediate: true, // 初期化時もwatchを発火させる
    },
    $route() {
      // ストアのsetCodeミューテーションでステートの値をnullに変更
      this.$store.commit("error/setCode", null);
    },
  },
};
</script>
