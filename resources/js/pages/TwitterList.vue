<template>
  <div class="c-container--bg">
    <section class="c-section">
      <h5 class="c-section__title">Twitterフォロー</h5>
      <p class="c-section__text">
        Twitterの仮想通貨関連アカウントを<br
          class="u-sp--only"
        />集めました。<br />
        自動フォロー機能を使うことで
        {{ totalItems }}件以上の<br class="u-sp--only" />ユーザーを<br />
        まとめてフォローできます。<br />
      </p>

      <div class="p-twitter">
        <TwitterLogin :is-login="isTwitterLogin" />
      </div>

      <div class="p-target">
        <div class="p-target__list">
          <h5 class="p-target__title">仮想通貨関連アカウント</h5>
          <TwitterTargetItem
            v-for="target in targets"
            :key="target.id"
            :item="target"
            :is-login="isTwitterLogin"
            @follow="createFollow"
            @unfollow="destroyFollow"
          />

          <Pagination
            :directory="directoryName"
            :current-page="currentPage"
            :last-page="lastPage"
            :per-page="perPage"
            :total-items="totalItems"
          />
          <PaginationInfo
            :current-page="currentPage"
            :per-page="perPage"
            :total-items="totalItems"
            :items-length="targets.length"
          />
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { OK } from "../utility";
import TwitterLogin from "../components/TwitterLogin.vue";
import TwitterTargetItem from "../components/TwitterTargetItem.vue";
import Pagination from "../components/Pagination.vue";
import PaginationInfo from "../components/PaginationInfo.vue";

export default {
  components: {
    TwitterLogin,
    TwitterTargetItem,
    Pagination,
    PaginationInfo,
  },
  data() {
    return {
      targets: [],
      currentPage: 0, //現在ページ
      lastPage: 0, //最終ページ
      perPage: 0, //1ページあたりの表示件数
      totalItems: 0, //トータル件数
      directoryName: "twitter", //ページネーションリンクに付与するディレクトリ
    };
  },
  // ページネーションのページ番号をrouter.jsから受け取る
  props: {
    page: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  computed: {
    isTwitterLogin() {
      // twitterストアのcheckゲッターでユーザーのTwitterログイン状態をチェック
      return this.$store.getters["twitter/check"];
    },
  },
  methods: {
    // Twitterアカウント一覧を取得
    async fetchTargets() {
      const response = await axios.get(`/api/twitter?page=${this.page}`);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // JSONのdata項目を格納
      this.targets = response.data.data;
      console.log(response);
      // ページネーションの現在ページ、最終ページの値を格納
      this.currentPage = response.data.current_page;
      this.lastPage = response.data.last_page;
      // 1ページあたりの表示件数、トータル件数を格納
      this.perPage = response.data.per_page;
      this.totalItems = response.data.total;

      return;
    },
    // フォロー登録メソッド
    async createFollow(id) {
      const response = await axios.post(`/api/twitter/${id}/follow`);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // レスポンスがOKの場合は配列の該当項目を変更して返却
      this.targets = this.targets.map((target) => {
        if (target.twitter_id === response.data.target_id) {
          // フォロー済みフラグをtrueにしてフォローボタンの表示を変更
          target.followed_by_user = true;
        }
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "フォローしました",
          type: "success",
          timeout: 2000,
        });
        return target;
      });
    },
    // フォロー解除メソッド
    async destroyFollow(id) {
      const response = await axios.post(`/api/twitter/${id}/unfollow`);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // レスポンスがOKの場合は配列の該当項目を変更して返却
      this.targets = this.targets.map((target) => {
        if (target.twitter_id === response.data.target_id) {
          // フォロー済みフラグをfalseにしてフォローボタンの表示を変更
          target.followed_by_user = false;
        }
        // フラッシュメッセージを表示
        this.$store.dispatch("message/showMessage", {
          text: "フォロー解除しました",
          type: "success",
          timeout: 2000,
        });
        return target;
      });
    },
  },
  watch: {
    // $routeを監視し、ページ切り替え時にデータ取得を実行
    $route: {
      async handler() {
        await this.fetchTargets();
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
};
</script>
