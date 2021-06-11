<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

use App\Mail\ConnectMail;

use App\Models\Core\Configs;
use App\Models\Core\AppLog;

/**
 * メール送信の共通処理
 *
 * @author 永原　篤 <nagahara@opensource-workshop.jp>
 * @copyright OpenSource-WorkShop Co.,Ltd. All Rights Reserved
 * @category メール管理
 * @package CommonTrait
 */
trait ConnectMailTrait
{
    /**
     * メール送信
     *
     * @return boolean
     */
    public function sendMail($mail_address, $mail_options, $mail_datas, $plugin_name)
    {
        // 参考
        // https://qiita.com/avocadoneko/items/f318db01a6b41e249878
        // count(Mail::failures())

        // https://teratail.com/questions/29342
        // Mail::clearResolvedInstance('mailer');
        // App::forgetInstance('mailer');

        // メールを送る前に、ため込んでいるエラー件数を取得
        // メールを送った後にエラー件数を取得して、ため込んでいたエラー件数と異なれば、最後のエラーが今回のエラーであると判定
        // Laravel のMail ファサードが送信メソッド（to()）ではエラーを返さないための処理。
        // 最後にエラー配列からメールアドレスで送信エラーになったものを探す方法も考えたが、同一メールアドレスに複数のメールを
        // 送った場合で一つのみエラーが発生した際に判断できないため、この方法にした。

        // 2020-12-26 追記
        // host unknown でエラーになるようなケースでも、エラーになることが確認できなかった。
        // そのため、一旦エラーログは出力しない。
        // app_logs テーブルのreturn_value カラムは今後、使えるようになった場合のために残しておく。

        // --- ログ用事前処理 ---
        //$failure_before_count = count(Mail::failures());

        // --- メール送信 ---
        Mail::to(trim($mail_address))->send(new ConnectMail($mail_options, $mail_datas));
        //$failure_after_count = count(Mail::failures());

        // --- ログ処理 ---

        // スーパークラスの request があればそこから config を取得して条件を判断、ログ出力
        if (isset($this->request)) {
            $configs = $this->request->get('configs');

            // ログを出力するかどうかの判定（最初はfalse、条件に合致したらtrue にする）
            $log_record_flag = false;

            // value（検索キーワードなど）
            // $value = null;

            // 記録範囲
            if ($configs->where('name', 'app_log_scope')->where('value', 'all')->isNotEmpty()) {
                // 全て
                $log_record_flag = true;
            } elseif ($configs->where('name', 'save_log_type_sendmail')->where('value', '1')->isNotEmpty()) {
                // メール送信ログ出力
                $log_record_flag = true;
            }

            if ($log_record_flag) {
                // ルート名の取得
                $route_name = Route::current()->getName();

                // ログレコード
                $app_log = new AppLog();
                $app_log->ip_address   = $this->request->ip();
                $app_log->plugin_name  = $plugin_name;
                $app_log->uri          = $this->request->getRequestUri();
                $app_log->route_name   = $route_name;
                $app_log->method       = $this->request->method();
                $app_log->type         = 'SendMail';
                //$app_log->return_code  = $return_code;
                $app_log->value        = $mail_address;

                // ログイン後のみの項目
                if (Auth::check()) {
                    $app_log->created_id = Auth::user()->id;
                    $app_log->userid     = Auth::user()->userid;
                }
                $app_log->save();
            }
        }
        return;
    }
}
