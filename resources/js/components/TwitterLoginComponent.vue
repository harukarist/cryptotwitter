<template>
  <div>
    <a class="btn btn-outline-primary" href="/auth/twitter">
      <!-- #00aced -->
      <i class="fab fa-twitter"></i>
      Twitterアカウント連携
    </a>
    <button type="submit" class="btn btn-outline-primary" @click="auth()">
      <!-- #00aced -->
      <i class="fab fa-twitter"></i>
      Twitterアカウント連携
    </button>
  </div>
</template>

<script>
export default {
  methods: {
    auth() {
      axios.get("/api/auth/twitter").then((res) => {
        console.log("oAuth success");
      });
    },
  },
};
</script>
