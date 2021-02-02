<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">ログイン</h2>
    <div class="c-form__wrapper">
      <form class="c-form--small" @submit.prevent="checkForm">
        <div class="c-form__group">
          <label for="login-email" class="c-form__label">
            メールアドレス
          </label>
          <input
            type="email"
            class="c-input c-input--large c-input--box"
            id="login-email"
            v-model="loginForm.email"
            required
            autocomplete="email"
            autofocus
          />
          <ul v-if="emailErrors">
            <li
              v-for="error in emailErrors"
              :key="error"
              class="c-valid__error"
            >
              {{ error }}
            </li>
          </ul>
          <ul v-if="loginErrors && loginErrors.email">
            <li
              v-for="error in loginErrors.email"
              :key="error"
              class="c-valid__error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="c-form__group">
          <label for="login-password" class="c-form__label">パスワード</label>
          <input
            type="password"
            class="c-input c-input--large c-input--box"
            id="login-password"
            v-model="loginForm.password"
            required
            autocomplete="current-password"
          />
          <ul v-if="passwordErrors">
            <li
              v-for="error in passwordErrors"
              :key="error"
              class="c-valid__error"
            >
              {{ error }}
            </li>
          </ul>
          <ul v-if="loginErrors && loginErrors.password">
            <li
              v-for="error in loginErrors.password"
              :key="error"
              class="c-valid__error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="c-form__group">
          <label class="c-checkbox__label" for="remember">
            <input
              class="c-checkbox__input"
              type="checkbox"
              id="remember"
              v-model="loginForm.remember"
            />
            <span class="c-checkbox__dummyInput"></span>
            <span class="c-checkbox__text">ログイン状態を保持する</span></label
          >
        </div>

        <div class="c-form__button">
          <button type="submit" class="c-btn__main-outline">ログイン</button>
        </div>
      </form>
      <div class="c-form__link">
        <RouterLink :to="{ name: 'password.request' }" class="c-form__link">
          パスワードをお忘れの方はこちら
        </RouterLink>
      </div>
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
        remember: "",
      },
      emailErrors: [],
      passwordErrors: [],
    };
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.auth.apiStatus,
      // authストアのステートを参照し、エラーメッセージを取得
      loginErrors: (state) => state.auth.loginErrorMessages,
    }),
  },
  methods: {
    // フロントエンド側のバリデーションチェック
    checkForm(e) {
      const MSG_EMAIL_EMPTY = "メールアドレスを入力してください";
      const MSG_EMAIL_TYPE = "メールアドレスの形式で入力してください";
      const MSG_EMAIL_MAX = "50文字以内で入力してください";
      const MSG_PASS_EMPTY = "パスワードを入力してください";
      const MSG_PASS_LESS = "パスワードが異なります";

      this.emailErrors = [];
      this.passwordErrors = [];

      // メールアドレスのバリデーション
      if (!this.loginForm.email) {
        // 未入力チェック
        this.emailErrors.push(MSG_EMAIL_EMPTY);
      } else if (this.loginForm.email.length > 50) {
        // 文字数チェック
        this.emailErrors.push(MSG_EMAIL_MAX);
      } else if (!this.validEmail(this.loginForm.email)) {
        // 下記のメソッドで形式チェック
        this.emailErrors.push(MSG_EMAIL_TYPE);
      }
      // パスワードのバリデーション
      if (!this.loginForm.password) {
        // 未入力チェック
        this.passwordErrors.push(MSG_PASS_EMPTY);
      } else if (this.loginForm.password.length < 8) {
        // 文字数チェック
        this.passwordErrors.push(MSG_PASS_LESS);
      }
      // エラーメッセージを格納した配列を全て結合
      const results = this.emailErrors.concat(this.passwordErrors);
      // エラーメッセージがなければユーザー登録WebAPIを呼び出す
      if (!results.length) {
        this.login();
      }
    },
    // メールアドレス形式チェック
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },

    // ログインWebAPI呼び出し
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
        // VueRouterのpush()でホーム画面へ遷移
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
