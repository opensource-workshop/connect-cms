<?php

namespace App\Mail;

use App\Models\Core\Configs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RelateNotice extends Mailable
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
        $this->text('mail.post.relate_text')
            ->subject($this->bucket_mail->getFormattedSubject($this->bucket_mail->relate_subject, $this->notice_embedded_tags))
            ->with([
                'notice_embedded_tags' => $this->notice_embedded_tags,
                'bucket_mail'          => $this->bucket_mail,
            ]);

        // メール配信管理の使用
        if (Configs::getSharedConfigsValue('use_unsubscribe', '0') == '1') {
            // メール購読解除のヘッダー追加（＋対象ヘッダーのDKIM署名必要）
            $this->withSwiftMessage(function ($message) {
                $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                $message->getHeaders()->addTextHeader('List-Unsubscribe', '<' . route('get_unsubscribe') . '>');
            });
        }

        return $this;
    }
}
