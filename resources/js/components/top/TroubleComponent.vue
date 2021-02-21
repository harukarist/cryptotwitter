<template>
  <div class="p-trouble">
    <fade-in-component>
      <p class="u-font--muted u-font--small">
        仮想通貨に興味はあるけれど…
      </p>
      <h3 class="p-trouble__title">
        こんな<span class="u-font--large">「お悩み」</span><br class="u-sp--only">抱えていませんか？
      </h3>
    </fade-in-component>

    <div class="p-trouble__contents">
      <div class="p-trouble__item">
        <fade-in-component>
          <div class="p-trouble__comment">
            仮想通貨取引を<br class="u-md--only">始めたいけれど<br>
            銘柄が色々ありすぎて<br>
            どれがいいのか<br class="u-md--only">よくわからない…
          </div>
          <div class="p-trouble__img-wrapper">
            <img
              src="/img/img_trouble_01.png"
              alt="お悩みアイコン">
          </div>
        </fade-in-component>
      </div>

      <div class="p-trouble__item">
        <fade-in-component>
          <div class="p-trouble__comment">
            Twitterをもっと<br class="u-md--only">活用したいけれど<br>
            フォローや<br class="u-md--only">ツイートチェックの<br>
            手間がかかる…<br>
          </div>
          <div class="p-trouble__img-wrapper">
            <img
              src="/img/img_trouble_02.png"
              alt="お悩みアイコン">
          </div>
        </fade-in-component>
      </div>

      <div class="p-trouble__item">
        <fade-in-component>
          <div class="p-trouble__comment">
            たくさん情報が<br class="u-md--only">ありすぎて<br>
            本当に必要な<br class="u-md--only">ニュースを<br>
            見逃してしまう…<br>
          </div>
          <div class="p-trouble__img-wrapper">
            <img
              src="/img/img_trouble_03.png"
              alt="お悩みアイコン">
          </div>
        </fade-in-component>
      </div>
    </div>
  </div>
</template>

<script>
import FadeInComponent from '../FadeInComponent.vue'

export default {
	components: { FadeInComponent },
}
</script>
