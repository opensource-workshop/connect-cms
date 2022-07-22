<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;

class Version extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Output installed version of Connect-CMS.';

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
        $this->line(config('version.cc_version'));
    }
}
