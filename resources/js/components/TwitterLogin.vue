<template>
  <div>
    <div v-if="isTwitterLogin">
      <div class="media">
        <img :src="usersTwitter.twitter_avatar" />
        <div class="media-body">
          {{ usersTwitter.user_name }} @{{ usersTwitter.screen_name }}
        </div>
      </div>

      <div class="p-autofollow" v-if="usersTwitter.use_autofollow">
        <div class="alert-success">
          <p><i class="fab fa-twitter"></i>自動フォロー機能を利用中です</p>
          <p>
            自動フォロー機能でこれまで{{
              totalAutoFollow
            }}件の仮想通貨アカウントをフォローしました
          </p>
        </div>

        <a class="text-secondary" @click.stop="cancelAutoFollow()">
          自動フォロー機能を解除する
        </a>
      </div>

      <div class="p-autofollow" v-if="!usersTwitter.use_autofollow">
        <button class="btn btn-outline-primary" @click="applyAutoFollow()">
          <i class="fab fa-twitter"></i>
          自動フォロー機能を利用する
        </button>
      </div>
      <a class="text-secondary" href="/auth/twitter/delete">
        <i class="fab fa-twitter"></i>
        Twitterアカウント連携を解除する
      </a>

      <!-- データ全体の状態を確認 -->
      <!-- <pre>{{ $data }}</pre> -->
    </div>
    <div v-if="!isTwitterLogin">
      <a class="btn btn-outline-primary" href="/auth/twitter/login">
        <i class="fab fa-twitter"></i>
        Twitterアカウント連携
      </a>
    </div>
  </div>
</template>

<script>
export default {
  name: "TwitterLogin",
  data() {
    return {
      users_twitter: [],
    };
  },
  computed: {
    isTwitterLogin() {
      // twitterストアのcheckゲッターでユーザーのTwitterログイン状態をチェック
      return this.$store.getters["twitter/check"];
    },
    usersTwitter() {
      // twitterストアのusersTwitterゲッターでユーザーのTwitterアカウント情報を取得
      return this.$store.getters["twitter/usersTwitter"];
    },
    totalAutoFollow() {
      // twitterストアのusersTwitterゲッターでユーザーのTwitterアカウント情報を取得
      return this.$store.getters["twitter/totalAutoFollow"];
    },
  },
  methods: {
    async checkAuth() {
      // dispatch()でtwitterストアのcheckAuthアクションを呼び出す
      await this.$store.dispatch("twitter/checkAuth");
    },
    async applyAutoFollow() {
      // dispatch()でtwitterストアのapplyAutoFollowアクションを呼び出す
      await this.$store.dispatch("twitter/applyAutoFollow");
    },
    async cancelAutoFollow() {
      // dispatch()でtwitterストアのcancelAutoFollowアクションを呼び出す
      await this.$store.dispatch("twitter/cancelAutoFollow");
    },
  },
  mounted() {
    // 画面描画時にTwitter認証情報を取得
    this.checkAuth();
  },
};
</script>
