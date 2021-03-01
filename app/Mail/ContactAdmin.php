<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * お問い合わせフォームから問い合わせが送信された場合に、
 * 管理者宛に通知メールを送信するためのMailableクラス
 * （メール送信処理は app/Http/Controllers/ContactController.php で実行）
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
     * お問い合わせ通知メールのメッセージを作成
     * @return $this
     */
    public function build()
    {
        // お問い合わせ通知メールのテンプレートcontact_admin.blade.phpに入力内容を渡す
        return $this
            ->subject('【' . config('app.name') . '】お問い合わせが届きました')
            ->view('email.contact_admin')
            ->with([
                'inputs' => $this->contact,
            ]);
    }
}
