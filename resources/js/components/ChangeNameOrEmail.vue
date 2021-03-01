<template>
  <form
    class="c-form--small"
    @submit.prevent="checkEditForm"
  >
    <div class="c-form__group">
      <label
        for="username"
        class="c-form__label"
      >
        お名前
        <span class="c-form__notes">20文字以内</span>
      </label>
      <input
        id="username"
        v-model="editForm.name"
        type="text"
        class="c-input c-input--large"
        required
        autocomplete="name"
      >
      <invalid-component :messages="nameErrors" />
      <invalid-component
        v-if="editErrors && editErrors.name"
        :messages="editErrors.name"
      />
    </div>
    <div class="c-form__group">
      <label
        for="email"
        class="c-form__label"
      >メールアドレス</label>
      <input
        id="email"
        v-model="editForm.email"
        type="email"
        class="c-input c-input--large"
        placeholder="例）your.email@example.com"
        required
        autocomplete="email"
      >
      <invalid-component :messages="emailErrors" />
      <invalid-component
        v-if="editErrors && editErrors.email"
        :messages="editErrors.email"
      />
    </div>
    <div class="c-form__button">
      <button
        type="submit"
        class="c-btn--accent c-btn--large"
      >
        アカウント情報を変更
      </button>
    </div>
  </form>
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
      editForm: {
        name: "",
        email: "",
      },
      nameErrors: [],
      emailErrors: [],
      successMessage: "",
    };
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、ユーザーデータを取得
      userData: (state) => state.auth.userData,
      // authストアのステートを参照し、API通信の成否ステータスを取得
      apiStatus: (state) => state.auth.apiStatus,
      // authストアのステートを参照し、エラーメッセージを取得
      editErrors: (state) => state.auth.editErrorMessages,
    }),
  },
  created() {
    // ページ読み込み時にエラーメッセージをクリア
    this.clearError();
    // ページ読み込み時にユーザー情報を編集フォームに表示
    this.setUserData();
  },
  methods: {
    // フロントエンド側のバリデーションチェック
    checkEditForm() {
      const MSG_NAME_EMPTY = "お名前を入力してください";
      const MSG_NAME_MAX = "20文字以内で入力してください";
      const MSG_EMAIL_EMPTY = "メールアドレスを入力してください";
      const MSG_EMAIL_TYPE = "メールアドレスの形式で入力してください";
      const MSG_EMAIL_MAX = "50文字以内で入力してください";
      this.nameErrors = [];
      this.emailErrors = [];

      // 名前のバリデーション
      if (!this.editForm.name) {
        // 未入力チェック
        this.nameErrors.push(MSG_NAME_EMPTY);
      } else if (this.editForm.name.length > 20) {
        // 文字数チェック
        this.nameErrors.push(MSG_NAME_MAX);
      }
      // メールアドレスのバリデーション
      if (!this.editForm.email) {
        // 未入力チェック
        this.emailErrors.push(MSG_EMAIL_EMPTY);
      } else if (this.editForm.email.length > 50) {
        // 文字数チェック
        this.emailErrors.push(MSG_EMAIL_MAX);
      } else if (!this.validEmail(this.editForm.email)) {
        // 下記のメソッドで形式チェック
        this.emailErrors.push(MSG_EMAIL_TYPE);
      }
      // エラーメッセージを格納した配列を全て結合
      const results = this.nameErrors.concat(this.emailErrors);
      // エラーメッセージがなければユーザー情報変更WebAPIを呼び出す
      if (!results.length) {
        this.EditAccount();
      }
    },
    // メールアドレス形式チェック
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },
    
    // ユーザー情報変更WebAPI呼び出し
    async EditAccount() {
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      // dispatch()でauthストアのアクションを呼び出す
      await this.$store.dispatch("auth/EditAccount", this.editForm);
      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      // API通信が成功した場合
      if (this.apiStatus) {
        // フォーム上にサクセスメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "アカウント情報を変更しました",
          type: "success",
          timeout: 2000,
        });
        // エラーメッセージをクリア
        this.nameErrors = [];
        this.emailErrors = [];
      }
    },
    clearError() {
      // エラーメッセージをクリア
      this.$store.commit("auth/setEditErrorMessages", null);
    },
    setUserData() {
      // DBに登録されたユーザー情報を編集フォームのv-modelに格納
      this.editForm.name = this.userData.name;
      this.editForm.email = this.userData.email;
    },
  },
};
</script>
