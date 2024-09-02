<?php

namespace App\Console\Commands;

use App\Models\{ChangeOrderStatusTrigger, AddTaskToOrderTrigger, SalesOrder, Trigger, ChangeOrderUser, SalesOrderStatus, Deliver, User, Notification};
use Illuminate\Support\Facades\DB;
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
        })->where('executed', 0)->where(DB::raw("DATE_FORMAT(executed_at, '%Y-%m-%d %H:%i')"), '<=', date('Y-m-d H:i'));

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }

        foreach ($iterable->get() as $order) {

            $thisOrder = ChangeOrderStatusTrigger::where('id', $order->id)->first();

            if (isset($thisOrder->order_id)) {

            $salesOrder = SalesOrder::findOrFail($thisOrder->order_id ?? null);
            $windowId = \Illuminate\Support\Str::random(30);
            $newStatus = $thisOrder->status_id;

            AddTaskToOrderTrigger::where('order_id', $thisOrder->order_id)->where('status_id', '!=',$newStatus)->where('executed', 0)->delete();
            ChangeOrderUser::where('order_id', $thisOrder->order_id)->where('status_id', '!=', $newStatus)->where('executed', 0)->delete();
            ChangeOrderStatusTrigger::where('order_id', $thisOrder->order_id)->where('status_id', '!=', $newStatus)->where('executed', 0)->delete();

            event(new \App\Events\OrderStatusEvent('order-status-change', [
                'orderId' => $thisOrder->order_id,
                'orderStatus' => $newStatus,
                'orderOldStatus' => $salesOrder->status,
                'windowId' => $windowId,
                'users' => [Deliver::where('so_id', $salesOrder->id)->where('status', 1)->first()->user_id ?? null, $salesOrder->added_by]
            ]));

            $fromStatus = SalesOrderStatus::withTrashed()->where('id', $salesOrder->status)->first();
            $toStatus = SalesOrderStatus::withTrashed()->where('id', $newStatus)->first();

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

            $notificationdriverid = $salesOrder->assigneddriver->user_id ?? null;
            if($notificationdriverid !="") {
                $driverphonenumber = [];
                $notificationdrivers = User::find($notificationdriverid);
                if(!empty($notificationdrivers)) {
                    $driverphonenumber[$notificationdrivers->id] = $notificationdrivers->country_dial_code.$notificationdrivers->phone;
                }
                if(!empty($driverphonenumber)) {
                    Helper::sendTwilioMsg($driverphonenumber,$newStatus,1,$salesOrder->id);
                }
            }
            $notificationsellerid = ($salesOrder->seller_id !=null) ? $salesOrder->seller_id : null;
            if($notificationsellerid !="") {
                $sellerphonenumber = [];
                $notificationsellers = User::find($notificationsellerid);
                if(!empty($notificationsellers)) {
                    $sellerphonenumber[$notificationsellers->id] = $notificationsellers->country_dial_code.$notificationsellers->phone;
                }
                if(!empty($sellerphonenumber)) {
                    Helper::sendTwilioMsg($sellerphonenumber,$newStatus,2,$salesOrder->id);
                }
            }
                //scammer status
                if ($newStatus == 7) {
                    \App\Models\ScammerContact::updateOrCreate([
                        'so_id' => $salesOrder->id,
                        'dial_code' => str_replace(' ', '', $salesOrder->country_dial_code),
                        'phone_number' => str_replace(' ', '', $salesOrder->customer_phone)
                    ]);

                    Notification::create([
                        'user_id' => $salesOrder->added_by,
                        'so_id' => $salesOrder->id,
                        'title' => 'Scammer Order',
                        'description' => 'Order <strong>' . $salesOrder->order_no . '</strong> does contains same phone number as other active order.',
                        'link' => 'sales-orders'
                    ]);

                    event(new \App\Events\OrderStatusEvent('order-allocation-info', ['user' => $salesOrder->added_by, 'content' => 'Order <strong>' . $salesOrder->order_no . '</strong> does contains same phone number as other active order.', 'link' => url('sales-orders')]));
                }

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

                if ($newStatus == 1) {
                    self::allocateDrivers($order->order_id, $fromStatus->id, $windowId);
                }

            }
        }
    }

    private static function allocateDrivers($orderId, $oldStatus, $windowId) {
        $salesorderInfo = SalesOrder::find($orderId);

        if(!empty($salesorderInfo)) {

            $salesorderInfo->status = 1;
            $salesorderInfo->responsible_user = null;
            $salesorderInfo->save();

            Deliver::where('so_id',$salesorderInfo->id)->delete();

            event(new \App\Events\OrderStatusEvent('order-status-change', [
                'orderId' => $salesorderInfo->id,
                'orderStatus' => 1,
                'orderOldStatus' => $oldStatus,
                'windowId' => $windowId,
                'users' => [Deliver::where('so_id', $salesorderInfo->id)->where('status', 1)->first()->user_id ?? null, $salesorderInfo->added_by],
                'removing' => true
            ]));

            $driverlist = \App\Http\Controllers\SalesOrderController::againDriverAllocate($salesorderInfo)->getData();

            if($driverlist->status === true && !empty($driverlist->drivers)) {

                $driverids = [];
                $driverphonenumber = [];
                foreach($driverlist->drivers as $driverid=>$driverallocaterang) {

                    $driverDetail = User::active()->find($driverid);

                    if(!empty($driverDetail)) {
                        $driverids[] = $driverid;
                        Deliver::create([
                            'user_id' => $driverid,
                            'so_id' => $salesorderInfo->id,
                            'added_by' => 1,
                            'driver_lat' => $driverDetail->lat,
                            'driver_long' => $driverDetail->long,
                            'delivery_location_lat' => $salesorderInfo->lat,
                            'delivery_location_long' => $salesorderInfo->long,
                            'range' => (isset($driverallocaterang) && $driverallocaterang != '') ? number_format($driverallocaterang,2,'.','') : 0
                        ]);

                        Notification::create([
                            'user_id' => $driverid,
                            'so_id' => $salesorderInfo->id,
                            'title' => 'New Order',
                            'description' => 'Order <strong>' . $salesorderInfo->order_no . '</strong> is allocated to you please check the order.',
                            'link' => 'sales-orders'
                        ]);

                        $driverphonenumber[$driverDetail->id] = $driverDetail->country_dial_code.$driverDetail->phone;

                        event(new \App\Events\OrderStatusEvent('order-allocation-info', ['driver' => $driverid, 'content' => "Order {$salesorderInfo->order_no} is allocated to you please check the order.", 'link' => url('sales-orders')]));
                    }

                }
                if(!empty($driverphonenumber)) {
                    Helper::sendTwilioMsg($driverphonenumber,1,1,$orderId);
                }
                if(!empty($driverids)) {
                    \App\Models\TriggerLog::create([
                        'trigger_id' => 0,
                        'order_id' => $salesorderInfo->id,
                        'type' => 4,
                        'allocated_driver_id' => implode(',',$driverids),
                    ]);
                } else {

                    Notification::create([
                        'user_id' => $salesorderInfo->added_by,
                        'so_id' => $salesorderInfo->id,
                        'title' => 'No drivers found',
                        'description' => 'We cannot accept your order <strong>' . $salesorderInfo->order_no . '</strong> because order location does not falls inside driver\'s delivery zone.',
                        'link' => 'sales-orders'
                    ]);

                    event(new \App\Events\OrderStatusEvent('order-allocation-info', ['user' => $salesorderInfo->added_by, 'content' => 'We cannot accept your order <strong>' . $salesorderInfo->order_no . '</strong> because order location does not falls inside driver\'s delivery zone.', 'link' => url('sales-orders')]));
                }
            }

        }
    }
}
