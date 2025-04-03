<?php

namespace App\Jobs;

use App\Mail\DeleteNotice;
use App\Models\Common\BucketsMail;
use App\Traits\ConnectMailTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class DeleteNoticeJob implements ShouldQueue
{
    /**
     * 1.キューに入っているジョブがコンストラクタで Eloquent モデルを受け入れる場合、SerializesModels トレイトにより、モデルの識別子だけがキューにシリアル化されます。
     * 2.ジョブが実際に処理されると、キュー システムはデータベースからモデルインスタンス全体を自動的に再取得します。
     */
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConnectMailTrait;

    /**
     * 最大試行回数
     * メールは大量送信時に途中で失敗した場合、同じメールが初めの方の人に再度送られるとまずいため、自動リトライしないため１回にする。
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @var \App\Models\Common\Buckets
     */
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

        // 送信者メールとグループから、通知するメールアドレス取得
        $notice_addresses = $bucket_mail->getEmailFromAddressesAndGroups($bucket_mail->notice_addresses, $bucket_mail->notice_groups, $bucket_mail->notice_everyone);

        // エラーチェック
        if (empty($notice_addresses)) {
            $this->saveAppLog($bucket_mail->plugin_name, "送信メールアドレスなし。bucket_name = {$this->bucket->bucket_name} buckets_id = {$this->bucket->id}");
            return;
        }

        // *** メール送信
        foreach ($notice_addresses as $notice_address) {
            Mail::to($notice_address)->send(new DeleteNotice($this->notice_embedded_tags, $bucket_mail));

            $this->saveAppLog($bucket_mail->plugin_name, $notice_address);
        }
    }
}
