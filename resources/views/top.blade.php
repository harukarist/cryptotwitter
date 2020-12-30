@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Login</div>

                <div class="card-body">
                    <button type="submit" class="btn btn-outline-primary">
                        {{-- TwitterColor: #00aced --}}
                        <a href="{{ route('auth.twitter') }}">
                            <i class="fab fa-twitter"></i>
                            Twitterでログイン・ユーザー登録
                        </a>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
