<?php

namespace App\Http\Controllers;

use App\Contact;
use App\Mail\ContactMail;
use App\Mail\ContactAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\CreateContactRequest;

/**
 * お問い合わせフォームから送信された内容の確認と
 * メール送信を行うクラス
 */
class ContactController extends Controller
{
    /**
     * CreateContactRequestでお問い合わせフォームから送信された内容を
     * バリデーションチェックし、問題なければ
     * vueの入力内容確認ページに入力内容を返却する。
     */
    public function confirm(CreateContactRequest $request)
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
    public function send(CreateContactRequest $request)
    {
        // フォームから受け取った値を取得
        $inputs = $request->all();

        // 正常に送信ボタンが押された場合はMailファサードで入力されたメールアドレスにメールを送信
        // （app/Mail のMailableクラスを使用する）
        Mail::to($inputs['email'])->send(new ContactMail($inputs));
        Mail::to(config('mail.from.address'))->send(new ContactAdmin($inputs));

        // 入力された内容をcontactsテーブルに保存
        $contact = new Contact();
        $contact->fill($inputs);
        $contact->save();

        // レスポンスを返却
        return response()->json();
    }
}
