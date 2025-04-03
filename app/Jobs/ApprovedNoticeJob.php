<?php

namespace App\Jobs;

use App\Mail\ApprovedNotice;
use App\Models\Common\BucketsMail;
use App\Traits\ConnectMailTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class ApprovedNoticeJob implements ShouldQueue
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
    private $created_id = null;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bucket, array $notice_embedded_tags, $created_id)
    {
        // buckets などの受け取り
        $this->bucket               = $bucket;
        $this->notice_embedded_tags = $notice_embedded_tags;
        $this->created_id           = $created_id;
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

        // 承認済み通知の 送信者メール,グループ,投稿者へ通知 から、通知するメールアドレス取得
        $approved_addresses = $bucket_mail->getApprovedEmailFromAddressesAndGroups($bucket_mail->approved_addresses, $bucket_mail->approved_groups, $this->created_id);

        // エラーチェック
        if (empty($approved_addresses)) {
            $this->saveAppLog($bucket_mail->plugin_name, "送信メールアドレスなし。bucket_name = {$this->bucket->bucket_name} buckets_id = {$this->bucket->id}");
            return;
        }

        // メール送信
        foreach ($approved_addresses as $approved_address) {
            Mail::to($approved_address)->send(new ApprovedNotice($this->notice_embedded_tags, $bucket_mail));

            $this->saveAppLog($bucket_mail->plugin_name, $approved_address);
        }
    }
}
