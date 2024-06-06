<?php

namespace App\Console\Commands;

use App\Models\{ChangeOrderStatusTrigger, SalesOrder};
use Illuminate\Console\Command;

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

    public function handle($triggers = []) {

        $iterable = ChangeOrderStatusTrigger::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', 0)->where('executed_at', '<=', date('Y-m-d H:i:s')); 

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }


        foreach ($iterable->get() as $order) {

            $thisOrder = ChangeOrderStatusTrigger::findOrFail($order->id);
            $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);
            $newStatus = $thisOrder->status_id;

            if (isset($thisOrder->order_id)) {

            event(new \App\Events\OrderStatusEvent('order-status-change', [
                'orderId' => $thisOrder->order_id,
                'orderStatus' => $newStatus,
                'orderOldStatus' => $salesOrder->status,
                'windowId' => \Illuminate\Support\Str::random(30)
            ]));

                $thisOrder->status_id = $salesOrder->status;
                $thisOrder->executed = true;
                $thisOrder->save();

                $salesOrder->status = $newStatus;
                $salesOrder->save();
            }
        }
    }
}
