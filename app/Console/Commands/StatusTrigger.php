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
        })->where('executed', 0)->where('executed_at', '<=', date('Y-m-d H:i:s'));

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }

        if ($iterable->count() > 0) {
            foreach ($iterable->get() as $order) {

                $thisOrder = ChangeOrderStatusTrigger::findOrFail($order->id);
                $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);
                $newStatus = $thisOrder->status_id;

                $so = [
                    'id' => $salesOrder->id,
                    'status' => $salesOrder->status
                ];
    
                if (isset($thisOrder->order_id)) {
                    event(new \App\Events\OrderStatusEvent('order-status-change', [
                        'orderId' => $thisOrder->order_id,
                        'orderStatus' => $newStatus,
                        'orderOldStatus' => $salesOrder->status,
                        'windowId' => \Illuminate\Support\Str::random(30)
                    ]));
                }
    
                Helper::logger("Command: ORDER STATUS FROM  : " . $salesOrder->status . " TO NEW STATUS " . $newStatus . " CHANGED ");

                $thisOrder->status_id = $salesOrder->status;
                $thisOrder->executed = true;
                $thisOrder->save();
    
                $salesOrder->status = $so['status'];
                $salesOrder->save();
    
                // Helper::logger("Command: {$newStatus} " . $so['status'] . "\n");
                Helper::fireTriggers(['status_id' => $newStatus], [
                   'id' => $so['id'],
                   'status' => $so['status']
                ], '1', [1, 3]);
                Helper::fireTriggers(['status_id' => $newStatus], [
                    'id' => $so['id'],
                    'status' => $so['status']
                ], '2', [1, 3]);
            }
        }
    }
}
