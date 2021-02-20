<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;

/**
 * パスワードリマインダーメール送信後のパスワードリセットを行う
 * コントローラー
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
     * vendor/laravel/framework/src/Illuminate/Foundation/Auth/ResetsPasswords.php のResetsPasswordsトレイトを使用
     */
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * リセットメールのURLからリクエストされるパスワードリセットフォームを表示
     */
    public function showResetForm(Request $request, $token = null)
    {
        // Vueコンポーネントのパスワードリセットフォームを表示
        return redirect("/pass/reset/{$token}?email={$request->email}");
    }

    /**
     * パスワードリセット処理を上書き
     */
    public function reset(Request $request)
    {
        clock($request);
        $request->validate($this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );
        // bladeテンプレートの返却ではなく、Vueコンポーネントにメッセージと成否フラグを返却するように変更
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * パスワード変更が成功した時の処理
     */
    protected function sendResetResponse(Request $request, $response)
    {
        // Vueコンポーネントにメッセージと成否フラグを返却
        return ([
            'status' => trans($response), //バリデーションメッセージ
            'result' => 'success', //成否フラグ
            'user' => Auth::user()
        ]);
    }

    /**
     * パスワード変更が失敗した時の処理
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        // Vueコンポーネントにメッセージと成否フラグを返却
        return ([
            'status' => trans($response), //バリデーションメッセージ
            'result' => 'failed', //成否フラグ
        ]);
    }
}
