<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * お問い合わせフォームから問い合わせを送信したユーザーのメールアドレス宛に、
 * 受付完了メールを送信するためのMailableクラス
 * （メール送信処理は app/Http/Controllers/ContactController.php で実行）
 */
class ContactMail extends Mailable
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
     * お問い合わせ受付完了メールのメッセージを作成
     * @return $this
     */
    public function build()
    {
        // お問い合わせ受付完了メールのテンプレートcontact.blade.phpに入力内容を渡す
        return $this
            ->subject('お問い合わせを受け付けました')
            ->view('email.contact')
            ->with([
                'inputs' => $this->contact,
            ]);
    }
}
