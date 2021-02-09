<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovalNotice extends Mailable
{
    use Queueable, SerializesModels;

    private $frame = null;
    private $bucket = null;
    private $id = null;
    private $show_method = null;
    private $bucket_mail = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($frame, $bucket, $id, $show_method, $bucket_mail)
    {
        // 引数の保持
        $this->frame         = $frame;
        $this->bucket        = $bucket;
        $this->id            = $id;
        $this->show_method   = $show_method;
        $this->bucket_mail   = $bucket_mail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->text('mail.post.approval_text')
                    ->subject($this->bucket_mail->approval_subject)
                    ->with([
                        'frame'         => $this->frame,
                        'bucket'        => $this->bucket,
                        'id'            => $this->id,
                        'show_method'   => $this->show_method,
                        'bucket_mail'   => $this->bucket_mail,
                    ]);
    }
}
