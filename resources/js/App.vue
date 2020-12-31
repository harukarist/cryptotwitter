<template>
  <div>
    <header>
      <HeaderComponent></HeaderComponent>
    </header>
    <main>
      <div class="container">
        <!-- VueRouterでコンポーネントを表示 -->
        <RouterView></RouterView>
      </div>
    </main>
    <footer>
      <FooterComponent></FooterComponent>
    </footer>
  </div>
</template>

<script>
import HeaderComponent from "./components/HeaderComponent";
import FooterComponent from "./components/FooterComponent";
import { INTERNAL_SERVER_ERROR } from "./utility";

export default {
  components: {
    HeaderComponent,
    FooterComponent,
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
      handler(val) {
        // サーバー内部エラーの場合は500エラーページへ遷移
        if (val === INTERNAL_SERVER_ERROR) {
          this.$router.push("/500");
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
