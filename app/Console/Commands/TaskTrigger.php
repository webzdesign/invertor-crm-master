<?php

namespace App\Console\Commands;

use App\Models\{AddTaskToOrderTrigger, SalesOrder, Trigger};
use App\Models\{ChangeOrderStatusTrigger};
use Illuminate\Console\Command;

class TaskTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When order is moved then add task';

    public function handle($triggers = null) {

        $iterable = AddTaskToOrderTrigger::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', 0)->where('executed_at', '<=', date('Y-m-d H:i:s')); 

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }


        foreach ($iterable->get() as $order) {
            $thisOrder = AddTaskToOrderTrigger::findOrFail($order->id);
            $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);

            if (isset($thisOrder->order_id)) {

                event(new \App\Events\OrderStatusEvent('add-task-to-order', [
                    'orderId' => $salesOrder->order_no
                ]));

                $thisOrder->executed = true;
                $thisOrder->save();

            }
        }
    }
}
