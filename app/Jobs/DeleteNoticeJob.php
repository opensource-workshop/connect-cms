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
    /**
     * 1.キューに入っているジョブがコンストラクタで Eloquent モデルを受け入れる場合、SerializesModels トレイトにより、モデルの識別子だけがキューにシリアル化されます。
     * 2.ジョブが実際に処理されると、キュー システムはデータベースからモデルインスタンス全体を自動的に再取得します。
     */
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 最大試行回数
     * メールは大量送信時に途中で失敗した場合、同じメールが初めの方の人に再度送られるとまずいため、自動リトライしないため１回にする。
     *
     * @var int
     */
    public $tries = 1;

    private $bucket = null;
    // change: $postは Eloquent モデルのため 物理削除時にキューで再取得できない。代わりに配列変数を使う。
    // private $post = null;
    private $notice_embedded_tags = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bucket, $notice_embedded_tags)
    {
        // buckets などの受け取り
        $this->bucket = $bucket;
        // $this->post   = $post;
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
            // Mail::to($notice_address)->send(new DeleteNotice($this->frame, $this->bucket, $this->post, $this->title, $this->show_method, $this->delete_comment, $bucket_mail));
            Mail::to($notice_address)->send(new DeleteNotice($this->notice_embedded_tags, $bucket_mail));
        }
    }
}
