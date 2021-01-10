<template>
  <div class="container">
    <form class="form" @submit.prevent="login">
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
      <div class="form-group">
        <label for="login-email">Emailアドレス</label>
        <input
          type="text"
          class="form__item"
          id="login-email"
          v-model="loginForm.email"
          autocomplete
        />
      </div>
      <div class="form-group">
        <label for="login-password">パスワード</label>
        <input
          type="password"
          class="form__item"
          id="login-password"
          v-model="loginForm.password"
          autocomplete
        />
      </div>

      <div class="form__button">
        <button type="submit" class="btn btn-primary">ログイン</button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loginForm: {
        email: "",
        password: "",
      },
    };
  },
  computed: {
    apiStatus() {
      // ストアのapiStatusステートを参照し、API通信の成否ステータスを取得
      return this.$store.state.auth.apiStatus;
    },
    loginErrors() {
      // ストアのloginErrorMessagesステートを参照し、エラーメッセージを取得
      return this.$store.state.auth.loginErrorMessages;
    },
  },
  methods: {
    async login() {
      // dispatch()でauthストアのloginアクションを呼び出す
      await this.$store.dispatch("auth/login", this.loginForm);

      // API通信が成功した場合
      if (this.apiStatus) {
        // awaitで非同期アクションの完了を待ってからVueRouterのpush()で遷移
        this.$router.push("/home");
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
