<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">パスワードの再設定</h2>
    <div class="c-form__wrapper">
      <form class="c-form--small" @submit.prevent="checkForm">
        <input v-model="resetForm.token" type="hidden" name="token" />
        <p class="c-section__text">
          新しいパスワードを設定してください。<br />
        </p>
        <div class="c-form__group">
          <label for="login-email" class="c-form__label">
            メールアドレス
          </label>
          <input
            id="email"
            v-model="resetForm.email"
            type="text"
            class="c-input c-input--large c-input--box"
            required
            autocomplete="email"
          />
          <invalid-component :messages="emailErrors" />
        </div>
        <transition name="popup">
          <p v-if="apiMessage" class="u-mb--l c-alert--danger">
            {{ apiMessage }}
          </p>
        </transition>
        <div class="c-form__group">
          <label for="password" class="c-form__label">
            パスワード
            <span class="c-form__notes">半角英数字8文字以上</span>
          </label>
          <input
            id="password"
            v-model="resetForm.password"
            type="password"
            class="c-input c-input--large c-input--box"
            placeholder="パスワードを入力"
            required
            autocomplete="new-password"
          />
          <invalid-component :messages="passwordErrors" />
        </div>
        <div class="c-form__group">
          <label for="password-confirmation" class="c-form__label"
            >パスワード（再入力）</label
          >
          <input
            id="password-confirmation"
            v-model="resetForm.password_confirmation"
            type="password"
            class="c-input c-input--large c-input--box"
            placeholder="パスワードを再度入力"
            required
            autocomplete="new-password"
          />
          <invalid-component :messages="confirmErrors" />
        </div>

        <div class="c-form__button">
          <button type="submit" class="c-btn--main-outline c-btn--large">
            送信する
          </button>
        </div>
      </form>
      <div class="c-form__link">
        <router-link :to="{ name: 'password.request' }" class="c-form__link">
          メールアドレスを再度入力する
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { OK } from "../utility";
import InvalidComponent from "../components/InvalidComponent.vue";

export default {
  components: {
    InvalidComponent, //バリデーションメッセージ表示用コンポーネント
  },
  data() {
    return {
      // v-modelでフォームの入力値と紐付けるデータ変数
      resetForm: {
        email: "",
        password: "",
        password_confirmation: "",
        token: "",
      },
      emailErrors: [],
      passwordErrors: [],
      confirmErrors: [],
      apiMessage: "",
    };
  },
  created() {
    // ページ読み込み時にURLのトークンを格納
    this.resetForm.token = this.$route.params["token"];
    this.resetForm.email = this.$route.query.email;
  },
  methods: {
    /**
     * フロントエンド側のバリデーションチェック
     */
    checkForm() {
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
      if (!this.resetForm.password) {
        // 未入力チェック
        this.passwordErrors.push(MSG_PASS_EMPTY);
      } else if (this.resetForm.password.length < 8) {
        // 文字数チェック
        this.passwordErrors.push(MSG_PASS_LESS);
      }
      // パスワード再入力のバリデーション
      if (!this.resetForm.password_confirmation) {
        // 未入力チェック
        this.confirmErrors.push(MSG_PASS_EMPTY);
      } else if (this.resetForm.password_confirmation.length < 8) {
        // 文字数チェック
        this.confirmErrors.push(MSG_PASS_LESS);
      } else if (
        this.resetForm.password !== this.resetForm.password_confirmation
      ) {
        // パスワード一致チェック
        this.confirmErrors.push(MSG_RETYPE);
      }
      // エラーメッセージを格納した配列を全て結合
      const results = this.emailErrors.concat(
        this.passwordErrors,
        this.confirmErrors
      );
      // エラーメッセージがなければパスワードリセット処理WebAPIを呼び出す
      if (!results.length) {
        this.resetPassword();
      }
    },

    /**
     * メールアドレス形式チェック
     */
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },

    /**
     * パスワードリセット処理WebAPI呼び出し
     */
    async resetPassword() {
      // サーバーのAPIを呼び出し
      const response = await axios.post("/api/password/reset", this.resetForm);

      // API通信が成功した時
      if (response.status === OK) {
        // Laravel側で設定したresultフラグを取得
        const result = response.data.result;

        // パスワード変更が完了した場合
        if (result === "success") {
          // ログイン処理を実行
          // setUserDataミューテーションでuserDataステートを更新
          this.$store.commit("auth/setUserData", response.data.user);
          // ログインユーザーのTwitterアカウント情報とフォロー済みリストを更新
          this.$store.dispatch("twitter/updateTwitterUser");
          // VueRouterのpush()でホーム画面へ遷移
          this.$router.push({ name: "home" });
          // フラッシュメッセージを表示
          this.$store.dispatch("message/showMessage", {
            text: "パスワードを変更しました",
            type: "success",
            timeout: 2000,
          });
          return;
          // パスワード変更できなかった場合
        } else if (result === "failed") {
          // エラーメッセージを表示
          this.apiMessage = response.data.status;
          return;
        }
      } else {
        // 失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
        this.$store.commit("error/setCode", response.status);
      }
    },
  },
};
</script>
