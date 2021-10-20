<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\RelateNotice;
use App\Models\Common\BucketsMail;

class RelateNoticeJob implements ShouldQueue
{
    /**
     * 1.キューに入っているジョブがコンストラクタで Eloquent モデルを受け入れる場合、SerializesModels トレイトにより、モデルの識別子だけがキューにシリアル化されます。
     * 2.ジョブが実際に処理されると、キューシステムはデータベースからモデルインスタンス全体を自動的に再取得します。
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
    private $notice_embedded_tags = null;
    // private $mail_users = null;
    private $relate_user_emails = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bucket, array $notice_embedded_tags, array $relate_user_emails)
    {
        // buckets などの受け取り
        $this->bucket               = $bucket;
        $this->notice_embedded_tags = $notice_embedded_tags;
        // $this->mail_users           = $mail_users;
        $this->relate_user_emails   = $relate_user_emails;
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

        // メール送信
        if (empty($this->relate_user_emails)) {
            return;
        }

        // bugfix: $this->mail_users は Eloquent モデルのため handle() で $this->mail_users を使用すると、キューシステムがモデルインスタンスを再取得したため、
        // 結合されたすべてのデータが消えてしまうため、代わりに配列変数 $this->relate_user_emails を使う。
        //
        // foreach ($this->mail_users as $relate_user) {
        //     if ($relate_user->email) {
        //         Mail::to($relate_user->email)->send(new RelateNotice($this->frame, $this->bucket, $this->post, $this->title, $this->show_method, $bucket_mail));
        //     }
        // }
        foreach ($this->relate_user_emails as $email) {
            Mail::to($email)->send(new RelateNotice($this->notice_embedded_tags, $bucket_mail));
        }
    }
}
