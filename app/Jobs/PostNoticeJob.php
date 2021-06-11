<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\PostNotice;
use App\Models\Common\BucketsMail;

class PostNoticeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $frame = null;
    private $bucket = null;
    private $post = null;
    private $show_method = null;
    private $notice_method = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($frame, $bucket, $post, $show_method, $notice_method)
    {
        // buckets などの受け取り
        $this->frame  = $frame;
        $this->bucket = $bucket;
        $this->post   = $post;
        $this->show_method   = $show_method;
        $this->notice_method = $notice_method;
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
        if (!$bucket_mail->notice_addresses) {
            Log::debug("送信先メールアドレスの指定なし。buckets_id = " . $this->bucket->id);
        }

        // メール送信
        $notice_addresses = explode(',', $bucket_mail->notice_addresses);
        if (empty($notice_addresses)) {
            return;
        }
        foreach ($notice_addresses as $notice_address) {
            Mail::to($notice_address)->send(new PostNotice($this->frame, $this->bucket, $this->post, $this->show_method, $this->notice_method, $bucket_mail));
        }
    }
}
