<?php

namespace App\Console\Commands;

use App\Models\{ChangeOrderUser, SalesOrder};
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
    public function handle($triggers = null)
    {
        $iterable = ChangeOrderUser::whereHas('trigger', function ($builder) {
            $builder->where('id', '>', 0);
        })->where('executed', 0)->where('executed_at', '<=', date('Y-m-d H:i:s')); 

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }


        foreach ($iterable->get() as $order) {
            $thisOrder = ChangeOrderUser::findOrFail($order->id);
            $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);

            if (isset($thisOrder->order_id)) {

                event(new \App\Events\OrderStatusEvent('change-user-for-order', [
                    'orderId' => $salesOrder->order_no,
                    'userId' => $thisOrder->user_id
                ]));

                $thisOrder->executed = true;
                $thisOrder->save();

                $respUsers = explode(',', $salesOrder->responsible_user);
                array_push($respUsers, $thisOrder->user_id);
                $respUsers = array_filter(array_unique($respUsers));

                $salesOrder->responsible_user = implode(',', $respUsers);
                $salesOrder->save();
            }
        }
    }
}
