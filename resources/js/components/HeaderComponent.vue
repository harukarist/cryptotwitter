<template>
  <div class="container-fluid bg-dark mb-3">
    <div class="container">
      <nav class="navbar navbar-expand-lg navbar-dark">
        <!-- サイトロゴ -->
        <router-link v-bind:to="{ name: 'home.index' }" class="navbar-brand">
          CryptoTrend
        </router-link>

        <!-- トグルアイコン -->
        <button
          class="navbar-toggler"
          type="button"
          data-toggle="collapse"
          data-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- メニュー -->
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="p-navbar navbar-nav">
            <li class="p-navbar__item nav-item active">
              <RouterLink v-bind:to="{ name: 'trend.index' }" class="nav-link">
                トレンド一覧
              </RouterLink>
            </li>
            <li class="p-navbar__item nav-item">
              <RouterLink
                v-bind:to="{ name: 'twitter.index' }"
                class="nav-link"
              >
                Twitterフォロー
              </RouterLink>
            </li>
            <li class="p-navbar__item nav-item">
              <RouterLink v-bind:to="{ name: 'news.index' }" class="nav-link">
                関連ニュース
              </RouterLink>
            </li>
            <li class="p-navbar__item nav-item">
              <RouterLink class="button button--link" to="/auth">
                Login / Register
              </RouterLink>
            </li>
          </ul>
        </div>
      </nav>
    </div>
  </div>
</template>

<script>
export default {};
</script>
