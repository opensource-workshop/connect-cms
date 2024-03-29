<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;

use App\Traits\Migration\MigrationExportNc3PageTrait;

class ExportNc3PageFromHttp extends Command
{
    use MigrationExportNc3PageTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExportNc3PageFromHttp {url} {page_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'NC3 の１つのウェブページからConnect-CMS 移行形式のHTMLにエクスポートする';

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
        // NC3 の１つのウェブページからデータをエクスポート
        $this->migrationNC3Page($this->argument("url"), $this->argument("page_id"));
    }
}
