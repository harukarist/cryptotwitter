<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">アカウント設定</h2>

    <div class="c-tab">
      <ul class="c-tab__list">
        <li
          class="c-tab__item c-tab__item--two"
          :class="{ 'c-tab__item--active': tabNum === 1 }"
          @click="tabNum = 1"
        >
          お名前・メールアドレスの変更
        </li>
        <li
          class="c-tab__item c-tab__item--two"
          :class="{ 'c-tab__item--active': tabNum === 2 }"
          @click="tabNum = 2"
        >
          パスワードの変更
        </li>
      </ul>
      <div class="c-form__wrapper" v-if="tabNum === 1">
        <form class="c-form--small" @submit.prevent="checkEditForm">
          <div class="c-form__group">
            <label for="username" class="c-form__label">
              お名前
              <span class="c-form__notes">20文字以内</span>
            </label>
            <input
              type="text"
              class="c-input c-input--large c-input--box"
              id="username"
              v-model="editForm.name"
              required
              autocomplete="name"
              autofocus
            />
            <ul v-if="nameErrors" class="c-error__item">
              <li
                v-for="error in nameErrors"
                :key="error"
                class="c-error__text"
              >
                {{ error }}
              </li>
            </ul>
            <ul v-if="editErrors && editErrors.name" class="c-error__item">
              <li
                v-for="error in editErrors.name"
                :key="error"
                class="c-error__text"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div class="c-form__group">
            <label for="email" class="c-form__label">メールアドレス</label>
            <input
              type="email"
              class="c-input c-input--large c-input--box"
              id="email"
              placeholder="例）your.email@example.com"
              v-model="editForm.email"
              required
              autocomplete="email"
            />
            <ul v-if="emailErrors" class="c-error__item">
              <li
                v-for="error in emailErrors"
                :key="error"
                class="c-error__text"
              >
                {{ error }}
              </li>
            </ul>
            <ul v-if="editErrors && editErrors.email" class="c-error__item">
              <li
                v-for="error in editErrors.email"
                :key="error"
                class="c-error__text"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div class="c-form__button">
            <button type="submit" class="c-btn__accent">
              アカウント情報を変更
            </button>
          </div>
        </form>
      </div>
      <div class="c-form__wrapper" v-if="tabNum === 2">
        <form class="c-form--small" @submit.prevent="checkChangePassForm">
          <div class="c-form__group">
            <label for="password" class="c-form__label">
              現在のパスワード
              <span class="c-form__notes">半角英数字8文字以上</span>
            </label>
            <input
              type="password"
              class="c-input c-input--large c-input--box"
              id="password"
              placeholder="現在のパスワードを入力"
              v-model="changePassForm.password"
              required
              autocomplete="current-password"
            />
            <ul v-if="passwordErrors" class="c-error__item">
              <li
                v-for="error in passwordErrors"
                :key="error"
                class="c-error__text"
              >
                {{ error }}
              </li>
            </ul>
            <ul v-if="editErrors && editErrors.password" class="c-error__item">
              <li
                v-for="error in editErrors.password"
                :key="error"
                class="c-error__text"
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
              class="c-input c-input--large c-input--box"
              id="new_password"
              placeholder="新しいパスワードを入力"
              v-model="changePassForm.new_password"
              required
              autocomplete="new-password"
            />
            <ul v-if="newPasswordErrors" class="c-error__item">
              <li
                v-for="error in newPasswordErrors"
                :key="error"
                class="c-error__text"
              >
                {{ error }}
              </li>
            </ul>
            <ul
              v-if="editErrors && editErrors.new_password"
              class="c-error__item"
            >
              <li
                v-for="error in editErrors.new_password"
                :key="error"
                class="c-error__text"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div class="c-form__group">
            <label for="password-confirmation" class="c-form__label"
              >新しいパスワード（再入力）</label
            >
            <input
              type="password"
              class="c-input c-input--large c-input--box"
              id="password-confirmation"
              placeholder="新しいパスワードを再度入力"
              v-model="changePassForm.password_confirmation"
              required
              autocomplete="new-password"
            />
            <ul v-if="confirmErrors" class="c-error__item">
              <li
                v-for="error in confirmErrors"
                :key="error"
                class="c-error__text"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div class="c-form__button">
            <button type="submit" class="c-btn__accent">
              パスワードを変更
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="c-form__link">
      <RouterLink :to="{ name: 'withdraw' }" class="c-form__link">
        アカウントを削除する場合はこちら
      </RouterLink>
    </div>
  </div>
</template>

<script>
import { mapState } from "vuex"; // VuexのmapState関数をインポート

export default {
  data() {
    return {
      tabNum: 1,
      // v-modelでフォームの入力値と紐付けるデータ変数
      editForm: {
        name: "",
        email: "",
      },
      changePassForm: {
        password: "",
        new_password: "",
        password_confirmation: "",
      },
      nameErrors: [],
      emailErrors: [],
      passwordErrors: [],
      newPasswordErrors: [],
      confirmErrors: [],
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
  methods: {
    // フロントエンド側のバリデーションチェック
    checkEditForm(e) {
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
        this.editProfile();
      }
    },
    // メールアドレス形式チェック
    validEmail(email) {
      const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return regex.test(email);
    },
    // フロントエンド側のパスワードバリデーションチェック
    checkChangePassForm(e) {
      const MSG_PASS_EMPTY = "パスワードを入力してください";
      const MSG_PASS_LESS = "8文字以上で入力してください";
      const MSG_RETYPE = "パスワードが一致していません";
      this.passwordErrors = [];
      this.newPasswordErrors = [];
      this.confirmErrors = [];

      // 現在のパスワードのバリデーション
      if (!this.changePassForm.password) {
        // 未入力チェック
        this.passwordErrors.push(MSG_PASS_EMPTY);
      } else if (this.changePassForm.password.length < 6) {
        // 文字数チェック
        this.passwordErrors.push(MSG_PASS_LESS);
      }
      // 新しいパスワードのバリデーション
      if (!this.changePassForm.new_password) {
        // 未入力チェック
        this.newPasswordErrors.push(MSG_PASS_EMPTY);
      } else if (this.changePassForm.new_password.length < 6) {
        // 文字数チェック
        this.newPasswordErrors.push(MSG_PASS_LESS);
      }
      // パスワード再入力のバリデーション
      if (!this.changePassForm.password_confirmation) {
        // 未入力チェック
        this.confirmErrors.push(MSG_PASS_EMPTY);
      } else if (this.changePassForm.password_confirmation.length < 8) {
        // 文字数チェック
        this.confirmErrors.push(MSG_PASS_LESS);
      } else if (
        this.changePassForm.new_password !==
        this.changePassForm.password_confirmation
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
        this.editProfile();
      }
    },

    // ユーザー情報変更WebAPI呼び出し
    async editProfile() {
      // dispatch()でauthストアのloginアクションを呼び出す
      await this.$store.dispatch("auth/edit", this.editForm);
      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "アカウント情報を変更しました",
          type: "success",
          timeout: 2000,
        });
      }
    },
    // パスワード変更WebAPI呼び出し
    async changePassword() {
      // dispatch()でauthストアのloginアクションを呼び出す
      await this.$store.dispatch("auth/changePassword", this.changePassForm);
      // API通信が成功した場合
      if (this.apiStatus) {
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "パスワードを変更しました",
          type: "success",
          timeout: 2000,
        });
      }
    },
    clearError() {
      this.$store.commit("auth/setEditErrorMessages", null);
    },
    setUserData() {
      this.editForm = this.userData;
    },
  },
  created() {
    // ページ読み込み時にエラーメッセージをクリア
    this.clearError();
    this.setUserData();
  },
};
</script>
