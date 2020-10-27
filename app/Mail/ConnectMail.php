<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Contracts\Queue\ShouldQueue;

class ConnectMail extends Mailable
{
    use Queueable, SerializesModels;

    // 引数で受け取る変数
    protected $options;
    protected $data;

    // コンストラクタ設定
    public function __construct($options, $data)
    {
        // 引数で受け取ったデータを変数にセット
        $this->options = $options;
        $this->data    = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->subject($this->options['subject'])
                        ->text($this->options['template'], $this->data);

        // 添付ファイル
        if (isset($this->options['attachs'])) {
            foreach ($this->options['attachs'] as $attach) {
                $mail->attach($attach['file_path'], [
                    'as' => $attach['file_name'],
                    'mime' => $attach['mime'],
                ]);
            }
        }

        return $mail;
    }
}
