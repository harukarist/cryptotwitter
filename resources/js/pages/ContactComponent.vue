<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">お問い合わせ</h2>
    <div class="c-form__wrapper">
      <form class="c-form--large" @submit.prevent="checkForm">
        <p v-if="isConfirm" class="c-form__text">
          下記の内容で送信してよろしいですか？<br />
        </p>
        <p v-else class="c-form__text">
          CryptoTrendについて、ご不明な点がありましたら<br
            class="u-sp--hidden"
          />
          <a href="/#faq">よくあるご質問</a>をご確認ください。<br />
          お問い合わせは下記のフォームにてお送りください。<br />
        </p>
        <div class="c-form__group">
          <label for="name" class="c-form__label">
            お名前<span class="c-form__label--required">必須</span>
          </label>
          <div v-if="isConfirm" class="c-form__confirm-text">
            {{ contactForm.name }}
          </div>
          <div v-else>
            <input
              type="text"
              class="c-input c-input--large"
              :class="{ 'is-invalid': nameErrors.length }"
              id="name"
              placeholder="山田 太郎"
              v-model="contactForm.name"
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
          <div v-if="isConfirm" class="c-form__confirm-text">
            {{ contactForm.email }}
          </div>
          <div v-else>
            <input
              type="email"
              class="c-input c-input--large"
              id="email"
              placeholder="例）your.email@example.com"
              :class="{ 'is-invalid': emailErrors.length }"
              v-model="contactForm.email"
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
          <div v-if="isConfirm" class="c-form__confirm-text">
            {{ contactForm.message }}
          </div>
          <div v-else>
            <textarea
              name="message"
              id="message"
              class="c-input c-input__textarea c-input--large"
              :class="{ 'is-invalid': messageErrors.length }"
              v-model="contactForm.message"
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
        <div v-if="isConfirm" class="c-form__button">
          <button type="submit" class="c-btn__main u-mb--m">送信する</button>
          <button
            type="button"
            class="c-btn--danger"
            @click.prevent="isConfirm = false"
          >
            内容を修正する
          </button>
        </div>
        <div v-else class="c-form__button">
          <button type="submit" class="c-btn__main">入力内容を確認</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { OK, UNPROCESSABLE_ENTITY } from "../utility";
import { mapState } from "vuex"; // VuexのmapState関数をインポート
import InvalidComponent from "../components/InvalidComponent.vue";

export default {
  components: {
    InvalidComponent,
  },
  data() {
    return {
      // v-modelでフォームの入力値と紐付けるデータ変数
      contactForm: {
        name: "",
        email: "",
        message: "",
      },
      // フロントエンド側のバリデーションエラーメッセージ
      nameErrors: [],
      emailErrors: [],
      messageErrors: [],
      // サーバー側のバリデーションエラーメッセージ
      apiMessages: [],
      apiResult: "",
      isConfirm: false,
      isSent: false,
    };
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、ユーザーデータを取得
      userData: (state) => state.auth.userData,
    }),
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
      if (!this.contactForm.name) {
        // 未入力チェック
        this.nameErrors.push(MSG_NAME_EMPTY);
      }
      // メッセージのバリデーション
      if (!this.contactForm.message) {
        // 未入力チェック
        this.messageErrors.push(MSG_MESSAGE_EMPTY);
      }

      // メールアドレスのバリデーション
      if (!this.contactForm.email) {
        // 未入力チェック
        this.emailErrors.push(MSG_EMAIL_EMPTY);
      } else if (this.contactForm.email.length > 50) {
        // 文字数チェック
        this.emailErrors.push(MSG_EMAIL_MAX);
      } else if (!this.validEmail(this.contactForm.email)) {
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
        // 確認画面からの送信の場合は送信メソッドを実行
        if (this.isConfirm) {
          this.sendContact();
        } else {
          // 入力フォームからの送信の場合は入力内容確認メソッドを実行
          this.confirmContact();
        }
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
      // サーバーのAPIを呼び出し
      const response = await axios.post(
        "/api/contact/confirm",
        this.contactForm
      );
      console.log(response);

      // API通信が成功した場合
      if (response.status === OK) {
        // 確認フラグをtrueにしてフォームタイプをhiddenに変更
        this.isConfirm = true;
        // サーバーからの返却値をv-modelに格納してフォームに表示
        this.contactForm.name = response.data.name;
        this.contactForm.email = response.data.email;
        this.contactForm.message = response.data.message;
        return;
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

    // お問い合わせフォーム送信WebAPI呼び出し
    async sendContact() {
      // サーバー側バリデーションメッセージをクリア
      this.apiMessages = [];
      // サーバーのAPIを呼び出し
      const response = await axios.post("/api/contact/send", this.contactForm);
      console.log(response);

      // API通信が成功した場合
      if (response.status === OK) {
        // 送信完了フラグをtrueにして送信完了メッセージを表示
        this.isConfirm = false;
        this.isSent = true;
        return;
      }
      // レスポンスのステータスがバリデーションエラーの場合はエラーメッセージを表示
      if (response.status === UNPROCESSABLE_ENTITY) {
        // サーバーから返却されたエラーメッセージを格納
        this.apiMessages = response.data.errors;
        // 確認フラグをfalseにしてフォームを再表示
        this.isConfirm = false;
        return;
      } else {
        // その他の失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
        // 別モジュールのミューテーションをcommitするためroot: trueを指定する
        this.$store.commit("error/setCode", response.status);
        // 確認フラグをfalseにしてフォームを再表示
        this.isConfirm = false;
      }
    },
    // ユーザー情報をお問い合わせフォームに表示
    setUserData() {
      // DBに登録されたユーザー情報があればお問い合わせフォームのv-modelに格納
      if (this.userData) {
        this.contactForm.name = this.userData.name;
        this.contactForm.email = this.userData.email;
      }
    },
  },
  created() {
    // ページ読み込み時にユーザー情報があればフォームに表示
    this.setUserData();
  },
};
</script>
