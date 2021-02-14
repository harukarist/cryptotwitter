@extends('layouts.app')

@section('content')
<div class="c-container--bg">
    <h2 class="c-container__title">パスワードをお忘れの方</h2>
        <div class="c-form__wrapper">
            @if (session('status'))
                <div class="c-alert--success c-fade-in" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <p class="c-form__text">
                    ユーザー登録時にご登録いただいたメールアドレスをご入力ください。<br />
                    メールアドレス宛に、パスワード変更ページのURLが記載されたメールが送信されます。<br />
                  </p>

                <div class="c-form__group">
                    <label for="email" class="c-form__label">{{ __('E-Mail Address') }}</label>

                    <input id="email" type="email" class="form-control c-input c-input--large @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                        <span class="c-valid__error" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                </div>

                <div class="c-form__button">
                        <button type="submit" class="c-btn__main--outline">
                            送信する
                        </button>
                </div>
            </form>
            <div class="c-form__link">
                <a href="/login" class="c-form__link">
                  ログインページへ戻る
                </a>
            </div>
        </div>
</div>
@endsection
