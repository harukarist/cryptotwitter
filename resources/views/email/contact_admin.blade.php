<h3>{{ config('app.name') }} 問い合わせフォームからのご連絡</h3>
<br>
{{ $inputs['name'] }} 様より下記のお問い合わせが送信されました。<br>
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
================================<br>
CryptoTrend<br>
{{ config('app.url') }}<br>

