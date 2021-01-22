<template>
  <div class="container">
    <h5>CoinCheck</h5>
    <ul v-for="coincheck in tickers.coincheck">
      <li>高値　{{ coincheck.high }}</li>
      <li>安値　{{ coincheck.low }}</li>
    </ul>

    <h5>Zaif</h5>
    <table class="table">
      <thead>
        <tr>
          <th>銘柄名</th>
          <th>高値</th>
          <th>安値</th>
          <th>終値</th>
          <th>出来高</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ticker in tickers.zaif" :key="ticker.name">
          <td>
            {{ ticker.name }}
          </td>
          <td>
            {{ ticker.high }}
          </td>
          <td>
            {{ ticker.low }}
          </td>
          <td>
            {{ ticker.last }}
          </td>
          <td>
            {{ ticker.volume }}
          </td>
        </tr>
      </tbody>
    </table>

    <h5>bitbank</h5>
    <table class="table">
      <thead>
        <tr>
          <th>銘柄名</th>
          <th>高値</th>
          <th>安値</th>
          <th>終値</th>
          <th>出来高</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ticker in tickers.bitbank" :key="ticker.name">
          <td>
            {{ ticker.name }}
          </td>
          <td>
            {{ ticker.high }}
          </td>
          <td>
            {{ ticker.low }}
          </td>
          <td>
            {{ ticker.last }}
          </td>
          <td>
            {{ ticker.volume }}
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
      tickers: [], //ニュース一覧を格納する配列を用意
    };
  },
  methods: {
    // axiosでニュース一覧取得APIにリクエスト
    getTickers() {
      axios.get(`api/tickers`).then((res) => {
        // レスポンスを配列に格納
        this.tickers = res.data;
      });
    },
  },
  mounted() {
    // 画面描画時にgetNews()メソッドを実行してニュースを取得
    this.getTickers();
  },
};
</script>
