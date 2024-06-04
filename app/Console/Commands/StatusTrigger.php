<?php

namespace App\Console\Commands;

use App\Models\ChangeOrderStatusTrigger;
use App\Models\AddTaskToOrderTrigger;
use Illuminate\Console\Command;
use App\Models\SalesOrder;
use App\Models\Trigger;

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
        foreach (ChangeOrderStatusTrigger::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', false)->where('executed_at', '<=', date('Y-m-d H:i:s'))->get() as $order) {

            $thisOrder = ChangeOrderStatusTrigger::findOrFail($order->id);
            $so = $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);

            if (isset($thisOrder->order_id)) {
                event(new \App\Events\OrderStatusEvent('order-status-change', [
                    'orderId' => $thisOrder->order_id,
                    'orderStatus' => $thisOrder->status_id,
                    'orderOldStatus' => $salesOrder->status,
                    'windowId' => \Illuminate\Support\Str::random(30)
                ]));
            }

            $salesOrder->status = $thisOrder->status_id;
            $salesOrder->save();
            $thisOrder->executed = true;
            $thisOrder->save();

            foreach (Trigger::where('status_id', $thisOrder->status_id)->where('type', '1')->orderBy('sequence', 'ASC')->get() as $t) {
                AddTaskToOrderTrigger::create([
                    'order_id' => $so->id,
                    'status_id' => $thisOrder->status_id,
                    'added_by' => 1,
                    'time' => $t->time,
                    'type' => $t->time_type,
                    'main_type' => 2,
                    'description' => $t->task_description,
                    'current_status_id' => $so->status,
                    'trigger_id' => $t->id
                ]);
            }

            foreach (Trigger::where('status_id', $thisOrder->status_id)->where('type', '2')->orderBy('sequence', 'ASC')->get() as $t) {
                ChangeOrderStatusTrigger::create([
                    'order_id' => $so->id,
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
    }
}
