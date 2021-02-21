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
            id="username"
            v-model="registerForm.name"
            type="text"
            class="c-input c-input--large"
            placeholder="クリプト太郎"
            required
            autocomplete="name"
            autofocus
          />
          <invalid-component :messages="nameErrors" />
          <invalid-component
            v-if="registerErrors && registerErrors.name"
            :messages="registerErrors.name"
          />
        </div>
        <div class="c-form__group">
          <label for="email" class="c-form__label">メールアドレス</label>
          <input
            id="email"
            v-model="registerForm.email"
            type="email"
            class="c-input c-input--large"
            placeholder="例）your.email@example.com"
            required
            autocomplete="email"
          />
          <invalid-component :messages="emailErrors" />
          <invalid-component
            v-if="registerErrors && registerErrors.email"
            :messages="registerErrors.email"
          />
        </div>
        <div class="c-form__group">
          <label for="password" class="c-form__label">
            パスワード
            <span class="c-form__notes">半角英数字8文字以上</span>
          </label>
          <input
            id="password"
            v-model="registerForm.password"
            type="password"
            class="c-input c-input--large"
            placeholder="パスワードを入力"
            required
            autocomplete="new-password"
          />
          <invalid-component :messages="passwordErrors" />
          <invalid-component
            v-if="registerErrors && registerErrors.password"
            :messages="registerErrors.password"
          />
        </div>
        <div class="c-form__group">
          <label for="password-confirmation" class="c-form__label"
            >パスワード（再入力）</label
          >
          <input
            id="password-confirmation"
            v-model="registerForm.password_confirmation"
            type="password"
            class="c-input c-input--large"
            placeholder="パスワードを再度入力"
            required
            autocomplete="new-password"
          />
          <invalid-component :messages="confirmErrors" />
        </div>
        <div class="c-form__info">
          <router-link :to="{ name: 'terms' }"> 利用規約 </router-link>
          および
          <router-link :to="{ name: 'privacy' }">
            プライバシーポリシー
          </router-link>
          に同意の上、ご登録ください。
        </div>
        <div class="c-form__button">
          <button type="submit" class="c-btn--accent c-btn--large">
            ユーザー登録
          </button>
        </div>
      </form>
      <div class="c-form__link">
        <router-link :to="{ name: 'login' }" class="c-form__link">
          アカウントをお持ちの方はこちら
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex"; // VuexのmapState関数をインポート
import InvalidComponent from "../components/InvalidComponent.vue";

export default {
  components: {
    InvalidComponent, //バリデーションメッセージ表示用コンポーネント
  },
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
  },
  created() {
    // ページ読み込み時にエラーメッセージをクリア
    this.clearError();
  },
  methods: {
    // フロントエンド側のバリデーションチェック
    checkForm() {
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
};
</script>
