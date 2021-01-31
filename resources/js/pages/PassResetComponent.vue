<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">パスワードの再設定</h2>
    <div class="c-form__wrapper">
      <form class="c-form--small" @submit.prevent="checkForm">
        <input type="hidden" name="token" :value="token" />
        <p class="c-section__text">
          新しいパスワードを設定してください。<br />
        </p>
        <div class="c-form__group">
          <label for="login-email" class="c-form__label">
            メールアドレス
          </label>
          <input
            type="text"
            class="c-input c-input--large c-input--box"
            id="email"
            required
            v-model="resetForm.email"
            autocomplete="email"
          />
          <ul v-if="emailErrors" class="c-error__item">
            <li v-for="error in emailErrors" :key="error" class="c-error__text">
              {{ error }}
            </li>
          </ul>
          <ul v-if="apiErrors" class="c-error__item">
            <li v-for="error in apiErrors" :key="error" class="c-error__text">
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="c-form__group">
          <label for="password" class="c-form__label">
            パスワード
            <span class="c-form__notes">半角英数字8文字以上</span>
          </label>
          <input
            type="password"
            class="c-input c-input--large c-input--box"
            id="password"
            placeholder="パスワードを入力"
            v-model="resetForm.password"
            required
            autocomplete="new-password"
          />
          <ul v-if="passwordErrors" class="c-error__item">
            <li
              v-for="error in passwordErrors"
              :key="error"
              class="c-error__text"
            >
              {{ error }}
            </li>
          </ul>
          <ul v-if="apiErrors" class="c-error__item">
            <li v-for="error in apiErrors" :key="error" class="c-error__text">
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="c-form__group">
          <label for="password-confirmation" class="c-form__label"
            >パスワード（再入力）</label
          >
          <input
            type="password"
            class="c-input c-input--large c-input--box"
            id="password-confirmation"
            placeholder="パスワードを再度入力"
            v-model="resetForm.password_confirmation"
            required
            autocomplete="new-password"
          />
          <ul v-if="confirmErrors" class="c-error__item">
            <li
              v-for="error in confirmErrors"
              :key="error"
              class="c-error__text"
            >
              {{ error }}
            </li>
          </ul>
        </div>

        <div class="c-form__button">
          <button type="submit" class="c-btn__main-outline">送信する</button>
        </div>
      </form>
      <div class="c-form__link">
        <RouterLink :to="{ name: 'login' }" class="c-form__link">
          ログインページへ戻る
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script>
import { OK, UNPROCESSABLE_ENTITY } from "../utility";

export default {
  data() {
    return {
      // v-modelでフォームの入力値と紐付けるデータ変数
      resetForm: {
        email: "",
        password: "",
      },
      emailErrors: [],
      apiErrors: [],
      token: "",
    };
  },
  methods: {
    // フロントエンド側のバリデーションチェック
    checkForm(e) {
      const MSG_EMAIL_EMPTY = "メールアドレスを入力してください";
      const MSG_EMAIL_TYPE = "メールアドレスの形式で入力してください";
      const MSG_EMAIL_MAX = "50文字以内で入力してください";
      const MSG_PASS_EMPTY = "パスワードを入力してください";
      const MSG_PASS_LESS = "8文字以上で入力してください";
      const MSG_RETYPE = "パスワードが一致していません";

      this.emailErrors = [];
      this.passwordErrors = [];
      this.confirmErrors = [];

      // メールアドレスのバリデーション
      if (!this.resetForm.email) {
        // 未入力チェック
        this.emailErrors.push(MSG_EMAIL_EMPTY);
      } else if (this.resetForm.email.length > 50) {
        // 文字数チェック
        this.emailErrors.push(MSG_EMAIL_MAX);
      } else if (!this.validEmail(this.resetForm.email)) {
        // 下記のメソッドで形式チェック
        this.emailErrors.push(MSG_EMAIL_TYPE);
      }

      // パスワードのバリデーション
      if (!this.registerForm.password) {
        // 未入力チェック
        this.passwordErrors.push(MSG_PASS_EMPTY);
      } else if (this.registerForm.password.length < 6) {
        // 文字数チェック
        this.passwordErrors.push(MSG_PASS_LESS);
      }
      // パスワード再入力のバリデーション
      if (!this.registerForm.password_confirmation) {
        // 未入力チェック
        this.confirmErrors.push(MSG_PASS_EMPTY);
      } else if (this.registerForm.password_confirmation.length < 8) {
        // 文字数チェック
        this.confirmErrors.push(MSG_PASS_LESS);
      } else if (
        this.registerForm.password !== this.registerForm.password_confirmation
      ) {
        // パスワード一致チェック
        this.confirmErrors.push(MSG_RETYPE);
      }
      // エラーメッセージを格納した配列を全て結合
      const results = this.emailErrors.concat(
        this.passwordErrors,
        this.confirmErrors
      );
      // エラーメッセージがなければユーザー登録WebAPIを呼び出す
      if (!results.length) {
        this.resetPassword();
      }
    },
    // メールアドレス形式チェック
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },

    // パスワードリセットWebAPI呼び出し
    async resetPassword() {
      // サーバーのAPIを呼び出し
      const response = await axios.post("/api/password/reset", this.resetForm);
      console.log(response);
      // API通信が成功した場合
      if (response.status === OK) {
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "パスワードを変更しました",
          type: "success",
          timeout: 2000,
        });
        // VueRouterのpush()でホーム画面へ遷移
        this.$router.push({ name: "hoome" });
      }
      // バリデーションエラー時はエラーメッセージのステートを更新
      if (response.status === UNPROCESSABLE_ENTITY) {
        this.apiErrors = response.data.errors;
      } else {
        // その他の失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
        // 別モジュールのミューテーションをcommitするためroot: trueを指定する
        this.$store.commit("error/setCode", response.status);
      }
    },
  },
  created() {
    // ページ読み込み時にURLのトークンを格納
    this.token = this.$route.token;
    // this.token = this.$route.query;
  },
};
</script>
