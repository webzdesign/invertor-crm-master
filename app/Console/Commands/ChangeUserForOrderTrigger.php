<?php

namespace App\Console\Commands;

use App\Models\{ChangeOrderUser, SalesOrder, Deliver};
use Illuminate\Console\Command;

class ChangeUserForOrderTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'change_user:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When order is moved then change responsible user.';

    /**
     * Execute the console command.
     */
    public function handle($triggers = [], $executor = null)
    {
        $iterable = ChangeOrderUser::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', 0)->where(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(executed_at, '%Y-%m-%d %H:%i')"), '<=', date('Y-m-d H:i:s')); 

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }


        foreach ($iterable->get() as $order) {
            $thisOrder = ChangeOrderUser::findOrFail($order->id);
            $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);

            if (isset($thisOrder->order_id)) {

                $respUser = 1;

                if ($thisOrder->user_id == 1) {
                    $respUser = Deliver::where('so_id', $salesOrder->id)->whereIn('status', [0, 1])->first()->user_id ?? null;
                } else if ($thisOrder->user_id == 2) {
                    $respUser = $salesOrder->added_by;
                }

                if (!is_numeric($respUser)) {
                    $respUser = 1;
                }

                event(new \App\Events\OrderStatusEvent('change-user-for-order', [
                    'orderId' => $salesOrder->order_no,
                    'userId' => $respUser
                ]));

                \App\Models\TriggerLog::create([
                    'trigger_id' => $order->trigger_id,
                    'cron_id' => $order->id,
                    'order_id' => $order->order_id,
                    'watcher_id' => $executor,
                    'next_status_id' => $order->status_id,
                    'current_status_id' => $order->current_status_id,
                    'user_id' => $respUser,
                    'type' => 3,
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

                $salesOrder->responsible_user = $respUser;
                $salesOrder->save();
            }
        }
    }
}
