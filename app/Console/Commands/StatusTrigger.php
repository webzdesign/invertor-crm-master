<?php

namespace App\Console\Commands;

use App\Models\{ChangeOrderStatusTrigger, AddTaskToOrderTrigger, SalesOrder, Trigger, ChangeOrderUser};
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

                AddTaskToOrderTrigger::where('status_id', $thisOrder->current_status_id)->where('executed_at', '>', date('Y-m-d H:i:s'))->where('executed', 0)->update(['executed_at' => null]);
                ChangeOrderUser::where('status_id', $thisOrder->current_status_id)->where('executed_at', '>', date('Y-m-d H:i:s'))->where('executed', 0)->update(['executed_at' => null]);
                ChangeOrderStatusTrigger::where('status_id', $thisOrder->current_status_id)->where('executed_at', '>', date('Y-m-d H:i:s'))->where('executed', 0)->update(['executed_at' => null]);

                if (AddTaskToOrderTrigger::where('current_status_id', $thisOrder->status_id)->whereNull('executed_at')->where('executed', 0)->exists()) {
                    foreach (AddTaskToOrderTrigger::where('current_status_id', $thisOrder->status_id)->whereNull('executed_at')->where('executed', 0)->get() as $iterator) {
                        AddTaskToOrderTrigger::where('id', $iterator->id)->update(['executed_at' => date('Y-m-d H:i:s', strtotime($iterator->time))]);
                    }
                }

                if (ChangeOrderUser::where('status_id', $thisOrder->status_id)->whereNull('executed_at')->where('executed', 0)->exists()) {
                    foreach (ChangeOrderUser::where('status_id', $thisOrder->status_id)->whereNull('executed_at')->where('executed', 0)->get() as $iterator) {
                        ChangeOrderUser::where('id', $iterator->id)->update(['executed_at' => date('Y-m-d H:i:s', strtotime($iterator->time))]);
                    }
                }

                if (ChangeOrderStatusTrigger::where('status_id', $thisOrder->status_id)->whereNull('executed_at')->where('executed', 0)->exists()) {
                    foreach (ChangeOrderStatusTrigger::where('status_id', $thisOrder->status_id)->whereNull('executed_at')->where('executed', 0)->get() as $iterator) {
                        ChangeOrderStatusTrigger::where('id', $iterator->id)->update(['executed_at' => date('Y-m-d H:i:s', strtotime($iterator->time))]);
                    }
                }

                event(new \App\Events\OrderStatusEvent('order-status-change', [
                    'orderId' => $thisOrder->order_id,
                    'orderStatus' => $newStatus,
                    'orderOldStatus' => $salesOrder->status,
                    'windowId' => \Illuminate\Support\Str::random(30)
                ]));

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
                            'main_type' => 2,
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
                            'main_type' => 3,
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
