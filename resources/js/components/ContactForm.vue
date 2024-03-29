<template>
  <form
    class="c-form--large"
    @submit.prevent="checkForm"
  >
    <p class="c-form__text">
      CryptoTrendについて、ご不明な点がありましたら<br class="u-sp--hidden">
      <a href="/#faq">よくあるご質問</a>をご確認ください。<br>
      お問い合わせは下記のフォームにてお送りください。<br>
    </p>
    <div class="c-form__group">
      <label
        for="name"
        class="c-form__label"
      >
        お名前<span class="c-form__label--required">必須</span>
      </label>
      <div>
        <input
          id="name"
          v-model="innerData.name"
          type="text"
          class="c-input c-input--large"
          :class="{ 'is-invalid': nameErrors.length }"
          placeholder="山田 太郎"
          required
        >
        <invalid-component :messages="nameErrors" />
        <invalid-component
          v-if="apiMessages && apiMessages.name"
          :messages="apiMessages.name"
        />
      </div>
    </div>
    <div class="c-form__group">
      <label
        for="email"
        class="c-form__label"
      >
        メールアドレス<span class="c-form__label--required">必須</span>
      </label>
      <div>
        <input
          id="email"
          v-model="innerData.email"
          type="email"
          class="c-input c-input--large"
          placeholder="例）your.email@example.com"
          :class="{ 'is-invalid': emailErrors.length }"
          autocomplete="email"
          required
        >
        <invalid-component :messages="emailErrors" />
        <invalid-component
          v-if="apiMessages && apiMessages.email"
          :messages="apiMessages.email"
        />
      </div>
    </div>

    <div class="c-form__group">
      <label
        for="message"
        class="c-form__label"
      >
        お問い合わせ内容<span class="c-form__label--required">必須</span>
      </label>
      <div>
        <textarea
          id="message"
          v-model="innerData.message"
          name="message"
          class="c-input c-input__textarea c-input--large"
          :class="{ 'is-invalid': messageErrors.length }"
          placeholder="お問い合わせ内容を入力してください"
          required
        />
        <invalid-component :messages="messageErrors" />
        <invalid-component
          v-if="apiMessages && apiMessages.message"
          :messages="apiMessages.message"
        />
      </div>
    </div>
    <div class="c-form__button">
      <button
        type="submit"
        class="c-btn--main c-btn--large"
      >
        入力内容を確認
      </button>
    </div>
  </form>
</template>

<script>
import { OK, UNPROCESSABLE_ENTITY } from "../utility";
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
  computed: {
    innerData: {
      get() {
        return this.formData;
      },
      set() {
        this.checkForm();
      },
    },
  },
  methods: {
    /**
     * フロントエンド側のバリデーションチェック
     */
    checkForm() {
      const MSG_NAME_EMPTY = "お名前を入力してください";
      const MSG_NAME_MAX = "20文字以内で入力してください";
      const MSG_EMAIL_EMPTY = "メールアドレスを入力してください";
      const MSG_EMAIL_TYPE = "メールアドレスの形式で入力してください";
      const MSG_EMAIL_MAX = "50文字以内で入力してください";
      const MSG_MESSAGE_EMPTY = "お問い合わせを入力してください";
      const MSG_MESSAGE_MAX =
        "お問い合わせ内容は1000文字以内で入力してください";

      this.nameErrors = [];
      this.emailErrors = [];
      this.messageErrors = [];

      // お名前のバリデーション
      if (!this.innerData.name) {
        // 未入力チェック
        this.nameErrors.push(MSG_NAME_EMPTY);
      } else if (this.innerData.name.length > 20) {
        // 文字数チェック
        this.nameErrors.push(MSG_NAME_MAX);
      }

      // メッセージのバリデーション
      if (!this.innerData.message) {
        // 未入力チェック
        this.messageErrors.push(MSG_MESSAGE_EMPTY);
      } else if (this.innerData.message.length > 1000) {
        // 文字数チェック
        this.messageErrors.push(MSG_MESSAGE_MAX);
      }

      // メールアドレスのバリデーション
      if (!this.innerData.email) {
        // 未入力チェック
        this.emailErrors.push(MSG_EMAIL_EMPTY);
      } else if (this.innerData.email.length > 50) {
        // 文字数チェック
        this.emailErrors.push(MSG_EMAIL_MAX);
      } else if (!this.validEmail(this.innerData.email)) {
        // 下記のメソッドで形式チェック
        this.emailErrors.push(MSG_EMAIL_TYPE);
      }
      // エラーメッセージを格納した配列を全て結合
      const results = this.nameErrors.concat(
        this.emailErrors,
        this.messageErrors
      );
      // エラーメッセージがなければお問い合わせフォーム確認WebAPIを呼び出す
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

    /**
     * お問い合わせフォーム確認WebAPI呼び出し
     */
    async confirmContact() {
      // サーバー側バリデーションメッセージをクリア
      this.apiMessages = [];
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      // 引数にv-modelの値を渡してサーバーのAPIを呼び出し
      const response = await axios.post("/api/contact/confirm", this.innerData);
      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ

      // API通信が成功した場合
      if (response.status === OK) {
        // サーバーからの返却値をv-modelに格納してフォームに表示
        this.innerData.name = response.data.name;
        this.innerData.email = response.data.email;
        this.innerData.message = response.data.message;
        // $emitで親コンポーネントに通知して確認画面を表示
        this.$emit("confirm", this.innerData);
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
