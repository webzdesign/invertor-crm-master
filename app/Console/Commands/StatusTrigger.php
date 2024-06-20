<?php

namespace App\Console\Commands;

use App\Models\{ChangeOrderStatusTrigger, AddTaskToOrderTrigger, SalesOrder, Trigger, ChangeOrderUser, SalesOrderStatus, Deliver};
use Illuminate\Console\Command;
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

    public function handle($triggers = [], $executor = null) {


        
        $iterable = ChangeOrderStatusTrigger::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', 0)->where(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(executed_at, '%Y-%m-%d %H:%i')"), '<=', date('Y-m-d H:i')); 

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }

        foreach ($iterable->get() as $order) {

            $thisOrder = ChangeOrderStatusTrigger::where('id', $order->id)->first();

            if (isset($thisOrder->order_id)) {

            $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);
            $newStatus = $thisOrder->status_id;

                AddTaskToOrderTrigger::where('order_id', $thisOrder->order_id)->where('status_id', '!=',$newStatus)->where('executed', 0)->delete();
                ChangeOrderUser::where('order_id', $thisOrder->order_id)->where('status_id', '!=', $newStatus)->where('executed', 0)->delete();
                ChangeOrderStatusTrigger::where('order_id', $thisOrder->order_id)->where('status_id', '!=', $newStatus)->where('executed', 0)->delete();

                event(new \App\Events\OrderStatusEvent('order-status-change', [
                    'orderId' => $thisOrder->order_id,
                    'orderStatus' => $newStatus,
                    'orderOldStatus' => $salesOrder->status,
                    'windowId' => \Illuminate\Support\Str::random(30),
                    'users' => [Deliver::where('so_id', $salesOrder->id)->where('status', 1)->first()->user_id ?? null, $salesOrder->added_by]
                ]));

                $fromStatus = SalesOrderStatus::custom()->withTrashed()->where('id', $salesOrder->status)->first();
                $toStatus = SalesOrderStatus::custom()->withTrashed()->where('id', $newStatus)->first();

                \App\Models\TriggerLog::create([
                    'trigger_id' => $order->trigger_id,
                    'cron_id' => $order->id,
                    'order_id' => $order->order_id,
                    'watcher_id' => $executor,
                    'next_status_id' => $newStatus,
                    'current_status_id' => $salesOrder->status,
                    'type' => 2,
                    'time_type' => $order->time_type,
                    'main_type' => $order->main_type,
                    'hour' => $order->hour,
                    'minute' => $order->minute,
                    'time' => $order->time,
                    'executed_at' => $order->executed_at,
                    'executed' => 1,
                    'from_status' => [
                       'name' => $fromStatus->name ?? '-',
                       'color' => $fromStatus->color ?? ''
                    ],
                    'to_status' => [
                        'name' => $toStatus->name ?? '-',
                        'color' => $toStatus->color ?? ''
                     ]
                ]);

                $thisOrder->executed = true;
                $thisOrder->save();
                
                $salesOrder->status = $newStatus;
                $salesOrder->save();

            /** TASKS **/
            $currentTime1 = date('Y-m-d H:i:s');
            $y = [];

            try {

                $triggers = Trigger::where('type', 1)->where('status_id', $newStatus)->whereIn('action_type', [1, 3]);
                if ($triggers->count() > 0) {

                    foreach ($triggers->get() as $t) {

                        $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                        
                        $record = AddTaskToOrderTrigger::create([
                            'order_id' => $thisOrder->order_id,
                            'status_id' => $newStatus,
                            'added_by' => 1,
                            'time' => $t->time,
                            'type' => $t->time_type,
                            'main_type' => $t->action_type,
                            'description' => $t->task_description,
                            'current_status_id' => $newStatus,
                            'executed_at' => $currentTime1,
                            'trigger_id' => $t->id
                        ]);

                        if ($t->time_type == 1) {
                            $y[] = $record->id;
                        }
                    }
                }

                if (!empty($y)) {
                    (new \App\Console\Commands\TaskTrigger)->handle($y);
                }

            } catch (\Exception $e) {
                Helper::logger("TASK REC. ERROR :" . $e->getMessage());
            }
            /** TASKS **/


            /** Change User **/
            $currentTime1 = date('Y-m-d H:i:s');
            $y = [];

            try {

                $triggers = Trigger::where('type', 3)->where('status_id', $newStatus)->whereIn('action_type', [1, 3]);
                if ($triggers->count() > 0) {

                    foreach ($triggers->get() as $t) {

                        $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                        
                        $record = ChangeOrderUser::create([
                            'order_id' => $thisOrder->order_id,
                            'status_id' => $newStatus,
                            'added_by' => 1,
                            'time' => $t->time,
                            'type' => $t->time_type,
                            'main_type' => $t->action_type,
                            'user_id' => $t->user_id,
                            'current_status_id' => $newStatus,
                            'executed_at' => $currentTime1,
                            'trigger_id' => $t->id
                        ]);

                        if ($t->time_type == 1) {
                            $y[] = $record->id;
                        }
                    }
                }

                if (!empty($y)) {
                    (new \App\Console\Commands\ChangeUserForOrderTrigger)->handle($y);
                }

            } catch (\Exception $e) {
                Helper::logger("CHANGE USER REC. ERROR :" . $e->getMessage());
            }
            /** Change User **/


            /** Change Order Status **/
            $currentTime1 = date('Y-m-d H:i:s');
            $y = [];

            try {
                $newTrigger = Trigger::where('status_id', $newStatus)->where('type', 2)
                ->whereIn('action_type', [1, 3])
                ->get();
    
                foreach ($newTrigger as $t) {
    
                    $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
    
                    $record = ChangeOrderStatusTrigger::create([
                        'order_id' => $thisOrder->order_id,
                        'status_id' => $t->next_status_id,
                        'added_by' => 1,
                        'time' => $t->time,
                        'main_type' => $t->action_type,
                        'type' => $t->time_type,
                        'current_status_id' => $t->status_id,
                        'executed_at' => date('Y-m-d H:i:s', strtotime($t->time)),
                        'trigger_id' => $t->id
                    ]);
    
                    if ($t->time_type == 1) {
                        $y[] = $record->id;
                    }
                }
    
                if (!empty($y)) {
                    $this->handle($y);
                }
            } catch (\Exception $e) {
                Helper::logger("CHANGE STATUS ORDER REC. ERROR :" . $e->getMessage());
            }
            /** Change Order Status **/

            }
        }
    }
}
