<?php

namespace App\Console\Commands;

use App\Models\{AddTaskToOrderTrigger, SalesOrder, Trigger, ChangeOrderStatusTrigger};
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
        $iterable = AddTaskToOrderTrigger::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', 0)->whereNotNull('executed_at')->where('executed_at', '<=', date('Y-m-d H:i:s'));

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

                foreach (Trigger::where('status_id', $thisOrder->status_id)->where('type', '1')->orderBy('sequence', 'ASC')->get() as $t) {
                    AddTaskToOrderTrigger::create([
                        'order_id' => $salesOrder->id,
                        'status_id' => $thisOrder->status_id,
                        'added_by' => 1,
                        'time' => $t->time,
                        'type' => $t->time_type,
                        'main_type' => 2,
                        'description' => $t->task_description,
                        'current_status_id' => $salesOrder->status,
                        'trigger_id' => $t->id
                    ]);
                }
    
                foreach (Trigger::where('status_id', $thisOrder->status_id)->where('type', '2')->orderBy('sequence', 'ASC')->get() as $t) {
                    ChangeOrderStatusTrigger::create([
                        'order_id' => $salesOrder->id,
                        'status_id' => $t->next_status_id,
                        'added_by' => 1,
                        'time' => $t->time,
                        'type' => $t->time_type,
                        'current_status_id' => $thisOrder->status_id,
                        'executed_at' => date('Y-m-d H:i:s', strtotime($t->time)),
                        'trigger_id' => $t->id
                    ]);
                }
            }

            $thisOrder->executed = true;
            $thisOrder->save();
        }
    }
}
