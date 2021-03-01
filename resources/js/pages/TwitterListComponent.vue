<template>
  <div class="c-container--bg">
    <section class="c-section">
      <h5 class="c-section__title">
        Twitterフォロー
      </h5>
      <p class="c-section__text">
        Twitterの仮想通貨関連アカウントを<br class="u-sp--only">集めました。<br>
        自動フォロー機能を使うことで<br>
        アカウントをまとめてフォローできます。<br>
      </p>

      <twitter-login />

      <div
        v-if="useAutoFollow"
        class="c-tab c-fade-in p-twitter__list"
      >
        <ul class="c-tab__list">
          <li
            class="c-tab__item c-tab__item--two"
            :class="{ 'c-tab__item--active': !showAutoFollow }"
          >
            <router-link :to="{ name: 'twitter.index' }">
              未フォローの<br class="u-sp--only">アカウントを表示
            </router-link>
          </li>
          <li
            class="c-tab__item c-tab__item--two"
            :class="{ 'c-tab__item--active': showAutoFollow }"
          >
            <router-link :to="{ name: 'autofollow.list' }">
              自動フォロー履歴<br class="u-sp--only">を表示
            </router-link>
          </li>
        </ul>

        <div v-if="showAutoFollow">
          <transition
            name="popup"
            appear
          >
            <auto-follow-list :page="page" />
          </transition>
        </div>
        <div v-else>
          <transition
            name="popup"
            appear
          >
            <twitter-target-list :page="page" />
          </transition>
        </div>
      </div>
      <div v-if="!useAutoFollow">
        <twitter-target-list :page="page" />
      </div>
    </section>
  </div>
</template>

<script>
import { mapGetters } from 'vuex' // VuexのmapGetters関数をインポート
import TwitterLogin from '../components/TwitterLogin.vue'
import TwitterTargetList from '../components/TwitterTargetList.vue'
import AutoFollowList from '../components/AutoFollowList.vue'

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
    }
  },
  computed: {
    ...mapGetters({
      // twitterストアのcheckゲッターでユーザーのTwitterログイン状態をチェック
      isTwitterLogin: 'twitter/check',
      // twitterストアのuseAutoFollowゲッターでユーザーの自動フォロー利用有無を取得
      useAutoFollow: 'twitter/useAutoFollow',
    }),
  },
  watch: {
    // ページリンク切り替え時にコンポーネントの再生成が必要になるため、
    // $routeを監視し、ページ切り替え時にデータ取得を実行する
    $route: {
      async handler() {
        await this.checkThisPath()
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
  methods: {
    checkThisPath() {
      if (this.$route.path === this.$router.resolve({ name: 'autofollow.list' }).href) {
        this.showAutoFollow = true
      } else {
        this.showAutoFollow = false
      }
    },
  },
}
</script>
