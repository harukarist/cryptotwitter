<template>
  <router-link v-bind:to="{ name: 'twitter.auth' }" class="navbar-brand">
    <button type="submit" class="btn btn-outline-primary">
      <!-- #00aced -->
      <i class="fab fa-twitter"></i>
      Twitterでログイン・ユーザー登録
    </button>
  </router-link>
</template>

<script>
export default {};
</script>
