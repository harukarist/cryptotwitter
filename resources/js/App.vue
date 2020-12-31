<template>
  <div>
    <header>
      <HeaderComponent></HeaderComponent>
    </header>
    <main>
      <div class="container">
        <!-- Vue Routerでコンポーネントを表示 -->
        <RouterView></RouterView>
      </div>
    </main>
  </div>
</template>

<script>
import HeaderComponent from './components/HeaderComponent';

export default {
  components: {
    HeaderComponent
  }
}
</script>
