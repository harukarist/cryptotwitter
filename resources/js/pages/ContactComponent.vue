<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">
      お問い合わせ
    </h2>
    <div class="c-form__wrapper">
      <ContactForm
        v-if="!isConfirm && !isSent"
        :form-data="formData"
        @confirm="formConfirm"
      />
      <ContactConfirm
        v-if="isConfirm"
        :form-data="formData"
        @sent="formSent"
        @back="formBack"
      />
      <ContactSent
        v-if="isSent"
        :form-data="formData"
      />
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex' // VuexのmapState関数をインポート
import ContactForm from '../components/ContactForm.vue'
import ContactConfirm from '../components/ContactConfirm.vue'
import ContactSent from '../components/ContactSent.vue'

export default {
  components: {
    // ViewRouterで子コンポーネントの表示を切り替え
    ContactForm, //入力フォーム
    ContactConfirm, //入力確認画面
    ContactSent, //送信完了画面
  },
  data() {
    return {
      // フォームの入力値をv-modelで紐付け、
      // propsで入力フォームと確認画面のデータの受け渡しを行う
      formData: {
        name: '',
        email: '',
        message: '',
      },
      isConfirm: false,
      isSent: false,
    }
  },
  computed: {
    ...mapState({
      // authストアのステートを参照し、ユーザーデータを取得
      userData: (state) => state.auth.userData,
    }),
  },
  created() {
    // ページ読み込み時にユーザー情報があればフォームに表示
    this.setUserData()
  },
  methods: {
    setUserData() {
      // ユーザー情報がある場合
      if (this.userData) {
        // DBに登録されたユーザー情報を編集フォームのv-modelに格納
        this.formData.name = this.userData.name ?? ''
        this.formData.email = this.userData.email ?? ''
      }
    },
    formConfirm(contactForm) {
      // 子コンポーネントから受け取ったフォーム入力内容をv-modelの各プロパティに格納
      Object.assign(this.formData, contactForm)
      //確認画面を表示
      this.isConfirm = true
      this.isSent = false
    },
    formSent() {
      //送信完了画面を表示
      this.isSent = true
      this.isConfirm = false
    },
    formBack() {
      //フォーム画面を表示
      this.isSent = false
      this.isConfirm = false
    },
  },
}
</script>
