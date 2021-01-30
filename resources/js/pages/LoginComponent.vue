<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">ログイン</h2>
    <div class="c-form__wrapper">
      <form class="c-form--small" @submit.prevent="login">
        <div v-if="loginErrors" class="errors">
          <ul v-if="loginErrors.email">
            <li v-for="message in loginErrors.email" :key="message">
              {{ message }}
            </li>
          </ul>
          <ul v-if="loginErrors.password">
            <li v-for="message in loginErrors.password" :key="message">
              {{ message }}
            </li>
          </ul>
        </div>
        <div class="c-form__group">
          <label for="login-email">メールアドレス</label>
          <input
            type="text"
            class="c-input c-input--large c-input--box"
            id="login-email"
            v-model="loginForm.email"
            autocomplete
          />
        </div>
        <div class="c-form__group">
          <label for="login-password">パスワード</label>
          <input
            type="password"
            class="c-input c-input--large c-input--box"
            id="login-password"
            v-model="loginForm.password"
            autocomplete
          />
        </div>

        <div class="c-form__button">
          <button type="submit" class="c-btn__main-outline">ログイン</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex"; // VuexのmapState関数をインポート

export default {
  data() {
    return {
      // v-modelでフォームの入力値と紐付けるデータ変数
      loginForm: {
        email: "",
        password: "",
      },
    };
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.auth.apiStatus,
      // authストアのステートを参照し、エラーメッセージを取得
      loginErrors: (state) => state.auth.loginErrorMessages,
    }),
    // apiStatus() {
    //   // authストアのステートを参照し、API通信の成否ステータスを取得
    //   return this.$store.state.auth.apiStatus;
    // },
    // loginErrors() {
    //   // authストアのステートを参照し、エラーメッセージを取得
    //   return this.$store.state.auth.loginErrorMessages;
    // },
  },
  methods: {
    async login() {
      // dispatch()でauthストアのloginアクションを呼び出す
      await this.$store.dispatch("auth/login", this.loginForm);

      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "ログインしました",
          type: "success",
          timeout: 2000,
        });

        // awaitで非同期アクションの完了を待ってからVueRouterのpush()で遷移
        this.$router.push({ name: "home" });
      }
    },
    clearError() {
      this.$store.commit("auth/setLoginErrorMessages", null);
    },
  },
  created() {
    // ページ読み込み時にエラーメッセージをクリア
    this.clearError();
  },
};
</script>
