<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">お問い合わせ</h2>
    <div class="c-form__wrapper">
      <form class="c-form--large" @submit.prevent="checkForm">
        <p v-if="isConfirm" class="c-form__text">
          下記の内容で送信してよろしいですか？<br />
        </p>
        <p v-else class="c-form__text">
          CryptoTrendについて、ご不明な点がありましたら<br class="u-sp--hidden"/>
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
            <ul v-if="nameErrors">
              <li
                v-for="error in nameErrors"
                :key="error"
                class="c-valid__error"
              >
                {{ error }}
              </li>
            </ul>
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
            <ul v-if="emailErrors">
              <li
                v-for="error in emailErrors"
                :key="error"
                class="c-valid__error"
              >
                {{ error }}
              </li>
            </ul>
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
            <ul v-if="messageErrors">
              <li
                v-for="error in messageErrors"
                :key="error"
                class="c-valid__error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
        </div>
        <div v-if="isConfirm" class="c-form__button">
          <button
            type="button"
            class="c-btn__danger"
            @click.prevent="isConfirm = false"
          >
            内容を修正する
          </button>
          <button type="submit" class="c-btn__main">送信する</button>
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

export default {
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
      apiMessage: "",
      apiResult: "",
      isConfirm: false,
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
          this.sendConfirm();
        } else {
          // 入力フォームからの送信の場合は入力内容確認メソッドを実行
          this.sendConfirm();
        }
      }
    },
    // メールアドレス形式チェック
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },

    // お問い合わせフォーム確認WebAPI呼び出し
    async sendConfirm() {
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
        this.contactForm.name = this.data.name;
        this.contactForm.email = this.data.email;
        this.contactForm.message = this.data.message;

        // // VueRouterのpush()でホーム画面へ遷移
        // this.$router.push({ name: "home" });
      } else {
        // その他の失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
        // 別モジュールのミューテーションをcommitするためroot: trueを指定する
        this.$store.commit("error/setCode", response.status);
      }
    },
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
