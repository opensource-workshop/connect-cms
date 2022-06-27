<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;

use App\Traits\Migration\MigrationTrait;

class ExportNc2 extends Command
{

    use MigrationTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExportNc2 {target?} {second_param?} {third_param?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'NC2 をDBからConnect-CMS 移行形式のHTMLにエクスポートする';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 引数の解釈
        $target = $this->argument("target");
        $target_plugin = null;
        $redo = null;

        // target が plugins の場合のみ、2番目が target_plugin、3番目が redoになる。
        // その他の場合は、2番目が redoになる。
        if ($target == 'plugins') {
            $target_plugin = $this->argument("second_param");
            $redo = $this->argument("third_param") == 'redo' ? true : false;
        } else {
            $redo = $this->argument("second_param") == 'redo' ? true : false;
        }

        // NC2 からデータをエクスポート
        $this->exportNc2($target, $target_plugin, $redo);
    }
}
