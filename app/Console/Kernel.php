<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
         Commands\Migration\FromHttp::class,
         Commands\Migration\ExportNc2::class,
         Commands\Migration\ImportSite::class,
         Commands\Migration\ImportHtml::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
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
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        $this->load(__DIR__.'/CommandsOption');

        require base_path('routes/console.php');
    }
}
