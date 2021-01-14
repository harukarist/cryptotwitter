<template>
  <div class="container c-container">
    <h5 class="c-container__title">トレンド一覧</h5>
    更新日時 {{ items.updated_at }}
    <table class="table">
      <thead>
        <tr>
          <th>順位</th>
          <th>銘柄名</th>
          <th>ツイート数</th>
          <th>過去24時間の最高取引価格</th>
          <th>過去24時間の最低取引価格</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(trend, index) in items.trends" :key="trend.id">
          <td>
            {{ index + 1 }}
          </td>
          <td>
            <a
              :href="`https://twitter.com/search?q=${trend.currency_name}`"
              target="_blank"
            >
              {{ trend.currency_name }}</a
            ><br />
            <a
              :href="`https://twitter.com/search?q=${trend.currency_ja}`"
              target="_blank"
            >
              {{ trend.currency_ja }}</a
            >
          </td>
          <td>
            {{ trend.tweet_hour }}<br>
            {{ trend.tweet_day }}<br>
            {{ trend.tweet_week }}
          </td>
          <td>
            {{ trend.high }}
          </td>
          <td>
            {{ trend.low }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  data() {
    return {
      items: [], //ニュース一覧を格納する配列を用意
    };
  },
  methods: {
    // axiosでニュース一覧取得APIにリクエスト
    getTrends() {
      axios.get(`${process.env.APP_URL}/api/trend`).then((res) => {
        // レスポンスを配列に格納
        this.items = res.data;
      });
    },
  },
  mounted() {
    // 画面描画時にgetNews()メソッドを実行してニュースを取得
    this.getTrends();
  },
};
</script>
