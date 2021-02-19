<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * お問い合わせフォームのメール送信に必要な情報をまとめた
 * Mailableクラス
 */
class ContactAdmin extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inputs)
    {
        // フォームに入力された各項目を格納
        $this->contact = $inputs;
    }

    /**
     * Build the message.
     * 問合せ受付完了メールのメッセージを作成
     * @return $this
     */
    public function build()
    {
        // 問合せ受付完了メールのテンプレートcontact.blade.phpに入力内容を渡す
        return $this
            ->subject('【' . config('app.name') . '】お問い合わせが届きました')
            ->view('email.contact_admin')
            ->with([
                'inputs' => $this->contact,
            ]);
    }
}
