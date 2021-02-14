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
          お名前・<br class="u-sp--only" />メールアドレスの<br
            class="u-sp--only"
          />変更
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
          Twitterアカウント<br class="u-sp--only" />連携
        </li>
      </ul>
      <div class="c-form__wrapper" v-if="tabNum === 1">
        <ChangeNameOrEmail />
      </div>

      <div class="c-form__wrapper" v-if="tabNum === 2">
        <ChangePassword />
      </div>

      <div class="c-form__wrapper" v-if="tabNum === 3">
        <TwitterLogin />
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
import ChangeNameOrEmail from "../components/ChangeNameOrEmail.vue";
import ChangePassword from "../components/ChangePassword.vue";
import TwitterLogin from "../components/TwitterLogin.vue";

export default {
  components: {
    ChangeNameOrEmail,
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
