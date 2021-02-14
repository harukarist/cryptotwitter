<template>
  <div class="c-container--bg">
    <section class="c-section">
      <h5 class="c-section__title">リアルタイムトレンド</h5>
      <div class="p-home__contents c-fade-in">
        <div class="c-tab p-home__contents">
          <ul class="c-tab__list">
            <li
              class="c-tab__item c-tab__item--three"
              :class="{ 'c-tab__item--active': column === 'tweet_hour' }"
              @click="sortByHour"
            >
              過去1時間の<br class="u-sp--only" />ツイート
            </li>
            <li
              class="c-tab__item c-tab__item--three"
              :class="{ 'c-tab__item--active': column === 'tweet_day' }"
              @click="sortByDay"
            >
              過去24時間の<br class="u-sp--only" />ツイート
            </li>
            <li
              class="c-tab__item c-tab__item--three"
              :class="{ 'c-tab__item--active': column === 'tweet_week' }"
              @click="sortByWeek"
            >
              過去1週間の<br class="u-sp--only" />ツイート
            </li>
          </ul>

          <div class="p-trend">
            <table class="c-table">
              <thead class="c-table__thead">
                <tr>
                  <th>順位</th>
                  <th>銘柄名</th>
                  <th>
                    {{ activeColumn }}の<br class="u-sp-hidden" />ツイート数
                  </th>
                  <th>過去24時間の<br class="u-sp-hidden" />最高取引価格</th>
                  <th>過去24時間の<br class="u-sp-hidden" />最低取引価格</th>
                </tr>
              </thead>
              <tbody class="c-table__tbody">
                <tr
                  v-for="(trend, index) in sorted"
                  :key="trend.id"
                  class="p-trend__item"
                >
                  <td>
                    <span
                      class="p-trend__order"
                      :class="{
                        'p-trend__order--gold': index === 0,
                        'p-trend__order--silver': index === 1,
                        'p-trend__order--bronze': index === 2,
                      }"
                    >
                      {{ index + 1 }}
                    </span>
                  </td>
                  <td>
                    <a
                      :href="`https://twitter.com/search?q=${trend.currency_name}`"
                      target="_blank"
                    >
                      <p class="p-trend__name">
                        {{ trend.currency_name }}
                      </p>
                    </a>
                    <a
                      :href="`https://twitter.com/search?q=${trend.currency_ja}`"
                      target="_blank"
                    >
                      <span class="p-trend__name--small">
                        {{ trend.currency_ja }}
                      </span>
                    </a>
                  </td>
                  <td>
                    <p v-if="column === 'tweet_hour'" class="u-font__num">
                      {{ trend.tweet_hour | localeNum }}
                    </p>
                    <p v-if="column === 'tweet_day'" class="u-font__num">
                      {{ trend.tweet_day | localeNum }}
                    </p>
                    <p v-if="column === 'tweet_week'" class="u-font__num">
                      {{ trend.tweet_week | localeNum }}
                    </p>
                  </td>
                  <td>
                    <p v-if="trend.high" class="p-trend__price">
                      <span class="u-font__num">{{
                        trend.high | round | localeNum
                      }}</span>
                      <span class="u-font--small">円</span>
                    </p>
                    <p v-else class="u-font--small u-font--muted">不明</p>
                  </td>
                  <td>
                    <p v-if="trend.low" class="p-trend__price">
                      <span class="u-font__num">{{
                        trend.low | round | localeNum
                      }}</span>
                      <span class="u-font--small">円</span>
                    </p>
                    <p v-else class="u-font--small u-font--muted">不明</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="p-home__contents">
          <div class="u-font--small u-font--muted u-font--right">
            更新日時 {{ items.updated_at }}
          </div>

          <div class="p-readmore__wrapper">
            <RouterLink :to="{ name: 'trend.index' }" class="p-readmore__link">
              もっと見る
            </RouterLink>
          </div>
        </div>
      </div>
    </section>

    <section class="c-section">
      <h5 class="c-section__title">仮想通貨 最新Twitterアカウント</h5>
      <div class="p-target p-home__contents">
        <div class="p-target__list p-home__twitter">
          <TwitterTargetItem
            v-for="target in targets"
            :key="target.id"
            :item="target"
            :is-login="false"
          />
          <div class="p-readmore__wrapper">
            <RouterLink
              :to="{ name: 'twitter.index' }"
              class="p-readmore__link"
            >
              もっと見る
            </RouterLink>
          </div>
        </div>
      </div>
    </section>

    <section class="c-section">
      <h5 class="c-section__title">仮想通貨 最新ニュース</h5>
      <div class="p-news p-home__contents">
        <div class="p-home__row">
          <div
            class="p-news__item p-home__item"
            v-for="item in news"
            :key="item.title"
          >
            <div class="p-news__info">
              <span class="p-news__date">
                {{ item.published_date }}
              </span>
            </div>
            <h5 class="p-news__title">
              <a :href="item.url" target="_blank">
                {{ item.title }}
              </a>
            </h5>
          </div>
        </div>

        <div class="p-readmore__wrapper">
          <RouterLink :to="{ name: 'news.index' }" class="p-readmore__link">
            もっと見る
          </RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { OK } from "../utility";
import TwitterTargetItem from "../components/TwitterTargetItem.vue";

export default {
  components: {
    TwitterTargetItem,
  },
  data() {
    return {
      column: "tweet_hour",
      items: [], //トレンド一覧を格納する配列を用意
      targets: [], //仮想通貨関連アカウント一覧を格納する配列
      news: [], //ニュース一覧を格納する配列
    };
  },
  filters: {
    // 小数点以下を第一位に丸めるフィルタ
    round(val) {
      return Math.round(val * 10) / 10;
    },
    // 数字をカンマ区切りに変換するフィルタ
    localeNum(val) {
      return val.toLocaleString();
    },
  },
  computed: {
    // 表示中のトレンド
    activeColumn() {
      if (this.column === "tweet_hour") {
        return "1時間";
      } else if (this.column === "tweet_day") {
        return "24時間";
      } else if (this.column === "tweet_week") {
        return "1週間";
      }
    },
    // ツイート数の大きい順に並べ替えて表示
    sorted() {
      // 絞り込み処理を行った後の配列を、表示するカラム（過去1時間、過去24時間、過去1週間のいずれか）の降順で並べ替え
      if (this.column === "tweet_hour") {
        return this.items.trend_hour;
      } else if (this.column === "tweet_day") {
        return this.items.trend_day;
      } else if (this.column === "tweet_week") {
        return this.items.trend_week;
      }
    },
  },
  methods: {
    // axiosでトレンド一覧取得APIにリクエスト
    async fetchTrends() {
      const response = await axios.get("/api/trend/latest");
      if (response.status !== OK) {
        // 通信失敗の場合
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // JSONのdata項目を格納
      this.items = response.data;
    },

    // Twitterアカウント一覧を取得
    async fetchTargets() {
      const response = await axios.get(`/api/twitter/latest`);
      // レスポンスのステータスが200以外の場合はエラーをストアにコミット
      if (response.status !== OK) {
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // JSONのdata項目を格納
      this.targets = response.data;
      return;
    },

    // axiosでニュース一覧取得APIにリクエスト
    async fetchNews() {
      const response = await axios.get(`/api/news/latest`);
      console.log(response.data);
      if (response.status !== OK) {
        // 通信失敗の場合
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // JSONのdata項目を格納
      this.news = response.data;
      return;
    },

    // sorted()で並べ替えるキーとなるカラム、タブ表示するカラムを指定
    sortByHour() {
      this.column = "tweet_hour";
    },
    sortByDay() {
      this.column = "tweet_day";
    },
    sortByWeek() {
      this.column = "tweet_week";
    },
  },
  watch: {
    // $routeを監視し、ページ切り替え時にデータ取得を実行
    $route: {
      async handler() {
        this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
        await this.fetchTrends();
        await this.fetchTargets();
        await this.fetchNews();
        this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
};
</script>
