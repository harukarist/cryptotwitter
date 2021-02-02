<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ChangeAccountRequest;

class ChangeAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function changeAccount(ChangeAccountRequest $request)
    {
        //ChangePasswordRequestでバリデーションチェックがOKで
        //DBの内容と変更があれば更新
        $user = Auth::user();
        if ($user->name !== $request->name) {
            $user->name = $request->name;
            $user->save();
        }
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->save();
        }

        // ユーザー情報を返却
        return $user;
    }
    
}
