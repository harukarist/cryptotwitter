<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">
      パスワードをお忘れの方
    </h2>

    <div class="c-form__wrapper">
      <form
        class="c-form--large"
        @submit.prevent="checkForm"
      >
        <div v-if="isSent">
          <p class="c-form__text">
            パスワード変更ページのURLを<br
              class="u-sp--hidden"
            >メールアドレスに送信しました。<br>
            メールに記載された内容にしたがって、<br
              class="u-sp--hidden"
            >パスワードの再設定をお願いいたします。<br>
          </p>
        </div>
        <div v-else>
          <p class="c-form__text">
            ユーザー登録時にご登録いただいたメールアドレスを<br
              class="u-sp--hidden"
            >ご入力ください。<br>
            メールアドレス宛に、パスワード変更ページのURLが<br
              class="u-sp--hidden"
            >記載されたメールが送信されます。<br>
          </p>
          <div class="c-form__group">
            <label
              for="login-email"
              class="c-form__label"
            >
              メールアドレス
            </label>
            <input
              id="email"
              v-model="requestForm.email"
              type="text"
              class="c-input c-input--large"
              required
              autocomplete="email"
            >
            <invalid-component :messages="emailErrors" />
            <transition name="popup">
              <p
                v-if="apiMessage"
                class="u-mb--l c-alert--danger"
              >
                {{ apiMessage }}
              </p>
            </transition>
          </div>

          <div class="c-form__button">
            <button
              type="submit"
              class="c-btn--main-outline c-btn--large"
            >
              送信する
            </button>
          </div>
        </div>
      </form>
      <div class="c-form__link">
        <router-link
          :to="{ name: 'login' }"
          class="c-form__link"
        >
          ログインページへ戻る
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
      requestForm: {
        email: "",
      },
      // フロントエンド側のバリデーションエラーメッセージ
      emailErrors: [],
      // サーバー側からのメッセージとステータス
      apiMessage: "",
      isSent: false,
    };
  },
  methods: {
    /**
     * フロントエンド側のバリデーションチェック
     */
    checkForm() {
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
      // エラーメッセージがなければパスワードリセットメール送信WebAPIを呼び出す
      if (!this.emailErrors.length) {
        this.sendResetLink();
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
     * パスワードリセットメール送信WebAPI呼び出し
     */
    async sendResetLink() {
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      // サーバーのAPIを呼び出し
      const response = await axios.post(
        "/api/password/email",
        this.requestForm
      );
      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      // API通信が成功した場合
      if (response.status === OK) {
        // Laravel側で設定したresultフラグを取得
        const result = response.data.result;
        // メール送信が完了した場合
        if (result === "success") {
          // フラグをtrueにして送信完了メッセージを表示
          this.isSent = true;
          // ページ最上部までスクロール
          window.scrollTo({
            top: 0,
            behavior: "smooth",
          });
          // メール送信が失敗した場合
        } else if (result === "failed") {
          // Laravel側で設定したエラーメッセージを表示
          this.apiMessage = response.data.message;
        }
      } else {
        // その他の失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
        // 別モジュールのミューテーションをcommitするためroot: trueを指定する
        this.$store.commit("error/setCode", response.status);
      }
    },
  },
};
</script>
