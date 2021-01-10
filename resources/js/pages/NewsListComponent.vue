<template>
  <div class="container c-container">
    <div class="row justify-content-center">
      <h5 class="c-container__title">関連ニュース一覧</h5>
    </div>

    <div class="p-news" v-for="item in news" :key="item.title">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            <a :href="item.url">
              {{ item.title }}
            </a>
          </h5>
          <p class="card-text">{{ item.date }} - {{ item.source }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      news: [], //ニュース一覧を格納する配列を用意
    };
  },
  methods: {
    // axiosでニュース一覧取得APIにリクエスト
    getNews() {
      axios.get("/api/news").then((res) => {
        // レスポンスを配列に格納
        this.news = res.data;
      });
    },
  },
  mounted() {
    // 画面描画時にgetNews()メソッドを実行してニュースを取得
    this.getNews();
  },
};
</script>
