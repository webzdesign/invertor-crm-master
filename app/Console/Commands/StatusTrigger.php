<?php

namespace App\Console\Commands;

use App\Models\ChangeOrderStatusTrigger;
use Illuminate\Console\Command;
use App\Models\CronHistory;
use App\Models\SalesOrder;

class StatusTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change status of orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (ChangeOrderStatusTrigger::where('executed', false)->where('executed_at', '<=', date('Y-m-d H:i:s'))->get() as $order) {

            $thisOrder = ChangeOrderStatusTrigger::findOrFail($order->id);
            SalesOrder::where('id', $thisOrder->order_id ?? null)->update([
                'status' => $thisOrder->status_id
            ]);
            $thisOrder->executed = true;
            $thisOrder->save();
            
        }
    }
}
