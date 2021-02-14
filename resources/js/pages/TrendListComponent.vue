<template>
  <div class="c-container--bg">
    <section class="c-section">
      <h5 class="c-section__title">トレンド一覧</h5>
      <p class="c-section__text">
        銘柄名を含むツイートの数を<br
          class="u-sp--only"
        />ランキング形式でお届けします。<br />
        気になる銘柄のツイート数を<br
          class="u-sp--only"
        />チェックしてみましょう。<br />
        銘柄名のリンクをクリックすると、<br
          class="u-sp--only"
        />Twitterで該当銘柄のツイートを<br
          class="u-sp--only"
        />検索できます。<br />
      </p>
      <div class="c-tab c-fade-in">
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
          <div class="p-trend__head">
            <div class="p-trend__head-left">
              <span
                v-if="
                  !selectedItems.length ||
                  items.trends.length === selectedItems.length
                "
                class="u-font--small u-font--muted"
              >
                全{{ sorted.length }}銘柄を表示
              </span>
              <span v-else class="u-font--small u-font--muted">
                {{ selectedItems.length }}件を絞り込み表示
              </span>
              <button
                class="c-btn--muted--outline"
                @click="isActive = !isActive"
              >
                <i v-show="!isActive" class="fas fa-angle-down"></i>
                <i v-show="isActive" class="fas fa-angle-up"></i>
                銘柄名で絞り込み
              </button>
            </div>
            <div class="p-trend__head-right">
              <div class="u-font--small u-font--muted">
                更新日時 <br class="u-sp--only" />{{ items.updated_at }}
              </div>
            </div>
          </div>
          <slide-down-component>
            <div v-show="isActive" class="p-trend__select">
              <p class="p-trend__select-text">表示する銘柄を選択</p>
              <i
                class="fas fa-times p-trend__select-close"
                @click="isActive = false"
              ></i>
              <ul class="p-trend__select-list">
                <li
                  v-for="trend in items.trends"
                  :key="trend.id"
                  class="p-trend__select-item"
                >
                  <input
                    type="checkbox"
                    class="c-checkbox__icon"
                    v-bind:id="trend.id"
                    v-bind:value="trend.id"
                    v-model="selectedItems"
                  />
                  <label v-bind:for="trend.id">
                    {{ trend.currency_name }}
                  </label>
                </li>
              </ul>
              <div class="p-trend__select-footer">
                <a
                  class="c-btn--muted--outline"
                  v-if="selectedItems.length"
                  @click="deselect()"
                >
                  選択をすべて解除
                </a>
                <a class="c-btn--muted--outline" v-else @click="selectAll()">
                  すべてを選択
                </a>
              </div>
            </div>
          </slide-down-component>

          <table class="c-table c-fade-in">
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
            <transition-group
              tag="tbody"
              name="flip-list"
              class="c-table__tbody"
            >
              <tr
                v-for="(trend, index) in sorted"
                v-show="trend.show"
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
            </transition-group>
          </table>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { OK } from "../utility";
import SlideDownComponent from "../components/SlideDownComponent.vue";

export default {
  components: { SlideDownComponent },
  data() {
    return {
      column: "tweet_hour",
      items: [], //トレンド一覧を格納する配列を用意
      isActive: false,
      selectedItems: [], // 絞り込み表示する銘柄のidを格納する
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
    // 銘柄名を指定して絞り込み表示
    matched() {
      // 絞り込み表示の配列に要素が入っている場合
      if (this.selectedItems.length) {
        // トレンド一覧の配列をlodashで展開し、絞り込み表示の配列と一致する要素のidをshowプロパティに代入
        var arr = this.selectedItems;
        return _.forEach(this.items.trends, function (item) {
          item.show = arr.includes(item.id);
        });
      }
      // 絞り込み表示の指定がない場合はAPIで取得したトレンド一覧の配列を展開し、showプロパティにtrueを指定
      return _.forEach(this.items.trends, function (item) {
        item.show = true;
      });
    },
    // ツイート数の大きい順に並べ替えて表示
    sorted() {
      // 絞り込み処理を行った後の配列を、表示するカラム（過去1時間、過去24時間、過去1週間のいずれか）の降順で並べ替え
      return _.orderBy(this.matched, this.column, "desc");
    },
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
  },
  methods: {
    // axiosでトレンド一覧取得APIにリクエスト
    async fetchTrends() {
      const response = await axios.get("/api/trend");
      if (response.status !== OK) {
        // 通信失敗の場合
        this.$store.commit("error/setCode", response.status);
        return false; //処理を中断
      }
      // JSONのdata項目を格納
      this.items = response.data;
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
    // 絞り込みを解除
    deselect() {
      // 絞り込み表示の配列を空にする
      this.selectedItems = [];
      // // APIで取得したトレンド一覧の配列を展開し、showプロパティにtrueを指定
      // return _.each(this.items.trends, function (item) {
      //   item.show = true;
      // });
    },
    // 銘柄をすべて選択
    selectAll() {
      // 絞り込み表示の配列を一旦空にする
      this.selectedItems = [];
      // APIで取得したトレンド一覧の配列を展開し、showプロパティにtrueを指定
      this.selectedItems = _.map(this.items.trends, "id");
    },
  },
  watch: {
    // $routeを監視し、ページ切り替え時にデータ取得を実行
    $route: {
      async handler() {
        this.$store.commit("loader/setIsLoading", true); //ローディング表示をオン
        await this.fetchTrends();
        this.$store.commit("loader/setIsLoading", false); //ローディング表示をオフ
      },
      immediate: true, //コンポーネント生成時も実行
    },
  },
};
</script>
