<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteNotice extends Mailable
{
    use Queueable, SerializesModels;

    private $notice_embedded_tags = null;
    private $bucket_mail = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $notice_embedded_tags, $bucket_mail)
    {
        // 引数の保持
        $this->notice_embedded_tags = $notice_embedded_tags;
        $this->bucket_mail          = $bucket_mail;
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
                        'notice_embedded_tags' => $this->notice_embedded_tags,
                        'bucket_mail'          => $this->bucket_mail,
                    ]);
    }
}
