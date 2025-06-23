<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\MoldcellController;

class GetMoldcellEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moldcell:get-employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get moldcell employee';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        MoldcellController::getMoldcellEmployee();
    }
}
