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

    public function handle($triggers = [], $executor = null) {

        $iterable = AddTaskToOrderTrigger::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', 0)->where(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(executed_at, '%Y-%m-%d %H:%i')"), '<=', date('Y-m-d H:i:s')); 

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

                \App\Models\TriggerLog::create([
                    'trigger_id' => $order->trigger_id,
                    'cron_id' => $order->id,
                    'order_id' => $order->order_id,
                    'watcher_id' => $executor,
                    'next_status_id' => $order->status_id,
                    'current_status_id' => $order->current_status_id,
                    'description' => $order->description,
                    'type' => 1,
                    'time_type' => $order->time_type,
                    'main_type' => $order->main_type,
                    'hour' => $order->hour,
                    'minute' => $order->minute,
                    'time' => $order->time,
                    'executed_at' => $order->executed_at,
                    'executed' => 1
                ]);

                $thisOrder->executed = true;
                $thisOrder->save();

            }
        }
    }
}
