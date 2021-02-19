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
class ExceptionMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($error)
    {
        // フォームに入力された各項目を格納
        $this->error = $error;
    }

    /**
     * Build the message.
     * サーバーエラー発生通知メールのメッセージを作成
     * @return $this
     */
    public function build()
    {
        // エラー通知メールのテンプレートexception.blade.phpに入力内容を渡す
        return $this
            ->subject('【' . config('app.name') . '】[' . ENV('APP_ENV') . '] サーバーエラー発生')
            ->view('email.exception')
            ->with([
                'error' => $this->error,
            ]);
    }
}
