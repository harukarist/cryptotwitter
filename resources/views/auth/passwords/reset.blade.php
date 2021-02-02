@extends('layouts.app')

@section('content')
<div class="c-container--bg">
    <h2 class="c-container__title">パスワードの再設定</h2>
        <div class="c-form__wrapper">
                    <form method="POST" action="{{ route('password.update') }}" class="c-form--small">
                        @csrf

                        <p class="c-form__text">
                            新しいパスワードを設定してください。<br />
                          </p>

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="c-form__group">
                            <label for="email" class="c-form__label">{{ __('E-Mail Address') }}</label>

                            <input id="email" type="email" class="form-control c-input c-input--large c-input--box @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                            @error('email')
                                <span class="c-valid__error" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="c-form__group">
                            <label for="password" class="c-form__label">{{ __('Password') }}
                            <span class="c-form__notes">半角英数字8文字以上</span></label>

                            <input id="password" type="password" class="form-control c-input c-input--large c-input--box @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                            @error('password')
                                <span class="c-valid__error" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="c-form__group">
                            <label for="password-confirm" class="c-form__label">{{ __('Confirm Password') }}</label>

                            <input id="password-confirm" type="password" class="form-control c-input c-input--large c-input--box" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="c-form__button">
                            <button type="submit" class="c-btn__main-outline">
                                {{ __('Reset Password') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
