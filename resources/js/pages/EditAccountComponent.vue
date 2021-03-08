<template>
  <div class="c-container--bg">
    <h2 class="c-container__title">アカウント設定</h2>

    <div class="c-tab">
      <ul class="c-tab__list">
        <li
          class="c-tab__item c-tab__item--three"
          :class="{ 'c-tab__item--active': tabNum === 1 }"
          @click="tabNum = 1"
        >
          メールアドレスの<br class="u-sp--only" />変更
        </li>
        <li
          class="c-tab__item c-tab__item--three"
          :class="{ 'c-tab__item--active': tabNum === 2 }"
          @click="tabNum = 2"
        >
          パスワードの<br class="u-sp--only" />変更
        </li>
        <li
          class="c-tab__item c-tab__item--three"
          :class="{ 'c-tab__item--active': tabNum === 3 }"
          @click="tabNum = 3"
        >
          Twitter<br class="u-sp--only" />アカウント<br
            class="u-sp--only"
          />連携
        </li>
      </ul>
      <div v-if="tabNum === 1" class="c-form__wrapper">
        <change-email />
      </div>

      <div v-if="tabNum === 2" class="c-form__wrapper">
        <change-password />
      </div>

      <div v-if="tabNum === 3" class="c-form__wrapper">
        <twitter-login />
      </div>
    </div>

    <div class="c-form__link">
      <router-link :to="{ name: 'withdraw' }" class="c-form__link">
        アカウントを削除する場合はこちら
      </router-link>
    </div>
  </div>
</template>

<script>
import ChangeEmail from "../components/ChangeEmail.vue";
import ChangePassword from "../components/ChangePassword.vue";
import TwitterLogin from "../components/TwitterLogin.vue";

export default {
  components: {
    ChangeEmail,
    ChangePassword,
    TwitterLogin,
  },
  data() {
    return {
      tabNum: 1,
    };
  },
};
</script>
