<template>
  <form
    class="c-form--large"
    @submit.prevent="sendContact"
  >
    <p class="c-form__text">
      下記の内容で送信してよろしいですか？<br>
    </p>
    <div class="c-form__group">
      <label
        for="name"
        class="c-form__label"
      > お名前<span class="c-form__label--required">必須</span> </label>
      <div class="c-form__confirm-text">
        {{ formData.name }}
      </div>
    </div>
    <div class="c-form__group">
      <label
        for="email"
        class="c-form__label"
      > メールアドレス<span class="c-form__label--required">必須</span> </label>
      <div class="c-form__confirm-text">
        {{ formData.email }}
      </div>
    </div>

    <div class="c-form__group">
      <label
        for="message"
        class="c-form__label"
      > お問い合わせ内容<span class="c-form__label--required">必須</span> </label>
      <div class="c-form__confirm-text">
        <p class="u-font__br">
          {{ formData.message }}
        </p>
      </div>
    </div>
    <div class="c-form__button">
      <div
        class="c-btn--danger u-mb--m"
        @click.prevent="goBack"
      >
        内容を修正する
      </div>
      <button
        type="submit"
        class="c-btn--main u-mb--m"
      >
        送信する
      </button>
    </div>
  </form>
</template>

<script>
import { OK, UNPROCESSABLE_ENTITY } from '../utility'

export default {
  props: {
    // v-modelでフォームの入力値と紐付けるデータ変数
    formData: {
      type: Object,
      required: true,
      //オブジェクトの初期値を関数で指定する
      default: function () {
        return {
          name: '',
          email: '',
          message: '',
        }
      },
    },
  },
  data() {
    return {
      // サーバー側のバリデーションエラーメッセージ
      apiMessages: [],
      apiResult: '',
      replacedMessage: '', //お問い合わせ本文の改行コード \n を <br>に置換した文字列
    }
  },
  methods: {
    // 入力フォームに戻る
    goBack() {
      this.$emit('back')
      return
    },
    // お問い合わせフォーム送信WebAPI呼び出し
    async sendContact() {
      // サーバー側バリデーションメッセージをクリア
      this.apiMessages = []
      this.$store.commit('loader/setIsLoading', true) //ローディング表示をオン
      // サーバーのAPIを呼び出し
      const response = await axios.post('/api/contact/send', this.formData)
      this.$store.commit('loader/setIsLoading', false) //ローディング表示をオフ
      // API通信が成功した場合
      if (response.status === OK) {
        // $emitで親コンポーネントに通知して送信完了画面へ遷移
        this.$emit('sent', this.formData)
        return
      }
      // バリデーションエラーの場合はエラーメッセージを表示
      if (response.status === UNPROCESSABLE_ENTITY) {
        // サーバーから返却されたエラーメッセージを格納
        this.apiMessages = response.data.errors
        // $emitで親コンポーネントに通知してフォームを再表示
        this.$emit('back')
        return
      } else {
        // その他の失敗の場合はerrorモジュールのsetCodeミューテーションでステータスを更新
        // 別モジュールのミューテーションをcommitするためroot: trueを指定する
        this.$store.commit('error/setCode', response.status)
        // $emitで親コンポーネントに通知してフォームを再表示
        this.$emit('back')
      }
    },
  },
}
</script>
