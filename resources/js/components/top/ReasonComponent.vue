<template>
  <div class="p-reason">
    <fade-in-component>
      <h3 class="p-reason__title">
        CryptoTrendが選ばれる<br class="u-sp--only" /><span
          class="u-font--large"
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
              仮想通貨の各銘柄がどのぐらいツイートされているかを<br
                class="u-md--hidden"
              />過去1時間・24時間・1週間でランキング表示
            </h4>
            <p class="p-reason__text">
              Coincheckにて取り扱われている全18銘柄の仮想通貨について<br
                class="u-md--hidden"
              />
              仮想通貨の銘柄名をキーワードに含むツイート数を集計し、<br
                class="u-md--hidden"
              />
              最新データをランキングで表示。<br
                class="u-md--hidden"
              />変化の激しい仮想通貨の最新トレンドを手軽にチェックできます。
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
              仮想通貨関連のTwitterユーザーに<br
                class="u-sp--hidden"
              />ターゲットを絞った自動フォロー
            </h4>
            <p class="p-reason__text">
              仮想通貨に関心を持っている1,500以上のTwitterアカウントを<br
                class="u-md--hidden"
              />
              自動でフォロー。お使いのTwitterアカウントを登録するだけで<br
                class="u-md--hidden"
              />
              手間なくフォロー数を増やすことができます。<br
                class="u-md--hidden"
              />
              個別のアカウントフォロー、最新ツイートのチェックも可能です。<br
                class="u-md--hidden"
              />
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
              仮想通貨関連のキーワードを含む<br
                class="u-sp--hidden"
              />最新ニュースを厳選してお届け
            </h4>
            <p class="p-reason__text">
              仮想通貨に関する最新ニュースをGoogleから随時お届けします。<br
                class="u-md--hidden"
              />
              膨大な情報の中から仮想通貨のみにテーマを絞って、いつでも<br
                class="u-md--hidden"
              />必要な情報だけを効率良くチェックできます。
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
