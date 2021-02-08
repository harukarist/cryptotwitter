<template>
  <div>
    <LoaderComponent />
    <!-- メッセージ表示 -->
    <InnerMessage />
    <!-- VueRouterでコンポーネントを表示 -->
    <RouterView />
  </div>
</template>

<script>
import InnerMessage from "./components/InnerMessage";
import HeaderComponent from "./components/HeaderComponent";
import FooterComponent from "./components/FooterComponent";
import LoaderComponent from "./components/LoaderComponent";
import { NOT_FOUND, UNAUTHORIZED, INTERNAL_SERVER_ERROR } from "./utility";

export default {
  components: {
    InnerMessage,
    HeaderComponent,
    FooterComponent,
    LoaderComponent,
  },
  computed: {
    errorCode() {
      // ストアのerrorモジュールのステートを参照
      return this.$store.state.error.code;
    },
  },
  watch: {
    // watchプロパティでerrorCodeの値の変更を監視
    errorCode: {
      async handler(val) {
        // サーバー内部エラーの場合はエラーメッセージを表示
        if (val === INTERNAL_SERVER_ERROR) {
          // this.$router.push("/error");
          // this.$router.push({ name: "errors.system" });
          this.$store.dispatch("message/showMessage", {
            text:
              "システムエラーが発生しました。お手数ですが、時間を置いて再度お試しください。",
            type: "danger",
            timeout: 6000,
          });
        } else if (val === UNAUTHORIZED) {
          // 認証エラーの場合はログインページへ移動
          // CSRFトークンをリフレッシュ
          await axios.get("/api/refresh-token");
          // ストアの古いuserDataをクリア
          this.$store.commit("auth/setUserData", null);
          // ログイン画面へ遷移
          // this.$router.push("/login");
          this.$router.push({ name: "login" });
        } else if (val === NOT_FOUND) {
          // 404エラーの場合はNotFoundページを表示
          // this.$router.push("/not-found");
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
