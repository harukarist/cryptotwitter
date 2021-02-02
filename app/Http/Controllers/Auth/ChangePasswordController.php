<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChangePasswordRequest;

class ChangePasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        //ChangePasswordRequestでバリデーションチェックがOKであれば
        //DBのパスワードを更新
        $user = Auth::user();
        $user->password = bcrypt($request->new_password);
        $user->save();

        // 他のデバイスからのログインを全てログアウトするため、
        // app/Http/Kernel.php の AuthenticateSessionミドルウェアを有効にする

        // ユーザー情報を返却
        return response()->json(['status' => 200]);
    }
}
