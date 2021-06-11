<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteNotice extends Mailable
{
    use Queueable, SerializesModels;

    private $frame = null;
    private $bucket = null;
    private $id = null;
    private $show_method = null;
    private $delete_comment = null;
    private $bucket_mail = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($frame, $bucket, $post, $show_method, $delete_comment, $bucket_mail)
    {
        // 引数の保持
        $this->frame          = $frame;
        $this->bucket         = $bucket;
        $this->post           = $post;
        $this->delete_comment = $delete_comment;
        $this->show_method    = $show_method;
        $this->bucket_mail    = $bucket_mail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->text('mail.post.delete_text')
                    ->subject($this->bucket_mail->notice_subject)
                    ->with([
                        'frame'          => $this->frame,
                        'bucket'         => $this->bucket,
                        'post'           => $this->post,
                        'show_method'    => $this->show_method,
                        'delete_comment' => $this->delete_comment,
                        'bucket_mail'    => $this->bucket_mail,
                    ]);
    }
}
