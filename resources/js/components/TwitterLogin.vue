<template>
  <div>
    <div v-if="usersTwitter">
      <div class="media">
        <img :src="usersTwitter.twitter_avatar" />
        <div class="media-body">
          {{ usersTwitter.user_name }} @{{ usersTwitter.screen_name }}
        </div>
      </div>
      <a class="btn btn-outline-primary" href="/auth/twitter/delete">
        <i class="fab fa-twitter"></i>
        Twitterアカウント連携を解除
      </a>

      <!-- データ全体の状態を確認 -->
      <!-- <pre>{{ $data }}</pre> -->
    </div>
    <div v-else>
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
    usersTwitter() {
      // twitterストアのusersTwitterゲッターでユーザーのTwitterアカウント情報を取得
      return this.$store.getters["twitter/usersTwitter"];
    },
  },
  methods: {
    // checkAuth() {
    //   axios.get("/api/auth/twitter/check").then((res) => {
    //     console.log(res.data);
    //     this.users_twitter = res.data;
    //   });
    // },

    async checkAuth() {
      // dispatch()でtwitterストアのcheckAuthアクションを呼び出す
      await this.$store.dispatch("twitter/checkAuth");
    },
  },
  mounted() {
    // 画面描画時にTwitter認証情報を取得
    this.checkAuth();
  },
};
</script>
