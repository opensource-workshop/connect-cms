<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PostNotice extends Mailable
{
    use Queueable, SerializesModels;

    private $frame = null;
    private $bucket = null;
    private $post = null;
    private $show_method = null;
    private $notice_method = null;
    private $bucket_mail = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($frame, $bucket, $post, $show_method, $notice_method, $bucket_mail)
    {
        // 引数の保持
        $this->frame         = $frame;
        $this->bucket        = $bucket;
        $this->post          = $post;
        $this->notice_method = $notice_method;
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
        return $this->text('mail.post.post_text')
                    ->subject($this->bucket_mail->notice_subject)
                    ->with([
                        'frame'         => $this->frame,
                        'bucket'        => $this->bucket,
                        'post'          => $this->post,
                        'show_method'   => $this->show_method,
                        'notice_method' => $this->notice_method,
                        'bucket_mail'   => $this->bucket_mail,
                    ]);
    }
}
