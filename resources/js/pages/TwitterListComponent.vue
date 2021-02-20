<template>
  <div class="c-container--bg">
    <section class="c-section">
      <h5 class="c-section__title">Twitterフォロー</h5>
      <p class="c-section__text">
        Twitterの仮想通貨関連アカウントを<br
          class="u-sp--only"
        />集めました。<br />
        自動フォロー機能を使うことで<br />
        アカウントをまとめてフォローできます。<br />
      </p>

      <twitter-login />

      <div v-if="useAutoFollow" class="c-tab c-fade-in p-twitter__list">
        <ul class="c-tab__list">
          <li
            class="c-tab__item c-tab__item--two"
            :class="{ 'c-tab__item--active': !showAutoFollow }"
            @click="showAutoFollow = false"
          >
            未フォローの<br class="u-sp--only" />アカウントを表示
          </li>
          <li
            class="c-tab__item c-tab__item--two"
            :class="{ 'c-tab__item--active': showAutoFollow }"
            @click="showAutoFollow = true"
          >
            自動フォロー履歴<br class="u-sp--only" />を表示
          </li>
        </ul>

        <div v-if="showAutoFollow">
          <transition name="popup" appear>
            <AutoFollowList :page="page" />
          </transition>
        </div>
        <div v-else>
          <transition name="popup" appear>
            <TwitterTargetList :page="page" />
          </transition>
        </div>
      </div>

      <div v-if="!useAutoFollow">
        <TwitterTargetList :page="page" />
      </div>
    </section>
  </div>
</template>

<script>
import { mapState, mapGetters } from "vuex"; // VuexのmapState関数,mapGetters関数をインポート
import TwitterLogin from "../components/TwitterLogin.vue";
import TwitterTargetList from "../components/TwitterTargetList.vue";
import AutoFollowList from "../components/AutoFollowList.vue";

export default {
  components: {
    TwitterLogin,
    TwitterTargetList,
    AutoFollowList,
  },
  // ページネーションのページ番号をrouter.jsから受け取る
  props: {
    page: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  data() {
    return {
      showAutoFollow: false,
    };
  },
  computed: {
    ...mapGetters({
      // twitterストアのcheckゲッターでユーザーのTwitterログイン状態をチェック
      isTwitterLogin: "twitter/check",
      // twitterストアのuseAutoFollowゲッターでユーザーの自動フォロー利用有無を取得
      useAutoFollow: "twitter/useAutoFollow",
    }),
  },
};
</script>
