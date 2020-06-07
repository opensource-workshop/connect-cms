<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;

use App\Traits\Migration\MigrationTrait;

class FromNc2 extends Command
{

    use MigrationTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:MigrationFromNc2';

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
        // NC2 をデータベースから移行する
        $this->migrationNC2();
    }
}
