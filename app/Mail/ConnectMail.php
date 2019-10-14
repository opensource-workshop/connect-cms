<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ConnectMail extends Mailable
{
    use Queueable, SerializesModels;

    // 引数で受け取る変数
    protected $mail_subject;
    protected $content;

    // コンストラクタ設定
    public function __construct($mail_subject, $content)
    {
        // 引数で受け取ったデータを変数にセット
        $this->mail_subject = $mail_subject;
        $this->content      = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      return $this
          ->subject($this->mail_subject)   // メールタイトル
          ->text('mail.send')      // メール本文のテンプレートとなるviewを設定
          ->with(['content' => $this->content]);  // withでセットしたデータをviewへ渡す

// env()は設定がキャッシュされたら取れない。from 指定しないと、設定から送信してくれた。
//          ->from('info@osws.jp') // 送信元
//          ->from(env('MAIL_FROM_ADDRESS')) // 送信元
    }
}
