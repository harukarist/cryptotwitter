<template>
  <form class="c-form--small" @submit.prevent="checkChangePassForm">
    <input
      v-model="changePassForm.username"
      name="username"
      autocomplete="username"
      style="display: none"
    />
    <div class="c-form__group">
      <label for="current_password" class="c-form__label">
        現在のパスワード
        <span class="c-form__notes">半角英数字8文字以上</span>
      </label>
      <input
        id="current_password"
        v-model="changePassForm.current_password"
        type="password"
        class="c-input c-input--large"
        placeholder="現在のパスワードを入力"
        required
        autocomplete="current-password"
      />
      <invalid-component :messages="passwordErrors" />
      <invalid-component
        v-if="apiMessages && apiMessages.current_password"
        :messages="apiMessages.current_password"
      />
    </div>
    <div class="c-form__group">
      <label for="new_password" class="c-form__label">
        新しいパスワード
        <span class="c-form__notes">半角英数字8文字以上</span>
      </label>
      <input
        id="new_password"
        v-model="changePassForm.new_password"
        type="password"
        class="c-input c-input--large"
        placeholder="新しいパスワードを入力"
        required
        autocomplete="new-password"
      />
      <invalid-component :messages="newPasswordErrors" />
      <invalid-component
        v-if="apiMessages && apiMessages.new_password"
        :messages="apiMessages.new_password"
      />
    </div>
    <div class="c-form__group">
      <label for="new_password_confirmation" class="c-form__label"
        >新しいパスワード（再入力）</label
      >
      <input
        id="new_password_confirmation"
        v-model="changePassForm.new_password_confirmation"
        type="password"
        class="c-input c-input--large"
        placeholder="新しいパスワードを再度入力"
        required
        autocomplete="new-password"
      />
      <invalid-component :messages="confirmErrors" />
      <invalid-component
        v-if="apiMessages && apiMessages.new_password_confirmation"
        :messages="apiMessages.new_password_confirmation"
      />
    </div>
    <div class="c-form__button">
      <button type="submit" class="c-btn--accent c-btn--large">
        パスワードを変更
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
  data() {
    return {
      // v-modelでフォームの入力値と紐付けるデータ変
      changePassForm: {
        username: "",
        current_password: "",
        new_password: "",
        new_password_confirmation: "", //Laravelではフィールド名+_confirmationとフィールドが同じ値かをバリデーション
      },
      passwordErrors: [],
      newPasswordErrors: [],
      confirmErrors: [],
      apiMessages: [],
    };
  },
  methods: {
    // フロントエンド側のパスワードバリデーションチェック
    checkChangePassForm() {
      const MSG_PASS_EMPTY = "パスワードを入力してください";
      const MSG_PASS_LESS = "8文字以上で入力してください";
      const MSG_RETYPE = "パスワードが一致していません";
      this.passwordErrors = [];
      this.newPasswordErrors = [];
      this.confirmErrors = [];
      this.apiMessages = [];

      // 現在のパスワードのバリデーション
      if (!this.changePassForm.current_password) {
        // 未入力チェック
        this.passwordErrors.push(MSG_PASS_EMPTY);
      } else if (this.changePassForm.current_password.length < 8) {
        // 文字数チェック
        this.passwordErrors.push(MSG_PASS_LESS);
      }
      // 新しいパスワードのバリデーション
      if (!this.changePassForm.new_password) {
        // 未入力チェック
        this.newPasswordErrors.push(MSG_PASS_EMPTY);
      } else if (this.changePassForm.new_password.length < 8) {
        // 文字数チェック
        this.newPasswordErrors.push(MSG_PASS_LESS);
      }
      // パスワード再入力のバリデーション
      if (!this.changePassForm.new_password_confirmation) {
        // 未入力チェック
        this.confirmErrors.push(MSG_PASS_EMPTY);
      } else if (this.changePassForm.new_password_confirmation.length < 8) {
        // 文字数チェック
        this.confirmErrors.push(MSG_PASS_LESS);
      } else if (
        this.changePassForm.new_password !==
        this.changePassForm.new_password_confirmation
      ) {
        // パスワード一致チェック
        this.confirmErrors.push(MSG_RETYPE);
      }
      // エラーメッセージを格納した配列を全て結合
      const results = this.passwordErrors.concat(
        this.newPasswordErrors,
        this.confirmErrors
      );
      // エラーメッセージがなければパスワード変更WebAPIを呼び出す
      if (!results.length) {
        this.changePassword();
      }
    },
    
    // パスワード変更WebAPI呼び出し
    async changePassword() {
      this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
      const response = await axios.post(
        "/api/password/change",
        this.changePassForm
      );
      this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      // レスポンスのステータスがバリデーションエラーの場合はエラーメッセージを表示
      if (response.status === UNPROCESSABLE_ENTITY) {
        this.apiMessages = response.data.errors;
        return false;
      } else if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false;
      }
      // レスポンスがOKの場合はフラッシュメッセージを表示
      this.$store.dispatch("message/showMessage", {
        text: "パスワードを変更しました",
        type: "success",
        timeout: 2000,
      });
      // エラーメッセージをクリア
      this.passwordErrors = [];
      this.newPasswordErrors = [];
      this.confirmErrors = [];
    },
    setUserData() {
      // DBに登録されたユーザー情報をパスワード変更フォームのv-modelに格納
      this.changePassForm.username = this.userData.username;
    },
  },
};
</script>
