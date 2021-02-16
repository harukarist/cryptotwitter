<template>
  <form class="c-form--large" @submit.prevent="checkForm">
    <p class="c-form__text">
      CryptoTrendについて、ご不明な点がありましたら<br class="u-sp--hidden" />
      <a href="/#faq">よくあるご質問</a>をご確認ください。<br />
      お問い合わせは下記のフォームにてお送りください。<br />
    </p>
    <div class="c-form__group">
      <label for="name" class="c-form__label">
        お名前<span class="c-form__label--required">必須</span>
      </label>
      <div>
        <input
          type="text"
          class="c-input c-input--large"
          :class="{ 'is-invalid': nameErrors.length }"
          id="name"
          placeholder="山田 太郎"
          v-model="formData.name"
          required
        />
        <invalid-component :messages="nameErrors" />
        <invalid-component
          v-if="apiMessages && apiMessages.name"
          :messages="apiMessages.name"
        />
      </div>
    </div>
    <div class="c-form__group">
      <label for="email" class="c-form__label">
        メールアドレス<span class="c-form__label--required">必須</span>
      </label>
      <div>
        <input
          type="email"
          class="c-input c-input--large"
          id="email"
          placeholder="例）your.email@example.com"
          :class="{ 'is-invalid': emailErrors.length }"
          v-model="formData.email"
          autocomplete="email"
          required
        />
        <invalid-component :messages="emailErrors" />
        <invalid-component
          v-if="apiMessages && apiMessages.email"
          :messages="apiMessages.email"
        />
      </div>
    </div>

    <div class="c-form__group">
      <label for="message" class="c-form__label">
        お問い合わせ内容<span class="c-form__label--required">必須</span>
      </label>
      <div>
        <textarea
          name="message"
          id="message"
          class="c-input c-input__textarea c-input--large"
          :class="{ 'is-invalid': messageErrors.length }"
          v-model="formData.message"
          placeholder="お問い合わせ内容を入力してください"
          required
        >
        </textarea>
        <invalid-component :messages="messageErrors" />
        <invalid-component
          v-if="apiMessages && apiMessages.message"
          :messages="apiMessages.message"
        />
      </div>
    </div>
    <div class="c-form__button">
      <button type="submit" class="c-btn--main c-btn--large">
        入力内容を確認
      </button>
    </div>
  </form>
</template>

<script>
import { OK, UNPROCESSABLE_ENTITY } from "../utility";
import { mapState } from "vuex"; // VuexのmapState関数をインポート
import InvalidComponent from "../components/InvalidComponent.vue";

export default {
  components: {
    InvalidComponent, //バリデーションメッセージ表示用コンポーネント
  },
  props: {
    // v-modelでフォームの入力値と紐付けるデータ変数
    formData: {
      type: Object,
      required: true,
      //オブジェクトの初期値を関数で指定する
      default: function () {
        return {
          name: "",
          email: "",
          message: "",
        };
      },
    },
  },
  data() {
    return {
      // フロントエンド側のバリデーションエラーメッセージ
      nameErrors: [],
      emailErrors: [],
      messageErrors: [],
      // サーバー側のバリデーションエラーメッセージ
      apiMessages: [],
      apiResult: "",
    };
  },
  methods: {
    // フロントエンド側のバリデーションチェック
    checkForm(e) {
      const MSG_NAME_EMPTY = "お名前を入力してください";
      const MSG_MESSAGE_EMPTY = "お問い合わせを入力してください";
      const MSG_EMAIL_EMPTY = "メールアドレスを入力してください";
      const MSG_EMAIL_TYPE = "メールアドレスの形式で入力してください";
      const MSG_EMAIL_MAX = "50文字以内で入力してください";

      this.errorMessages = "";

      // お名前のバリデーション
      if (!this.formData.name) {
        // 未入力チェック
        this.nameErrors.push(MSG_NAME_EMPTY);
      }
      // メッセージのバリデーション
      if (!this.formData.message) {
        // 未入力チェック
        this.messageErrors.push(MSG_MESSAGE_EMPTY);
      }

      // メールアドレスのバリデーション
      if (!this.formData.email) {
        // 未入力チェック
        this.emailErrors.push(MSG_EMAIL_EMPTY);
      } else if (this.formData.email.length > 50) {
        // 文字数チェック
        this.emailErrors.push(MSG_EMAIL_MAX);
      } else if (!this.validEmail(this.formData.email)) {
        // 下記のメソッドで形式チェック
        this.emailErrors.push(MSG_EMAIL_TYPE);
      }
      // エラーメッセージを格納した配列を全て結合
      const results = this.nameErrors.concat(
        this.emailErrors,
        this.messageErrors
      );
      // エラーメッセージがなければユーザー登録WebAPIを呼び出す
      if (!results.length) {
        // 入力フォームからの送信の場合は入力内容確認メソッドを実行
        this.confirmContact();
      }
    },
    // メールアドレス形式チェック
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },

    // お問い合わせフォーム確認WebAPI呼び出し
    async confirmContact() {
      // サーバー側バリデーションメッセージをクリア
      this.apiMessages = [];
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      // 引数にv-modelの値を渡してサーバーのAPIを呼び出し
      const response = await axios.post("/api/contact/confirm", this.formData);
      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ

      // API通信が成功した場合
      if (response.status === OK) {
        // サーバーからの返却値をv-modelに格納してフォームに表示
        this.formData.name = response.data.name;
        this.formData.email = response.data.email;
        this.formData.message = response.data.message;
        // $emitで親コンポーネントに通知して確認画面を表示
        this.$emit("confirm", this.formData);
      }
      // レスポンスのステータスがバリデーションエラーの場合はエラーメッセージを表示
      if (response.status === UNPROCESSABLE_ENTITY) {
        // サーバーから返却されたエラーメッセージを格納
        this.apiMessages = response.data.errors;
        return;
      } else {
        // その他の失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
        // 別モジュールのミューテーションをcommitするためroot: trueを指定する
        this.$store.commit("error/setCode", response.status);
      }
    },
  },
};
</script>
