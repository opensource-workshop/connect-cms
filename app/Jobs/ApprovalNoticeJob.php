<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\ApprovalNotice;
use App\Models\Common\BucketsMail;

class ApprovalNoticeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大試行回数
     * メールは大量送信時に途中で失敗した場合、同じメールが初めの方の人に再度送られるとまずいため、自動リトライしないため１回にする。
     *
     * @var int
     */
    public $tries = 1;

    private $frame = null;
    private $bucket = null;
    private $post = null;
    private $show_method = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($frame, $bucket, $post, $show_method)
    {
        // buckets などの受け取り
        $this->frame       = $frame;
        $this->bucket      = $bucket;
        $this->post        = $post;
        $this->show_method = $show_method;
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

        // エラーチェック（とりあえずデバックログに出力。管理画面で確認できるエラーテーブルに移すこと）
        if (!$bucket_mail->approval_addresses) {
            Log::debug("送信先メールアドレスの指定なし。buckets_id = " . $this->bucket->id);
        }

        // メール送信
        $approval_addresses = explode(',', $bucket_mail->approval_addresses);
        if (empty($approval_addresses)) {
            return;
        }
        foreach ($approval_addresses as $approval_address) {
            Mail::to($approval_address)->send(new ApprovalNotice($this->frame, $this->bucket, $this->post, $this->show_method, $bucket_mail));
        }
    }
}
