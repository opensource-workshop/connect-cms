<?php

namespace App\Jobs;

use App\Mail\ApprovalNotice;
use App\Models\Common\Buckets;
use App\Models\Common\BucketsMail;
use App\Traits\ConnectMailTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApprovalNoticeJob implements ShouldQueue
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
        $approval_addresses = $bucket_mail->getEmailFromAddressesAndGroups($bucket_mail->approval_addresses, $bucket_mail->approval_groups);

        // エラーチェック（とりあえずデバックログに出力。管理画面で確認できるエラーテーブルに移すこと）
        if (empty($approval_addresses)) {
            Log::debug("送信先メールアドレスの指定なし。buckets_id = " . $this->bucket->id);
            return;
        }

        // メール送信
        foreach ($approval_addresses as $approval_address) {
            Mail::to($approval_address)->send(new ApprovalNotice($this->notice_embedded_tags, $bucket_mail));

            $bucket = Buckets::findOrNew($bucket_mail->buckets_id);
            $this->saveAppLog($bucket->plugin_name, $approval_address);
        }
    }
}
