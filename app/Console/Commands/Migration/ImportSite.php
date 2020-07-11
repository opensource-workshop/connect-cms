<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;

use App\Traits\Migration\MigrationTrait;

class ImportSite extends Command
{

    use MigrationTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ImportSite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        // Connect-CMS 移行形式のHTML(Site全部) をインポートする
        $this->importSite();
    }
}
