{{ $inputs['name'] }} 様<br>
<br>
CryptoTrendをご利用いただき、ありがとうございます。<br>
下記の内容でお問い合わせを受け付けました。<br>
<br>
■ お名前<br>
{{ $inputs['name'] }}<br>
<br>
■ メールアドレス<br>
{{ $inputs['email'] }}<br>
<br>
<br>
■ お問い合わせ内容<br>
{!! nl2br($inputs['message']) !!}<br>
<br>
<br>
本メールアドレスは配信専用です。<br>
ご返信の際は<a href="{{ config('app.url')."/contact"  }}">お問い合わせフォーム</a>をご利用ください。<br>

{{ config('app.url')."/contact"  }}<br>
<br>
<br>
================================<br>
CryptoTrend<br>
{{ config('app.url') }}<br>

