<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

/**
 * ユーザー登録処理を行うコントローラー
 */
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */


    /**
     * vendor/laravel/framework/src/Illuminate/Foundation/Auth/RegistersUsers.php の RegistersUsersトレイトを使用
     */
    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     * コントローラーのインスタンスを生成するコンストラクタ
     * @return void
     */
    public function __construct()
    {
        // ゲスト権限のミドルウェアを指定し、未認証ユーザーのみアクセス可能とする
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     * ユーザー登録フォームのバリデーションチェックを行うメソッド
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:20'],
            'email' => [
                'required', 'string', 'email', 'max:50',
                Rule::unique('users')->where(function ($query) {
                    // 論理削除されていないレコードのみ、emailカラムのunique制約を指定
                    return $query->whereNull('deleted_at');
                }),

            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     * usersテーブルにユーザー情報のレコードを登録するメソッド
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * The user has been registered.
     * ユーザ登録完了後の処理を行うメソッド
     */
    protected function registered(Request $request, $user)
    {
        // ユーザ登録完了後に登録ユーザ情報を返却するよう、
        // RegistersUsersトレイトのregistered()を上書き
        return $user;
    }
}
