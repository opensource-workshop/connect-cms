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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $frame = null;
    private $bucket = null;
    private $post = null;
    private $show_method = null;
    private $mail_users = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($frame, $bucket, $post, $show_method, $mail_users)
    {
        // buckets などの受け取り
        $this->frame  = $frame;
        $this->bucket = $bucket;
        $this->post   = $post;
        $this->show_method = $show_method;
        $this->mail_users  = $mail_users;
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
        if (empty($this->mail_users)) {
            return;
        }
        foreach ($this->mail_users as $relate_user) {
            if ($relate_user->email) {
                Mail::to($relate_user->email)->send(new RelateNotice($this->frame, $this->bucket, $this->post, $this->show_method, $bucket_mail));
            }
        }
    }
}
