<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Common\Page;

class RecalculatePageDepth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pages:recalc-depth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate depth column for all pages based on nested set positions.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Recalculating page depths...');

        $start = microtime(true);
        Page::recalcAllDepths();
        $elapsed = microtime(true) - $start;

        $this->info(sprintf('Done. (%.2f sec)', $elapsed));

        return 0;
    }
}
