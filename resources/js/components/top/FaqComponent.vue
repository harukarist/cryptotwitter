<template>
  <div class="p-faq">
    <fade-in-component>
      <h3 class="p-faq__title">よくあるご質問</h3>
    </fade-in-component>

    <fade-in-component>
      <div class="p-faq__contents">
        <div class="p-faq__item">
          <accordion-component>
            <div class="p-faq__question" slot="title">費用はかかりますか？</div>
            <div class="p-faq__answer" slot="body">
              利用料金は一切かかりません。全ての機能を無料でご利用いただけます。
            </div>
          </accordion-component>
        </div>
        <div class="p-faq__item">
          <accordion-component>
            <div class="p-faq__question" slot="title">どうやって使うの？</div>
            <div class="p-faq__answer" slot="body">
              PC、タブレット、スマートフォンから簡単にお使いいただけます。<br />
              まずは<RouterLink :to="{ name: 'register' }"
                >ユーザー登録</RouterLink
              >にて、メールアドレスとパスワードでアカウント登録いただくと、
              仮想通貨のツイート数ランキング、Twitterアカウント一覧、ニュース一覧をご覧いただけます。<br />
              お持ちのTwitterアカウントを登録いただき、自動フォロー機能を有効にすると、毎日、自動で仮想通貨関連アカウントをフォローできます。<br />
              自動フォロー機能はいつでも自由に停止、再開できます。
            </div>
          </accordion-component>
        </div>
        <div class="p-faq__item">
          <accordion-component>
            <div class="p-faq__question" slot="title">
              Twitterアカウントの登録は必須ですか？
            </div>
            <div class="p-faq__answer" slot="body">
              仮想通貨のツイート数ランキング、Twitterアカウント一覧、ニュース一覧は、Twitterアカウントを登録しなくても閲覧可能です。<br />
              Twitterアカウントの自動フォロー及びCryptoTrend上での個別フォローにはTwitterアカウントの登録が必要になります。
            </div>
          </accordion-component>
        </div>
        <div class="p-faq__item">
          <accordion-component>
            <div class="p-faq__question" slot="title">
              パスワードを忘れてしまいました。
            </div>
            <div class="p-faq__answer" slot="body">
              パスワードをお忘れの方は
              <RouterLink :to="{ name: 'password.request' }">
                パスワードリマインダーフォーム
              </RouterLink>
              にて、ご登録いただいたメールアドレスをご入力ください。<br />
              パスワード再発行のメールを送信いたします。
            </div>
          </accordion-component>
        </div>
        <div class="p-faq__item">
          <accordion-component>
            <div class="p-faq__question" slot="title">
              パスワード再発行メールが届きません。
            </div>
            <div class="p-faq__answer" slot="body">
              GmailやYahooメールなどのフリーメールをご利用の場合、メールが迷惑メールフォルダに振り分けられている場合がございます。<br />
              お手数ですが、迷惑メールフォルダをご確認ください。<br />
              迷惑メールフォルダにも届いていない場合、恐れ入りますが、再度、パスワード再発行のお手続きをお願いいたします。
            </div>
          </accordion-component>
        </div>
        <div class="p-faq__item">
          <accordion-component>
            <div class="p-faq__question" slot="title">
              利用しなくなったので退会したい
            </div>
            <div class="p-faq__answer" slot="body">
              ログイン後、「アカウント設定」画面よりアカウントの削除が可能です。
            </div>
          </accordion-component>
        </div>
      </div>
    </fade-in-component>
  </div>
</template>

<script>
import FadeInComponent from "../FadeInComponent.vue";
import AccordionComponent from "../AccordionComponent.vue";

export default {
  components: {
    AccordionComponent,
    FadeInComponent,
  },
};
</script>
