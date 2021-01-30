<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">新規ユーザー登録</h2>
    <div class="c-form__wrapper">
      <form class="c-form--small" @submit.prevent="register">
        <div v-if="registerErrors" class="errors">
          <ul v-if="registerErrors.name">
            <li v-for="message in registerErrors.name" :key="message">
              {{ message }}
            </li>
          </ul>
          <ul v-if="registerErrors.email">
            <li v-for="message in registerErrors.email" :key="message">
              {{ message }}
            </li>
          </ul>
          <ul v-if="registerErrors.password">
            <li v-for="message in registerErrors.password" :key="message">
              {{ message }}
            </li>
          </ul>
        </div>
        <div class="c-form__group">
          <label for="username" class="c-form__label">お名前</label>
          <input
            type="text"
            class="c-input c-input--large c-input--box"
            id="username"
            placeholder="クリプト太郎"
            v-model="registerForm.name"
          />
        </div>
        <div class="c-form__group">
          <label for="email" class="c-form__label">メールアドレス</label>
          <input
            type="text"
            class="c-input c-input--large c-input--box"
            id="email"
            placeholder="例）your.email@example.com"
            v-model="registerForm.email"
          />
        </div>
        <div class="c-form__group">
          <label for="password" class="c-form__label"
            >パスワード
            <span class="c-form__notes">半角英数字6文字以上</span></label
          >
          <input
            type="password"
            class="c-input c-input--large c-input--box"
            id="password"
            placeholder="パスワードを入力"
            v-model="registerForm.password"
            autocomplete
          />
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
            autocomplete
          />
        </div>
        <div class="c-form__info">
          <a href="#">利用規約</a> および
          <a href="#">プライバシポリシー</a> に同意の上、ご登録ください。
        </div>
        <div class="c-form__button">
          <button type="submit" class="c-btn__accent">ユーザー登録</button>
        </div>
      </form>
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
    };
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.auth.apiStatus,
      // authストアのステートを参照し、エラーメッセージを取得
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
