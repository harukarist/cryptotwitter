<template>
  <button type="submit" class="btn btn-outline-primary" @click="auth()">
    <!-- #00aced -->
    <i class="fab fa-twitter"></i>
    Twitterでログイン・ユーザー登録
  </button>
</template>

<script>
export default {
  methods: {
    auth() {
      axios.get("/api/auth/twitter").then((res) => {
        // トップページに移動する
        console.log("oAuth success");
        this.$router.push("/");
      });
    },
  },
};
</script>
