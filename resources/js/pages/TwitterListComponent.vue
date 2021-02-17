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

      <TwitterLogin />

      <div v-if="totalAutoFollow" class="c-tab c-fade-in p-twitter__list">
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

      <div v-if="!totalAutoFollow">
        <TwitterTargetList :page="page" />
      </div>
    </section>
  </div>
</template>

<script>
import { OK } from "../utility";
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
      totalAutoFollow: 0,
      showAutoFollow: false,
    };
  },
  computed: {
    isTwitterLogin() {
      // twitterストアのcheckゲッターでユーザーのTwitterログイン状態をチェック
      return this.$store.getters["twitter/check"];
    },
  },
  methods: {
    // 自動フォロー累計数を取得
    async fetchTotalAutoFollow() {
      const response = await axios.get("/api/autofollow/count");
      console.log(response);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // 自動フォロー累計数を格納
      this.totalAutoFollow = response.data;
    },
  },
  created() {
    // ページ読み込み時に自動フォロー累計数を取得
    this.fetchTotalAutoFollow();
  },
};
</script>
