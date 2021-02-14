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
              主要な18銘柄について<br />
              Twitterのツイート数を<br class="u-md--only" />集計し、<br
                class="u-md--hidden"
              />
              今どの銘柄が<br class="u-md--only" />注目されているのかを<br />
              ランキング形式で<br class="u-md--only" />お届けします。
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
              仮想通貨に関心のある<br />
              Twitterアカウントだけを<br />
              自動的にフォローします。<br />
              運用の手間をかけずに<br />
              フォロー数を<br class="u-md--only" />増やすことができます。
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
              仮想通貨についての<br
                class="u-md--only"
              />ニュースに特化しており<br />
              余計な情報に<br class="u-md--only" />振り回されることなく、<br />
              効率よく必要な情報を<br class="u-md--only" />チェックできます。
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
