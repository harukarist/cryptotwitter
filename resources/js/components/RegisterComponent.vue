<template>
  <div class="container">
    <form class="form" @submit.prevent="register">
      <div v-if="registerErrors" class="errors">
        <ul v-if="registerErrors.name">
          <li v-for="message in registerErrors.name" :key="message">
            {{ message }}
          </li>
        </ul>
        <ul v-if="registerErrors.email">
          <li v-for="message in registerErrors.email" :key="message">
            {{ message }}
          </li>
        </ul>
        <ul v-if="registerErrors.password">
          <li v-for="message in registerErrors.password" :key="message">
            {{ message }}
          </li>
        </ul>
      </div>
      <div class="form-group">
        <label for="username">お名前</label>
        <input
          type="text"
          class="form__item"
          id="username"
          v-model="registerForm.name"
        />
      </div>
      <div class="form-group">
        <label for="email">Emailアドレス</label>
        <input
          type="text"
          class="form__item"
          id="email"
          v-model="registerForm.email"
        />
      </div>
      <div class="form-group">
        <label for="password">パスワード</label>
        <input
          type="password"
          class="form__item"
          id="password"
          v-model="registerForm.password"
          autocomplete
        />
      </div>
      <div class="form-group">
        <label for="password-confirmation">パスワード（再入力）</label>
        <input
          type="password"
          class="form__item"
          id="password-confirmation"
          v-model="registerForm.password_confirmation"
          autocomplete
        />
      </div>
      <div class="form__button">
        <button type="submit" class="btn btn-primary">ユーザー登録</button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      registerForm: {
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
      },
    };
  },
  computed: {
    apiStatus() {
      // ストアのapiStatusステートを参照し、API通信の成否ステータスを取得
      return this.$store.state.auth.apiStatus;
    },
    registerErrors() {
      // ストアのloginErrorMessagesステートを参照し、エラーメッセージを取得
      return this.$store.state.auth.registerErrorMessages;
    },
  },
  methods: {
    async register() {
      // dispatch()でauthストアのresigterアクションを呼び出す
      await this.$store.dispatch("auth/register", this.registerForm);

      // API通信が成功した場合
      if (this.apiStatus) {
        // awaitで非同期アクションの完了を待ってからVueRouterのpush()で遷移
        this.$router.push("/home");
      }
    },
    clearError() {
      this.$store.commit("auth/setRegisterErrorMessages", null);
    },
  },
  created() {
    // ページ読み込み時にエラーメッセージをクリア
    this.clearError();
  },
};
</script>
