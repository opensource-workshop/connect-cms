<?php

namespace App\Jobs;

use App\Mail\PostNotice;
use App\Models\Common\BucketsMail;
use App\Traits\ConnectMailTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class PostNoticeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConnectMailTrait;

    /**
     * 最大試行回数
     * メールは大量送信時に途中で失敗した場合、同じメールが初めの方の人に再度送られるとまずいため、自動リトライしないため１回にする。
     *
     * @var int
     */
    public $tries = 1;

    private $bucket = null;
    private $notice_embedded_tags = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bucket, array $notice_embedded_tags)
    {
        // buckets などの受け取り
        $this->bucket               = $bucket;
        $this->notice_embedded_tags = $notice_embedded_tags;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // buckets_mails の取得
        $bucket_mail = BucketsMail::firstOrNew(['buckets_id' => $this->bucket->id]);

        // 送信者メールとグループから、通知するメールアドレス取得
        $notice_addresses = $bucket_mail->getEmailFromAddressesAndGroups($bucket_mail->notice_addresses, $bucket_mail->notice_groups, $bucket_mail->notice_everyone);

        // エラーチェック
        if (empty($notice_addresses)) {
            $this->saveAppLog($bucket_mail->plugin_name, "送信メールアドレスなし。bucket_name = {$this->bucket->bucket_name} buckets_id = {$this->bucket->id}");
            return;
        }

        // メール送信
        foreach ($notice_addresses as $notice_address) {
            Mail::to($notice_address)->send(new PostNotice($this->notice_embedded_tags, $bucket_mail));

            $this->saveAppLog($bucket_mail->plugin_name, $notice_address);
        }
    }
}
