<template>
  <form class="c-form--small" @submit.prevent="checkChangePassForm">
    <input
      name="username"
      v-model="changePassForm.username"
      autocomplete="username"
      style="display: none"
    />
    <div class="c-form__group">
      <label for="current_password" class="c-form__label">
        現在のパスワード
        <span class="c-form__notes">半角英数字8文字以上</span>
      </label>
      <input
        type="password"
        class="c-input c-input--large"
        id="current_password"
        placeholder="現在のパスワードを入力"
        v-model="changePassForm.current_password"
        required
        autocomplete="current-password"
      />
      <ul v-if="passwordErrors">
        <li v-for="error in passwordErrors" :key="error" class="c-valid__error">
          {{ error }}
        </li>
      </ul>
      <ul v-if="apiErrors && apiErrors.current_password">
        <li
          v-for="error in apiErrors.current_password"
          :key="error"
          class="c-valid__error"
        >
          {{ error }}
        </li>
      </ul>
    </div>
    <div class="c-form__group">
      <label for="new_password" class="c-form__label">
        新しいパスワード
        <span class="c-form__notes">半角英数字8文字以上</span>
      </label>
      <input
        type="password"
        class="c-input c-input--large"
        id="new_password"
        placeholder="新しいパスワードを入力"
        v-model="changePassForm.new_password"
        required
        autocomplete="new-password"
      />
      <ul v-if="newPasswordErrors">
        <li
          v-for="error in newPasswordErrors"
          :key="error"
          class="c-valid__error"
        >
          {{ error }}
        </li>
      </ul>
      <ul v-if="apiErrors && apiErrors.new_password">
        <li
          v-for="error in apiErrors.new_password"
          :key="error"
          class="c-valid__error"
        >
          {{ error }}
        </li>
      </ul>
    </div>
    <div class="c-form__group">
      <label for="new_password_confirmation" class="c-form__label"
        >新しいパスワード（再入力）</label
      >
      <input
        type="password"
        class="c-input c-input--large"
        id="new_password_confirmation"
        placeholder="新しいパスワードを再度入力"
        v-model="changePassForm.new_password_confirmation"
        required
        autocomplete="new-password"
      />
      <ul v-if="confirmErrors">
        <li v-for="error in confirmErrors" :key="error" class="c-valid__error">
          {{ error }}
        </li>
      </ul>
      <ul v-if="apiErrors && apiErrors.new_password_confirmation">
        <li
          v-for="error in apiErrors.new_password_confirmation"
          :key="error"
          class="c-valid__error"
        >
          {{ error }}
        </li>
      </ul>
    </div>
    <div class="c-form__button">
      <button type="submit" class="c-btn__accent">パスワードを変更</button>
    </div>
  </form>
</template>

<script>
import { mapState } from "vuex"; // VuexのmapState関数をインポート
import { OK, UNPROCESSABLE_ENTITY } from "../utility";

export default {
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
      apiErrors: [],
    };
  },
  methods: {
    // フロントエンド側のパスワードバリデーションチェック
    checkChangePassForm(e) {
      const MSG_PASS_EMPTY = "パスワードを入力してください";
      const MSG_PASS_LESS = "8文字以上で入力してください";
      const MSG_RETYPE = "パスワードが一致していません";
      this.passwordErrors = [];
      this.newPasswordErrors = [];
      this.confirmErrors = [];

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
      const response = await axios.post(
        "/api/password/change",
        this.changePassForm
      );
      // レスポンスのステータスがバリデーションエラーの場合はエラーメッセージを表示
      if (response.status === UNPROCESSABLE_ENTITY) {
        this.apiErrors = response.data.errors;
        return false;
      } else if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false;
      }
      // レスポンスがOKの場合はフラッシュメッセージを表示
      this.$store.dispatch("message/showMessage", {
        text: "パスワードを変更しました",
        type: "success",
        timeout: 6000,
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
