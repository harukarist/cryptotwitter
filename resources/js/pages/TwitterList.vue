<template>
  <div class="container c-container">
    <div class="row justify-content-center">
      <h5 class="c-container__title">Twitterフォロー</h5>
    </div>
    <div class="row justify-content-center">
      <TwitterLogin />
    </div>
    <div class="p-target-list row justify-content-center">
      <Pagination
        :directory="directoryName"
        :current-page="currentPage"
        :last-page="lastPage"
      />

      <div class="grid">
        <TwitterTargetItem
          class="p-target-list__item grid__item"
          v-for="target in targets"
          :key="target.id"
          :item="target"
          @follow="createFollow"
          @unfollow="destroyFollow"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { OK } from "../utility";
import TwitterLogin from "../components/TwitterLogin.vue";
import TwitterTargetItem from "../components/TwitterTargetItem.vue";
import Pagination from "../components/Pagination.vue";

export default {
  components: {
    TwitterLogin,
    TwitterTargetItem,
    Pagination,
  },
  data() {
    return {
      targets: [],
      currentPage: 0, //現在ページ
      lastPage: 0, //最終ページ
      directoryName: "twitter",
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
  methods: {
    // Twitterアカウント一覧を取得
    async fetchTargets() {
      const response = await axios.get(`api/twitter?page=${this.page}`);
      // console.log(response.data);
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false;
      }
      // JSONのdata項目を格納
      this.targets = response.data.data;
      // ページネーションの現在ページ、最終ページの値を格納
      this.currentPage = response.data.current_page;
      this.lastPage = response.data.last_page;
      return;
    },
    // フォロー登録メソッド
    async createFollow(id) {
      console.log(id);
      const response = await axios.post(`/api/twitter/${id}/follow`);
      console.log(response.status);
      console.log(response.data);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false;
      }
      // レスポンスがOKの場合は配列の該当項目を変更して返却
      this.targets = this.targets.map((target) => {
        if (target.twitter_id === response.data.target_id) {
          // フォロー済みフラグをtrueに
          target.followed_by_user = true;
        }
        return target;
      });
    },
    // フォロー解除メソッド
    async destroyFollow(id) {
      console.log(id);
      const response = await axios.post(`/api/twitter/${id}/unfollow`);
      console.log(response.status);
      console.log(response.data);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false;
      }
      // レスポンスがOKの場合は配列の該当項目を変更して返却
      this.targets = this.targets.map((target) => {
        if (target.twitter_id === response.data.target_id) {
          // フォロー済みフラグをfalseに
          target.followed_by_user = false;
        }
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
