<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\DeleteNotice;
use App\Models\Common\BucketsMail;

class DeleteNoticeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $frame = null;
    private $bucket = null;
    private $id = null;
    private $show_method = null;
    private $delete_comment = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($frame, $bucket, $id, $show_method, $delete_comment)
    {
        // buckets などの受け取り
        $this->frame  = $frame;
        $this->bucket = $bucket;
        $this->id     = $id;
        $this->show_method    = $show_method;
        $this->delete_comment = $delete_comment;
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
            Mail::to($notice_address)->send(new DeleteNotice($this->frame, $this->bucket, $this->id, $this->show_method, $this->delete_comment, $bucket_mail));
        }
    }
}
