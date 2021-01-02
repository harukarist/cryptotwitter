<template>
  <div class="container c-container">
    <div class="row justify-content-center">
      <h5 class="c-container__title">Twitterフォロー</h5>
    </div>
    <div class="row justify-content-center">
      <TwitterLoginComponent></TwitterLoginComponent>
    </div>
  </div>
</template>

<script>
import TwitterLoginComponent from "./TwitterLoginComponent";

export default {
  components: {
    TwitterLoginComponent,
  },
};
</script>
