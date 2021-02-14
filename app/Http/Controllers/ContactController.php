<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\ContactMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ContactRequest;

/**
 * お問い合わせフォームから送信された内容の確認と
 * メール送信を行うクラス
 */
class ContactController extends Controller
{
    /**
     * ContactRequestでお問い合わせフォームから送信された内容を
     * バリデーションチェックし、問題なければ
     * vueの入力内容確認ページに入力内容を返却する。
     */
    public function confirm(ContactRequest $request)
    {
        //フォームから受け取ったすべてのinputの値を取得
        $inputs = $request->all();

        //入力内容確認ページのvueに変数を渡して表示
        return $inputs;
    }

    /**
     * vueの入力内容確認ページから送信された内容を
     * バリデーションチェックし、問題なければメールを送信する。
     */
    public function send(ContactRequest $request)
    {
        // フォームから受け取った値を取得
        $inputs = $request->all();

        // 正常に送信ボタンが押された場合は入力されたメールアドレスにメールを送信
        // （app/Mail/ContactMail.php のMailableクラスを使用する）
        Mail::to($inputs['email'])->send(new ContactMail($inputs));

        // レスポンスを返却
        return response()->json();
    }
}
