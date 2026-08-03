<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')
        //          ->hourly();
        /**
         * 指定日時でバッチスケジュールを設定
         */
        if(env('OPTION_BATCH_SCHEDULE')){
            // カンマ連結されたバッチスケジュールセットを分割
            $defs = explode(',', env('OPTION_BATCH_SCHEDULE'));
            foreach($defs as $def){
                // パイプ連結されたコマンドと実施時刻を分割
                $option_schedule_sets = explode('|', $def);
                $cmd = 'command:' . $option_schedule_sets[0];
                $time = $option_schedule_sets[1];

                // バッチスケジュールを定義
                $schedule->command($cmd)->at($time);
            }
        }

        /**
         * 毎分実行のスケジュール設定
         */
        if(env('OPTION_BATCH_SCHEDULE_MINUTELY')){
            // カンマ連結されたバッチスケジュールセットを分割
            $defs = explode(',', env('OPTION_BATCH_SCHEDULE_MINUTELY'));
            foreach($defs as $def){
                // コマンドを取得
                $cmd = 'command:' . $def;

                // 毎分バッチスケジュールを定義
                $schedule->command($cmd)->everyMinute();
            }
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        $this->load(__DIR__.'/CommandsOption');

        require base_path('routes/console.php');
    }
}
