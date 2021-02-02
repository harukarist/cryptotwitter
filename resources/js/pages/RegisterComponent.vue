<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">新規ユーザー登録</h2>
    <div class="c-form__wrapper">
      <form class="c-form--small" @submit.prevent="checkForm">
        <div class="c-form__group">
          <label for="username" class="c-form__label">
            お名前
            <span class="c-form__notes">20文字以内</span>
          </label>
          <input
            type="text"
            class="c-input c-input--large c-input--box"
            id="username"
            placeholder="クリプト太郎"
            v-model="registerForm.name"
            required
            autocomplete="name"
            autofocus
          />
          <ul v-if="nameErrors">
            <li v-for="error in nameErrors" :key="error" class="c-valid__error">
              {{ error }}
            </li>
          </ul>
          <ul v-if="registerErrors && registerErrors.name">
            <li
              v-for="error in registerErrors.name"
              :key="error"
              class="c-valid__error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="c-form__group">
          <label for="email" class="c-form__label">メールアドレス</label>
          <input
            type="email"
            class="c-input c-input--large c-input--box"
            id="email"
            placeholder="例）your.email@example.com"
            v-model="registerForm.email"
            required
            autocomplete="email"
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
          <ul v-if="registerErrors && registerErrors.email">
            <li
              v-for="error in registerErrors.email"
              :key="error"
              class="c-valid__error"
            >
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
            v-model="registerForm.password"
            required
            autocomplete="new-password"
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
          <ul v-if="registerErrors && registerErrors.password">
            <li
              v-for="error in registerErrors.password"
              :key="error"
              class="c-valid__error"
            >
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
            v-model="registerForm.password_confirmation"
            required
            autocomplete="new-password"
          />
          <ul v-if="confirmErrors">
            <li
              v-for="error in confirmErrors"
              :key="error"
              class="c-valid__error"
            >
              {{ error }}
            </li>
          </ul>
        </div>
        <div class="c-form__info">
          <a href="#">利用規約</a> および
          <a href="#">プライバシポリシー</a> に同意の上、ご登録ください。
        </div>
        <div class="c-form__button">
          <button type="submit" class="c-btn__accent">ユーザー登録</button>
        </div>
      </form>
      <div class="c-form__link">
        <RouterLink :to="{ name: 'login' }" class="c-form__link">
          アカウントをお持ちの方はこちら
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
      registerForm: {
        name: "",
        email: "",
        password: "",
        password_confirmation: "",
      },
      nameErrors: [],
      emailErrors: [],
      passwordErrors: [],
      confirmErrors: [],
    };
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、WebAPIの成否ステータスを取得
      apiStatus: (state) => state.auth.apiStatus,
      // authストアのステートを参照し、WebAPIから返却されるエラーメッセージを取得
      registerErrors: (state) => state.auth.registerErrorMessages,
    }),
    // apiStatus() {
    //   // authストアのステートを参照し、API通信の成否ステータスを取得
    //   return this.$store.state.auth.apiStatus;
    // },
    // registerErrors() {
    //   // authストアのステートを参照し、エラーメッセージを取得
    //   return this.$store.state.auth.registerErrorMessages;
    // },
  },
  methods: {
    // フロントエンド側のバリデーションチェック
    checkForm(e) {
      const MSG_NAME_EMPTY = "お名前を入力してください";
      const MSG_NAME_MAX = "20文字以内で入力してください";
      const MSG_EMAIL_EMPTY = "メールアドレスを入力してください";
      const MSG_EMAIL_TYPE = "メールアドレスの形式で入力してください";
      const MSG_EMAIL_MAX = "50文字以内で入力してください";
      const MSG_PASS_EMPTY = "パスワードを入力してください";
      const MSG_PASS_LESS = "8文字以上で入力してください";
      const MSG_RETYPE = "パスワードが一致していません";

      this.nameErrors = [];
      this.emailErrors = [];
      this.passwordErrors = [];
      this.confirmErrors = [];

      // 名前のバリデーション
      if (!this.registerForm.name) {
        // 未入力チェック
        this.nameErrors.push(MSG_NAME_EMPTY);
      } else if (this.registerForm.name.length > 20) {
        // 文字数チェック
        this.nameErrors.push(MSG_NAME_MAX);
      }
      // メールアドレスのバリデーション
      if (!this.registerForm.email) {
        // 未入力チェック
        this.emailErrors.push(MSG_EMAIL_EMPTY);
      } else if (this.registerForm.email.length > 50) {
        // 文字数チェック
        this.emailErrors.push(MSG_EMAIL_MAX);
      } else if (!this.validEmail(this.registerForm.email)) {
        // 下記のメソッドで形式チェック
        this.emailErrors.push(MSG_EMAIL_TYPE);
      }
      // パスワードのバリデーション
      if (!this.registerForm.password) {
        // 未入力チェック
        this.passwordErrors.push(MSG_PASS_EMPTY);
      } else if (this.registerForm.password.length < 8) {
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
      const results = this.nameErrors.concat(
        this.emailErrors,
        this.passwordErrors,
        this.confirmErrors
      );
      // エラーメッセージがなければユーザー登録WebAPIを呼び出す
      if (!results.length) {
        this.register();
      }
    },
    // メールアドレス形式チェック
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },

    // ユーザー登録WebAPI呼び出し
    async register() {
      // dispatch()でauthストアのresigterアクションを呼び出す
      await this.$store.dispatch("auth/register", this.registerForm);
      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        await this.$store.dispatch("message/showMessage", {
          text: "ユーザー登録が完了しました！",
          type: "success",
          timeout: 4000,
        });
        // VueRouterのpush()でホーム画面へ遷移
        this.$router.push({ name: "home" });
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
