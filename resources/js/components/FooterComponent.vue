<template>
  <div class="c-container--fluid">
    <div class="p-footer">
      <ul class="p-footer__list">
        <li class="p-footer__item">
          <router-link
            :to="{ name: 'terms' }"
            class="p-footer__link"
          >
            利用規約
          </router-link>
        </li>
        <li class="p-footer__item">
          <router-link
            :to="{ name: 'privacy' }"
            class="p-footer__link"
          >
            プライバシーポリシー
          </router-link>
        </li>
        <li class="p-footer__item">
          <router-link
            :to="{ name: 'contact' }"
            class="p-footer__link"
          >
            お問い合わせ
          </router-link>
        </li>
      </ul>
      <p class="p-footer__copyright">
        © 2021 CryptoTrend.
      </p>
    </div>
  </div>
</template>

<script>
export default {}
</script>
