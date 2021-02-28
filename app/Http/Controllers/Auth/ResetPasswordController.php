<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;

/**
 * パスワード再設定メールに書かれたURLを経由した
 * パスワードリセットフォームの表示、及び、
 * ログインユーザーのパスワードリセット処理を行うコントローラー
 */
class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */


    /**
     * vendor/laravel/framework/src/Illuminate/Foundation/Auth/ResetsPasswords.php のResetsPasswordsトレイトを使用する
     */
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     * パスワードリセット後のリダイレクト先として、ホーム画面を指定
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * パスワードリセットフォームを表示するためのメソッド
     */
    public function showResetForm(Request $request, $token = null)
    {
        // トークンとメールアドレスをクエリパラメータに含めたパスをリダイレクト先に指定して
        // Vueコンポーネントのパスワードリセットフォームを表示する
        return redirect("/password/reset/form/{$token}?email={$request->email}");
    }

    /**
     * ResetsPasswordsトレイトのパスワードリセット処理を
     * BladeテンプレートではなくVueコンポーネントにレスポンスを返すように
     * 処理を上書きするためのメソッド
     */
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );
        // パスワード変更が成功した時、失敗した時の処理を指定する下記のメソッドで
        // Vueコンポーネントにメッセージと成否フラグを返却する
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * パスワード変更が成功した時の処理を指定するメソッド
     */
    protected function sendResetResponse(Request $request, $response)
    {
        // Vueコンポーネントにメッセージと成否フラグを返却する
        return ([
            'status' => trans($response), //バリデーションメッセージ
            'result' => 'success', //成否フラグ
            'user' => Auth::user()
        ]);
    }

    /**
     * パスワード変更が失敗した時の処理を指定するメソッド
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        // Vueコンポーネントにメッセージと成否フラグを返却する
        return ([
            'status' => trans($response), //バリデーションメッセージ
            'result' => 'failed', //成否フラグ
        ]);
    }
}
