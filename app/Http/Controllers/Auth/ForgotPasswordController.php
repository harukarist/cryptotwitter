<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;


/**
 * パスワードリマインダーフォームの表示および
 * パスワード再設定フォームへのURLを記載したメールの送信を
 * 行うコントローラー
 */
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    /**
     * vendor/laravel/framework/src/Illuminate/Foundation/Auth/SendsPasswordResetEmails.php の
     * SendsPasswordResetEmailsトレイトを使用
     */

    use SendsPasswordResetEmails;

    /**
     * パスワードリマインダフォーム表示
     */
    public function showLinkRequestForm()
    {
        // return view('auth.passwords.email');
        return redirect('/pass/request');
    }

    /**
     * パスワードリセットメール送信
     */
    public function sendResetLinkEmail(Request $request)
    {
        //パスワードリセットメール送信のリクエストをチェックする
        $this->validateEmail($request);
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );
        // 結果をJSON形式で返却
        return $response == Password::RESET_LINK_SENT
            ? response()->json([
                'message' => trans($response),
                'result' => 'success'
            ])
            : response()->json([
                'message' => trans($response),
                'result' => 'failed'
            ]);
    }
}
