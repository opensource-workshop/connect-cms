<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

use App\Traits\ConnectMailTrait;

/**
 * メール送信Job
 *
 * @author 牟田口 満 <mutaguchi@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category 共通
 * @package Job
 */
class ConnectMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConnectMailTrait;

    /**
     * 最大試行回数
     * メールは大量送信時に途中で失敗した場合、同じメールが初めの方の人に再度送られるとまずいため、自動リトライしないため１回にする。
     *
     * @var int
     */
    public $tries = 1;

    private $notice_addresses = null;
    private $mail_options = null;
    private $body = null;
    private $plugin_name = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $notice_addresses, array $mail_options, string $body, string $plugin_name)
    {
        $this->notice_addresses = $notice_addresses;
        $this->mail_options = $mail_options;
        $this->body = $body;
        $this->plugin_name = $plugin_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // メール送信
        foreach ($this->notice_addresses as $notice_address) {
            $this->sendMail($notice_address, $this->mail_options, ['content' => $this->body], $this->plugin_name);
        }
    }
}
