<template>
  <div class="p-solution">
    <fade-in-component>
      <h3 class="p-solution__title">
        そんなお悩みを<br class="u-sp--only" /><span class="u-font--large"
          >CryptoTrend</span
        >が<br class="u-sp--only" />解決します！
      </h3>
    </fade-in-component>

    <div class="p-solution__contents">
      <div class="p-solution__item">
        <fade-in-component>
          <div class="p-solution__description">
            <h4 class="p-solution__heading">
              各銘柄のツイート数<br />
              ランキング表示
            </h4>
            <p class="p-solution__text">
              主要な18銘柄について、Twitterの最新ツイート数を集計し、今どの銘柄が最も注目されているのかをランキング形式でお届けします。
            </p>
          </div>
          <div class="p-solution__img-wrapper">
            <img src="/img/img_solution_01.png" alt="ソリューションアイコン" />
          </div>
        </fade-in-component>
      </div>

      <div class="p-solution__item">
        <fade-in-component>
          <div class="p-solution__description">
            <h4 class="p-solution__heading">
              仮想通貨に特化した<br />
              Twitter自動フォロー
            </h4>
            <p class="p-solution__text">
              仮想通貨に関心のある3,000件以上のTwitterアカウントだけを自動的にフォローします。
              運用の手間をかけずに、フォロー数を増やすことができます。
            </p>
          </div>
          <div class="p-solution__img-wrapper">
            <img src="/img/img_solution_02.png" alt="ソリューションアイコン" />
          </div>
        </fade-in-component>
      </div>

      <div class="p-solution__item">
        <fade-in-component>
          <div class="p-solution__description">
            <h4 class="p-solution__heading">
              仮想通貨に関する<br />
              ニュースのみを表示
            </h4>
            <p class="p-solution__text">
              仮想通貨についてのニュースに特化しているため、余計な情報に振り回されることなく、毎日のスキマ時間に効率よく、必要な情報をチェックできます。
            </p>
          </div>
          <div class="p-solution__img-wrapper">
            <img src="/img/img_solution_03.png" alt="ソリューションアイコン" />
          </div>
        </fade-in-component>
      </div>
    </div>
  </div>
</template>

<script>
import FadeInComponent from "../FadeInComponent.vue";

export default {
  components: { FadeInComponent },
};
</script>
