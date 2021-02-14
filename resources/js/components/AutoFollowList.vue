<template>
  <div class="p-target">
    <div class="p-target__list">
      <SearchFormComponent @search="searchTargets" @clear="clearResult" />

      <h5 class="p-target__title">自動フォロー履歴</h5>

      <TwitterTargetItem
        v-for="target in targets"
        :key="target.id"
        :item="target.target_user"
        @follow="createFollow"
        @unfollow="destroyFollow"
      >
        <div class="u-font--muted u-font--small u-mb--m" slot="follow_date">
          フォロー日時: {{ target.created_at }}
        </div>
      </TwitterTargetItem>

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
</template>

<script>
import { OK } from "../utility";
import TwitterTargetItem from "../components/TwitterTargetItem.vue";
import Pagination from "../components/Pagination.vue";
import PaginationInfo from "../components/PaginationInfo.vue";
import SearchFormComponent from "../components/SearchFormComponent.vue";

export default {
  components: {
    TwitterTargetItem,
    Pagination, //ページ番号付きページネーション（PC、タブレットで表示）
    PaginationInfo, //ページネーションの件数表示
    SearchFormComponent,
  },
  data() {
    return {
      targets: [], //仮想通貨関連アカウント一覧を格納する配列を用意
      followed_at: "", //現在ページ
      currentPage: 0, //現在ページ
      lastPage: 0, //最終ページ
      perPage: 0, //1ページあたりの表示件数
      totalItems: 0, //トータル件数
      directoryName: "autofollow/list", //ページネーションリンクに付与するディレクトリ
    };
  },
  // ページネーションのページ番号を親コンポーネントTwitterListComponentから受け取る
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
    searchTargets(word) {
      let params = {
        params: {
          search: word,
        },
      };
      this.fetchTargets(params);
    },
    clearResult() {
      let params = {};
      this.fetchTargets(params);
    },
    // 自動フォロー済みのTwitterアカウント一覧を取得
    async fetchTargets(params) {
      const response = await axios.get(
        `/api/autofollow/list?page=${this.page}`,
        params
      );
      console.log(response);

      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // JSONのdata項目を格納
      this.targets = response.data.data;
      // console.log(response);
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
        if (target.target_user.twitter_id === response.data.target_id) {
          // フォロー済みフラグをtrueにしてフォローボタンの表示を変更
          target.target_user.followed_by_user = true;
        }
        return target;
      });
      // フラッシュメッセージを表示
      this.$store.dispatch("message/showMessage", {
        text: "フォローしました",
        type: "success",
        timeout: 2000,
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
        if (target.target_user.twitter_id === response.data.target_id) {
          // フォロー済みフラグをfalseにしてフォローボタンの表示を変更
          target.target_user.followed_by_user = false;
        }
        return target;
      });
      // フラッシュメッセージを表示
      this.$store.dispatch("message/showMessage", {
        text: "フォロー解除しました",
        type: "success",
        timeout: 2000,
      });
    },
  },
  watch: {
    // $routeを監視し、ページ切り替え時にデータ取得を実行
    $route: {
      async handler() {
        this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
        await this.fetchTargets();
        this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
};
</script>