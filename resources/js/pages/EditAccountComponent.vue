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
          <transition name="popup">
            <p v-if="successMessage" class="u-mb--xxxl c-alert--success">
              {{ successMessage }}
            </p>
          </transition>
          <div class="c-form__group">
            <label for="username" class="c-form__label">
              お名前
              <span class="c-form__notes">20文字以内</span>
            </label>
            <input
              type="text"
              class="c-input c-input--large"
              id="username"
              v-model="editForm.name"
              required
              autocomplete="name"
              autofocus
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
            <ul v-if="editErrors && editErrors.name">
              <li
                v-for="error in editErrors.name"
                :key="error"
                class="c-valid__error"
              >
                {{ error }}
              </li>
            </ul>
          </div>
          <div class="c-form__group">
            <label for="email" class="c-form__label">メールアドレス</label>
            <input
              type="email"
              class="c-input c-input--large"
              id="email"
              placeholder="例）your.email@example.com"
              v-model="editForm.email"
              required
              autocomplete="email"
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
            <ul v-if="editErrors && editErrors.email">
              <li
                v-for="error in editErrors.email"
                :key="error"
                class="c-valid__error"
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
        <ChangePassword />
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
import ChangePassword from "../components/ChangePassword.vue";

export default {
  components: {
    ChangePassword,
  },
  data() {
    return {
      tabNum: 1,
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
      // dispatch()でauthストアのアクションを呼び出す
      await this.$store.dispatch("auth/EditAccount", this.editForm);
      // API通信が成功した場合
      if (this.apiStatus) {
        // フォーム上にサクセスメッセージを表示
        this.successMessage = "アカウント情報を変更しました";
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
  created() {
    // ページ読み込み時にエラーメッセージをクリア
    this.clearError();
    // ページ読み込み時にユーザー情報を編集フォームに表示
    this.setUserData();
  },
};
</script>
