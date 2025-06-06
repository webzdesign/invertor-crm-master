<?php

namespace App\Console\Commands;

use App\Http\Controllers\MoldcellController;
use Illuminate\Console\Command;

class MoldcellGetCallHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moldcell:get-call-history {period=today} {type=in}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get call history from moldcell';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->argument("period");
        $type = $this->argument("type");

        MoldcellController::getMoldcellCallHistory(1, $period, $type);
    }
}
