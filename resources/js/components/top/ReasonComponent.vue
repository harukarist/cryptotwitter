<template>
  <div class="p-reason">
    <fade-in-component>
      <h3 class="p-reason__title">
        CryptoTrendが選ばれる<br class="u-sp--only" /><span
          class="u-font__large"
          >3つの理由</span
        >
      </h3>
    </fade-in-component>

    <div class="p-reason__contents">
      <div class="p-reason__item">
        <div class="p-reason__description">
          <span class="p-reason__num">01</span>
          <fade-in-component>
            <h4 class="p-reason__heading">
              仮想通貨の各銘柄が<br class="u-sp--only" />どのぐらいツイート<br
                class="u-sp--only"
              />されているかを<br />
              過去1時間・24時間・<br class="u-sp--only" />1週間でランキング表示
            </h4>
            <p class="p-reason__text">
              Coincheckにて取り扱われている<br
                class="u-sp--only"
              />全18銘柄の仮想通貨について<br />
              仮想通貨の銘柄名をキーワードに<br
                class="u-sp--only"
              />含むツイート数を集計し、<br />
              最新データをランキングで表示。<br />
              変化の激しい仮想通貨の<br
                class="u-sp--only"
              />最新トレンドを手軽に<br class="u-sp--only" />チェックできます。
            </p>
          </fade-in-component>
        </div>
        <div class="p-reason__img-wrapper">
          <fade-in-component>
            <img
              src="/img/img_reason_01.png"
              class="p-reason__img"
              alt="ランキング表示アイコン"
            />
          </fade-in-component>
        </div>
      </div>

      <div class="p-reason__item">
        <div class="p-reason__description">
          <span class="p-reason__num">02</span>
          <fade-in-component>
            <h4 class="p-reason__heading">
              仮想通貨関連の<br class="u-sp--only" />Twitterユーザーに<br />
              ターゲットを絞った<br class="u-sp--only" />自動フォロー
            </h4>
            <p class="p-reason__text">
              仮想通貨に関心を持っている<br
                class="u-sp--only"
              />1,500以上のTwitterアカウントを<br />
              自動でフォロー。<br
                class="u-sp--only"
              />お使いのTwitterアカウントを<br
                class="u-sp--only"
              />登録するだけで<br />
              手間なくフォロー数を<br
                class="u-sp--only"
              />増やすことができます。<br />
              個別のアカウントフォロー、<br
                class="u-sp--only"
              />最新ツイートのチェックも<br
                class="u-sp--only"
              />可能です。<br />
            </p>
          </fade-in-component>
        </div>
        <div class="p-reason__img-wrapper">
          <fade-in-component>
            <img
              src="/img/img_reason_02.png"
              class="p-reason__img"
              alt="自動フォローアイコン"
            />
          </fade-in-component>
        </div>
      </div>

      <div class="p-reason__item">
        <div class="p-reason__description">
          <span class="p-reason__num">03</span>
          <fade-in-component>
            <h4 class="p-reason__heading">
              仮想通貨関連の<br class="u-sp--only" />キーワードを含む<br />
              最新ニュースを<br class="u-sp--only" />厳選してお届け
            </h4>
            <p class="p-reason__text">
              仮想通貨に関する最新ニュースを<br
                class="u-sp--only"
              />Googleから随時お届けします。<br />
              膨大な情報の中から<br
                class="u-sp--only"
              />仮想通貨のみにテーマを絞って、<br
                class="u-sp--only"
              />いつでも<br class="u-sp--hidden" />必要な情報だけを<br
                class="u-sp--only"
              />効率良くチェックできます。
            </p>
          </fade-in-component>
        </div>
        <div class="p-reason__img-wrapper">
          <fade-in-component>
            <img
              src="/img/img_reason_03.png"
              class="p-reason__img"
              alt="ニュース表示アイコン"
            />
          </fade-in-component>
        </div>
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
