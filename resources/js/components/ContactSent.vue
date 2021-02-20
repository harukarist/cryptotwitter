
<template>
  <div class="c-form--large">
    <p class="u-font--center u-mb--xxl">
      下記の内容でお問い合わせを送信しました
    </p>
    <div class="c-form__group">
      <label for="name" class="c-form__label"> お名前 </label>
      <div class="c-form__confirm-text">
        {{ formData.name }}
      </div>
    </div>
    <div class="c-form__group">
      <label for="email" class="c-form__label"> メールアドレス </label>
      <div class="c-form__confirm-text">
        {{ formData.email }}
      </div>
    </div>

    <div class="c-form__group">
      <label for="message" class="c-form__label"> お問い合わせ内容 </label>
      <div class="c-form__confirm-text">
        <p class="u-font__br">{{ formData.message }}</p>
      </div>
    </div>

    <div class="c-form__link">
      <RouterLink :to="{ name: 'top' }" class="c-form__link">
        トップページへ戻る
      </RouterLink>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    // v-modelでフォームの入力値と紐付けるデータ変数
    formData: {
      type: Object,
      required: true,
      //オブジェクトの初期値を関数で指定する
      default: function () {
        return {
          name: "",
          email: "",
          message: "",
        };
      },
    },
  },
};
</script>
