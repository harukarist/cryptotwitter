<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">パスワードをお忘れの方</h2>
    <div class="c-form__wrapper">
      <form class="c-form--large" @submit.prevent="checkForm">
        <p class="c-form__text">
          ユーザー登録時にご登録いただいたメールアドレスをご入力ください。<br />
          メールアドレス宛に、パスワード変更ページのURLが記載されたメールが送信されます。<br />
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
            v-model="requestForm.email"
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
          <p
            v-if="apiMessage"
            :class="{
              'c-valid__error': apiResult === 'NG',
              'c-valid__success': apiResult === 'OK',
            }"
          >
            {{ apiMessage }}
          </p>
        </div>

        <div class="c-form__button">
          <button type="submit" class="c-btn__main--outline">送信する</button>
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
      requestForm: {
        email: "",
      },
      emailErrors: [],
      apiMessage: "",
      apiResult: "",
    };
  },
  methods: {
    // フロントエンド側のバリデーションチェック
    checkForm(e) {
      const MSG_EMAIL_EMPTY = "メールアドレスを入力してください";
      const MSG_EMAIL_TYPE = "メールアドレスの形式で入力してください";
      const MSG_EMAIL_MAX = "50文字以内で入力してください";

      this.emailErrors = [];

      // メールアドレスのバリデーション
      if (!this.requestForm.email) {
        // 未入力チェック
        this.emailErrors.push(MSG_EMAIL_EMPTY);
      } else if (this.requestForm.email.length > 50) {
        // 文字数チェック
        this.emailErrors.push(MSG_EMAIL_MAX);
      } else if (!this.validEmail(this.requestForm.email)) {
        // 下記のメソッドで形式チェック
        this.emailErrors.push(MSG_EMAIL_TYPE);
      }
      // エラーメッセージがなければユーザー登録WebAPIを呼び出す
      if (!this.emailErrors.length) {
        this.sendResetLink();
      }
    },
    // メールアドレス形式チェック
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },

    // パスワードリセットメール送信WebAPI呼び出し
    async sendResetLink() {
      // サーバーのAPIを呼び出し
      const response = await axios.post(
        "/api/password/email",
        this.requestForm
      );
      console.log(response);
      // API通信が成功した場合
      if (response.status === OK) {
        this.apiMessage = response.data.message;
        this.apiResult = response.data.result;
        // // VueRouterのpush()でホーム画面へ遷移
        // this.$router.push({ name: "home" });
      } else {
        // その他の失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
        // 別モジュールのミューテーションをcommitするためroot: trueを指定する
        this.$store.commit("error/setCode", response.status);
      }
    },
  },
  // created() {
  //   // ページ読み込み時にエラーメッセージをクリア
  //   this.clearError();
  // },
};
</script>
