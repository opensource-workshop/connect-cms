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
         return $this->subject($this->options['subject'])
             ->text($this->options['template'], $this->data);
    }
}
