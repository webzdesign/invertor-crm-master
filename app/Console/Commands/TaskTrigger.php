<?php

namespace App\Console\Commands;

use App\Models\{AddTaskToOrderTrigger, SalesOrder};
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

    /**
     * Execute the console command.
     */
    public function handle($order = null)
    {
        $iterable = AddTaskToOrderTrigger::where('executed', 0)->whereNotNull('executed_at');

        if (!is_null($order)) {
            $iterable = $iterable->where('order_id', $order);
        }

        foreach ($iterable->get() as $order) {

            $thisOrder = AddTaskToOrderTrigger::findOrFail($order->id);
            $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);

            if (isset($thisOrder->order_id)) {
                event(new \App\Events\OrderStatusEvent('add-task-to-order', [
                    'orderId' => $salesOrder->order_no
                ]));
            }

            $thisOrder->executed = true;
            $thisOrder->save();
        }
    }
}
