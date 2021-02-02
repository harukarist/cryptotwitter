<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;

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

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    // リセットメールのURLからリクエストされるパスワードリセットフォームを表示
    public function showResetForm(Request $request, $token = null)
    {
        // return redirect("/test/reset")->with([
        //     'token' => $token,
        //     'email' => $request->email
        // ]);

        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    // public function reset(Request $request)
    // {
    //     $request->validate($this->rules(), $this->validationErrorMessages());
    //     $response = $this->broker()->reset(
    //         $this->credentials($request),
    //         function ($user, $password) {
    //             $this->resetPassword($user, $password);
    //         }
    //     );
    //     return $response == Password::PASSWORD_RESET
    //         ? response()->json([
    //             'message' => trans($response),
    //             'result' => 'OK'
    //         ])
    //         : response()->json([
    //             'message' => trans($response),
    //             'result' => 'NG'
    //         ]);
    // }
}
