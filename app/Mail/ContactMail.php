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
        // $this->name = $inputs['name'];
        // $this->email = $inputs['email'];
        // $this->message  = $inputs['message'];
    }

    /**
     * Build the message.
     * 問合せ受付完了メールのメッセージを作成
     * @return $this
     */
    public function build()
    {
        // メール本文のテンプレート contact.blade.phpに入力内容を渡す
        return $this
            ->subject('お問い合わせを受け付けました')
            ->view('mail.contact')
            ->with([
                'inputs' => $this->contact,
            ]);
        // return $this
        //     ->subject('お問い合わせを受け付けました')
        //     ->view('mail.contact')
        //     ->with([
        //         'name' => $this->name,
        //         'email' => $this->email,
        //         'message'  => $this->message,
        //     ]);
    }
}
