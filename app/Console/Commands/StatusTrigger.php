<?php

namespace App\Console\Commands;

use App\Models\ChangeOrderStatusTrigger;
use Illuminate\Console\Command;
use App\Models\SalesOrder;
use App\Helpers\Helper;

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
    public function handle($triggers = null)
    {
        $iterable = ChangeOrderStatusTrigger::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', false)->where('executed_at', '<=', date('Y-m-d H:i:s'));

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }

        if ($iterable->count() > 0) {
            foreach ($iterable->get() as $order) {

                $thisOrder = ChangeOrderStatusTrigger::findOrFail($order->id);
                $so = $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);
                $tempStatus = $thisOrder->status_id;
    
                if (isset($thisOrder->order_id)) {
                    event(new \App\Events\OrderStatusEvent('order-status-change', [
                        'orderId' => $thisOrder->order_id,
                        'orderStatus' => $thisOrder->status_id,
                        'orderOldStatus' => $salesOrder->status,
                        'windowId' => \Illuminate\Support\Str::random(30)
                    ]));
                }
    
                $thisOrder->current_status_id = $salesOrder->status;
                $thisOrder->executed = true;
                $thisOrder->save();
    
                $salesOrder->status = $tempStatus;
                $salesOrder->save();
    
    
                Helper::fireTriggers(['status_id' => $tempStatus], [
                   'id' => $so->id,
                   'status' => $so->status
                ], '1', [1, 3]);
                Helper::fireTriggers(['status_id' => $tempStatus], [
                    'id' => $so->id,
                    'status' => $so->status
                ], '2', [1, 3]);
            }
        }
    }
}
