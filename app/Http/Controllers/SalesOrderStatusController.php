<?php

namespace App\Http\Controllers;

use App\Models\{ChangeOrderStatusTrigger, AddTaskToOrderTrigger, ChangeOrderUser, Setting, Trigger};
use App\Models\{SalesOrderStatus, SalesOrder, Deliver, Role, ManageStatus, User};
use App\Helpers\{Helper, Distance};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SalesOrderStatusController extends Controller
{
    public function index() {

        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::sequence()->custom()->orderBy('sequence', 'ASC')->get();
        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];
        $orders = [];

        foreach ($statuses as $status) {
            $tempOrder = SalesOrder::join('sales_order_items', 'sales_order_items.so_id', '=', 'sales_orders.id')->selectRaw("sales_orders.id, sales_orders.order_no, sales_orders.date, SUM(sales_order_items.amount) as amount, sales_orders.status as status")->where('sales_orders.status', $status->id)->groupBy('sales_order_items.so_id');

            if ($tempOrder->exists()) {
                $toBeShown = [];
                $orderIds = $tempOrder->pluck('id')->toArray();

                foreach ($orderIds as $soid) {
                    if (Deliver::where('so_id', $soid)->where('status', 0)->where('user_id', auth()->user()->id)->exists()) {
                        $toBeShown[] = $soid;
                    } else if (SalesOrder::where('id', $soid)->where(function($builder) {
                        $builder->where('added_by', auth()->user()->id)->orWhere('responsible_user', auth()->user()->id);
                    })->exists()) {
                        $toBeShown[] = $soid;
                    } else if (in_array(1, User::getUserRoles())) {
                        $toBeShown[] = $soid;
                    }
                }

                $orders[$status->id] = SalesOrder::join('sales_order_items', 'sales_order_items.so_id', '=', 'sales_orders.id')
                ->selectRaw("sales_orders.id, sales_orders.order_no, sales_orders.date, SUM(sales_order_items.amount) as amount, sales_orders.status as status")
                ->where('sales_orders.status', $status->id)
                ->whereIn('sales_orders.id', $toBeShown)
                ->groupBy('sales_order_items.so_id')->get()->toArray();
            }
        }

        return view('sales-orders-status.index', compact('moduleName', 'statuses', 'colours', 'orders'));
    }

    public function edit() {
        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::sequence()->custom()->orderBy('sequence', 'ASC')->get();
        $s = SalesOrderStatus::sequence()->custom()->select('id', 'name')->orderBy('sequence', 'ASC')->pluck('name', 'id')->toArray();
        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];
        $roles = Role::active()->select('id', 'name')->whereIn('id', [1, 2, 3])->pluck('name', 'id')->toArray();
        $maxTriggers = Setting::first()->triggers_per_status ?? 10;

        return view('sales-orders-status.edit', compact('moduleName', 'statuses', 'colours', 'roles', 's', 'maxTriggers'));
    }

    public function update(Request $request) {
        $this->validate($request, [
            'name.*' => 'required|distinct'
        ], [
            'name.*.required' => 'Enter status name before you save.',
            'name.*.distinct' => 'Status name must be unique.'
        ]);

        $tasks = $request->task;
        $changeStatus = $request->statuschange;
        $changeUsers = $request->userchange;

        $sequences = $request->sequence;
        $names = $request->name;
        $colors = $request->color;

        if (count($sequences) != count($names)) {
            return redirect()->route('sales-order-status-edit')->with('error', 'Add atleast a card to save.');
        }

        $userId = auth()->user()->id;
        $allStatusList = SalesOrderStatus::custom()->select('id', 'name')->pluck('name', 'id')->toArray();
        $toNotBeDeleted = [];

        DB::beginTransaction();

        try {
            if (count($sequences) > 0) {

                foreach ($sequences as $key => $value) {

                    if (!is_null($value)) {
                        SalesOrderStatus::custom()->where('id', $value)->update([
                            'name' => strtoupper($names[$key]),
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                    } else {
                        SalesOrderStatus::custom()->create([
                            'name' => strtoupper($names[$key]),
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                    }
                }

                if (is_array($tasks) && count($tasks) > 0) {
                    foreach ($tasks as $thisStatus => $array) {
                        if (isset($allStatusList[$thisStatus])) {
                            foreach ($array as $k => $v) {
                                if (isset($v['edit_id']) && $v['edit_id'] > 0) {
                                    Trigger::where('id', $v['edit_id'])->update([
                                        'status_id' => $v['status'],
                                        'hour' => $v['hour'],
                                        'minute' => $v['minute'],
                                        'sequence' => $k,
                                        'type' => 1,
                                        'time' => self::getStringToTime($v['timetype'], $v['hour'], $v['minute']),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'user_id' => null,
                                        'task_description' => $v['desc'],
                                        'updated_by' => $userId
                                    ]);
                                    $toNotBeDeleted[] = $v['edit_id'];
                                } else {
                                    $toNotBeDeleted[] = Trigger::create([
                                        'status_id' => $v['status'],
                                        'sequence' => $k,
                                        'hour' => $v['hour'] ?? null,
                                        'minute' => $v['minute'] ?? null,
                                        'type' => 1,
                                        'time' => self::getStringToTime($v['timetype'], ($v['hour'] ?? null), ($v['minute'] ?? null)),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'task_description' => $v['desc'] ?? null,
                                        'added_by' => $userId
                                    ])->id;
                                }
                            }
                        }
                    }
                }
    
                if (is_array($changeStatus) && count($changeStatus) > 0) {
                    foreach ($changeStatus as $thisStatus => $array) {
                        if (isset($allStatusList[$thisStatus])) {
                            foreach ($array as $k => $v) {
                                if (isset($v['edit_id']) && $v['edit_id'] > 0) {
                                    Trigger::where('id', $v['edit_id'])->update([
                                        'status_id' => $v['status'],
                                        'next_status_id' => $v['nextstatus'],
                                        'hour' => $v['hour'],
                                        'minute' => $v['minute'],
                                        'sequence' => $k,
                                        'type' => 2,
                                        'time' => self::getStringToTime($v['timetype'], $v['hour'], $v['minute']),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'user_id' => null,
                                        'task_description' => null,
                                        'updated_by' => $userId
                                    ]);
                                    $toNotBeDeleted[] = $v['edit_id'];
                                } else {
                                    $toNotBeDeleted[] = Trigger::create([
                                        'status_id' => $v['status'],
                                        'next_status_id' => $v['nextstatus'],
                                        'sequence' => $k,
                                        'hour' => $v['hour'] ?? null,
                                        'minute' => $v['minute'] ?? null,
                                        'type' => 2,
                                        'time' => self::getStringToTime($v['timetype'], ($v['hour'] ?? null), ($v['minute'] ?? null)),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'task_description' => $v['desc'] ?? null,
                                        'added_by' => $userId
                                    ])->id;
                                }
                            }
                        }
                    }
                }

                if (is_array($changeUsers) && count($changeUsers) > 0) {
                    foreach ($changeUsers as $thisStatus => $array) {
                        if (isset($allStatusList[$thisStatus])) {
                            foreach ($array as $k => $v) {
                                if (isset($v['edit_id']) && $v['edit_id'] > 0) {
                                    Trigger::where('id', $v['edit_id'])->update([
                                        'status_id' => $v['status'],
                                        'hour' => $v['hour'],
                                        'minute' => $v['minute'],
                                        'sequence' => $k,
                                        'type' => 3,
                                        'time' => self::getStringToTime($v['timetype'], $v['hour'], $v['minute']),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'user_id' => $v['user'],
                                        'updated_by' => $userId
                                    ]);
                                    $toNotBeDeleted[] = $v['edit_id'];
                                } else {
                                    $toNotBeDeleted[] = Trigger::create([
                                        'status_id' => $v['status'],
                                        'sequence' => $k,
                                        'hour' => $v['hour'] ?? null,
                                        'minute' => $v['minute'] ?? null,
                                        'type' => 3,
                                        'time' => self::getStringToTime($v['timetype'], ($v['hour'] ?? null), ($v['minute'] ?? null)),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'user_id' => $v['user'],
                                        'added_by' => $userId
                                    ])->id;
                                }
                            }
                        }
                    }
                }

                if (is_array($toNotBeDeleted)) {

                    if (count($toNotBeDeleted) > 0) {
                        $ids = Trigger::whereNotIn('id', $toNotBeDeleted)->select('id')->pluck('id')->toArray();
                        $ids = Trigger::whereIn('id', $ids)->select('id')->pluck('id')->toArray();
    
                        if (count($ids) > 0) {
                            AddTaskToOrderTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                            ChangeOrderStatusTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                            ChangeOrderUser::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
    
                            Trigger::whereIn('id', $ids)->delete();
                        }
                    } else if (empty($toNotBeDeleted)) {
                            $ids = Trigger::select('id')->pluck('id')->toArray();
                            AddTaskToOrderTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                            ChangeOrderStatusTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                            ChangeOrderUser::where('executed', 0)->whereIn('trigger_id', $ids)->delete();

                            Trigger::whereIn('id', $ids)->delete();
                    }
                }

                DB::commit();
                return redirect()->route('sales-order-status')->with('success', 'Sales order status updated successfully.');
            }

        } catch (\Exception $e) {
            Helper::logger($e->getMessage() . ' ' . $e->getLine());
            DB::rollBack();
            return redirect()->route('sales-order-status-edit')->with('error', Helper::$errorMessage);
        }

        DB::rollBack();
        return redirect()->route('sales-order-status-edit')->with('error', 'Add atleast a card to save.');
    }

    public function sequence(Request $request) {
        if (SalesOrderStatus::custom()->where('id', $request->status)->doesntExist()) {
            return response()->json(['status' => false, 'container' => true]);
        }

        if (SalesOrder::where('id', $request->order)->doesntExist()) {
            return response()->json(['status' => false, 'card' => true]);
        }

        $oldStatus = SalesOrder::where('id', $request->order)->select('status')->first()->status;

        if (SalesOrder::where('id', $request->order)->update(['status' => $request->status]) && isset($oldStatus)) {

            AddTaskToOrderTrigger::where('order_id', $request->order)->where('status_id', '!=',$request->status)->where('executed', 0)->delete();
            ChangeOrderUser::where('order_id', $request->order)->where('status_id', '!=', $request->status)->where('executed', 0)->delete();
            ChangeOrderStatusTrigger::where('order_id', $request->order)->where('status_id', '!=', $request->status)->where('executed', 0)->delete();

            $disOrder = SalesOrder::where('id', $request->order)->first();

            event(new \App\Events\OrderStatusEvent('order-status-change', [
                'orderId' => $request->order,
                'orderStatus' => $request->status,
                'orderOldStatus' => $oldStatus,
                'windowId' => $request->windowId,
                'users' => [Deliver::where('so_id', $disOrder->id)->where('status', 1)->first()->user_id ?? null, $disOrder->added_by]
            ]));
           
            $fromStatus = SalesOrderStatus::custom()->withTrashed()->where('id', $oldStatus)->first();
            $toStatus = SalesOrderStatus::custom()->withTrashed()->where('id', $request->status)->first();

            \App\Models\TriggerLog::create([
                'trigger_id' => 0,
                'cron_id' => $disOrder->id,
                'order_id' => $disOrder->id,
                'watcher_id' => auth()->user()->id,
                'next_status_id' => $request->status,
                'current_status_id' => $oldStatus,
                'type' => 2,
                'time_type' => $disOrder->time_type,
                'main_type' => $disOrder->main_type,
                'hour' => $disOrder->hour,
                'minute' => $disOrder->minute,
                'time' => $disOrder->time,
                'executed_at' => $disOrder->executed_at,
                'user_id' => auth()->user()->id,
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

            $newStatus = Trigger::where('status_id', $request->status)->where('type', 2)
            ->whereIn('action_type', [1, 3])->first()->next_status_id ?? 0;

            /** TRIGGERS **/

            /** TASKS **/
            $currentTime1 = date('Y-m-d H:i:s');
            $y = [];

            try {

                $triggers = Trigger::where('type', 1)->where('status_id', $request->status)->whereIn('action_type', [1, 3]);
                if ($triggers->count() > 0) {

                    foreach ($triggers->get() as $t) {

                        $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                        
                        $record = AddTaskToOrderTrigger::create([
                            'order_id' => $request->order,
                            'status_id' => $request->status,
                            'added_by' => auth()->user()->id,
                            'time' => $t->time,
                            'type' => $t->time_type,
                            'main_type' => $t->action_type,
                            'description' => $t->task_description,
                            'current_status_id' => $oldStatus,
                            'executed_at' => $currentTime1,
                            'trigger_id' => $t->id
                        ]);

                        if ($t->time_type == 1) {
                            $y[] = $record->id;
                        }
                    }
                }

                (new \App\Console\Commands\TaskTrigger)->handle($y);

            } catch (\Exception $e) {
                Helper::logger($e->getMessage());
            }

            /** TASKS **/

            /** Change User **/
            $currentTime1 = date('Y-m-d H:i:s');
            $y = [];

            try {

                $triggers = Trigger::where('type', 3)->where('status_id', $request->status)->whereIn('action_type', [1, 3]);
                if ($triggers->count() > 0) {

                    foreach ($triggers->get() as $t) {

                        $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                        
                        $record = ChangeOrderUser::create([
                            'order_id' => $request->order,
                            'status_id' => $request->status,
                            'added_by' => auth()->user()->id,
                            'time' => $t->time,
                            'type' => $t->time_type,
                            'main_type' => $t->action_type,
                            'user_id' => $t->user_id,
                            'current_status_id' => $oldStatus,
                            'executed_at' => $currentTime1,
                            'trigger_id' => $t->id
                        ]);

                        if ($t->time_type == 1) {
                            $y[] = $record->id;
                        }
                    }
                }

                (new \App\Console\Commands\ChangeUserForOrderTrigger)->handle($y);

            } catch (\Exception $e) {
                Helper::logger($e->getMessage());
            }

            /** Change User **/


            /** Change order status **/
            $currentTime = date('Y-m-d H:i:s');
            $x = [];

            try {

                $triggers = Trigger::where('type', 2)->where('status_id', $request->status)->whereIn('action_type', [1, 3]);
                if ($triggers->count() > 0) {
                    foreach ($triggers->get() as $t) {

                        $currentTime = date('Y-m-d H:i:s', strtotime("{$t->time}"));

                        $record = ChangeOrderStatusTrigger::create([
                            'order_id' => $request->order,
                            'status_id' => $newStatus,
                            'added_by' => auth()->user()->id,
                            'time' => $t->time,
                            'type' => $t->time_type,
                            'main_type' => $t->action_type,
                            'current_status_id' => $request->status,
                            'executed_at' => $currentTime,
                            'trigger_id' => $t->id
                        ]);
                        
                        if ($t->time_type == 1) {
                            $x[] = $record->id;
                        }
                    }
                }

                (new \App\Console\Commands\StatusTrigger)->handle($x);

            } catch (\Exception $e) {
                Helper::logger($e->getMessage());
            }
            /** Change order status **/


            return response()->json(['status' => true]);
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function list(Request $request) {
        return abort(404);

        $statuses = SalesOrderStatus::custom()->active()->select('id', 'name', 'color')->get();

        if (!$request->ajax()) {
            $moduleName = 'Sales Order Status';
    
            return view('sales-orders-status.list', compact('moduleName', 'statuses'));
        }

        $orders = SalesOrder::with(['items', 'ostatus']);

        if (in_array(3, User::getUserRoles())) {
            $driversOrder = Deliver::where('user_id', auth()->user()->id)->select('so_id')->pluck('so_id')->toArray();
            $orders = $orders->where(function ($builder) use ($driversOrder) {
                $builder->whereIn('id', $driversOrder);
            })
            ->where(function ($builder) {
                    $builder->where('responsible_user', '')
                ->orWhereNull('responsible_user');
            });
        } else if (!in_array(1, User::getUserRoles())) {
            $orders = $orders->where('added_by', auth()->user()->id);
        }

        $tempCount = $orders->count();

        if (isset($request->order[1]['column']) && $request->order[1]['column'] == 0) {
            $orders = $orders->orderBy('id', 'desc');
        }

        return dataTables($orders)
                ->addColumn('checkbox', function ($row) {
                    return "<input type='checkbox' class='form-check-input single-checkbox' value='{$row->id}' />";
                })
                ->editColumn('order_no', function ($row) {
                    $route = route('sales-orders.view', encrypt($row->id));
                    return "<a target='_blank' href='{$route}' class='color-blue'> {$row->order_no} </a>";
                })
                ->addColumn('status', function ($row) use ($statuses) {
                   
                    $manageSt = ManageStatus::where('status_id', $row->status)->first()->ps ?? [];
                    $statuses = SalesOrderStatus::custom()->active()->whereIn('id', $manageSt)->select('id', 'name', 'color')->get();

                    if (count($statuses) > 0) {

                        $html = 
                        '<div class="status-main button-dropdown position-relative">
                            <label class="status-label" style="background:' . ($row->ostatus->color ?? '') . ';color:' . (Helper::generateTextColor($row->ostatus->color ?? '')) . ';"> ' . ($row->ostatus->name ?? '') . ' </label>
                            <button class="dropdown-toggle status-opener ms-2 d-inline-flex align-items-center justify-content-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 20 19" fill="none">
                                <path d="M0.998047 14.613V18.456H4.84105L16.175 7.12403L12.332 3.28103L0.998047 14.613ZM19.147 4.15203C19.242 4.05721 19.3174 3.94458 19.3688 3.82061C19.4202 3.69664 19.4466 3.56374 19.4466 3.42953C19.4466 3.29533 19.4202 3.16243 19.3688 3.03846C19.3174 2.91449 19.242 2.80186 19.147 2.70703L16.747 0.307035C16.6522 0.212063 16.5396 0.136719 16.4156 0.0853128C16.2916 0.0339065 16.1588 0.00744629 16.0245 0.00744629C15.8903 0.00744629 15.7574 0.0339065 15.6335 0.0853128C15.5095 0.136719 15.3969 0.212063 15.302 0.307035L13.428 2.18403L17.271 6.02703L19.147 4.15203Z" fill="#3C3E42"/>
                                </svg>
                            </button>
                            <div class="dropdown-menu status-modal">
                                <div class="status-dropdown">';

                                foreach ($statuses as $k => $status) {
                                    if ($k == 0) {
                                    $html .= '<button type="button" data-sid="' . $status->id . '" data-oid="' . $row->id . '" style="background:' . $status->color . ';color:' . Helper::generateTextColor($status->color) . ';" class="status-dropdown-toggle d-flex align-items-center justify-content-between f-14">
                                        <span>' . $status->name . '</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                                            <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                                        </svg>
                                    </button>';
                                    }
                                }
    
                                    $html .= '<div class="status-dropdown-menu">';
    
                                    foreach ($statuses as $status) {
                                        $html .= '<li class="f-14" data-isajax="true" style="background: '. $status->color .';color:' . Helper::generateTextColor($status->color) . ';" data-sid="' . $status->id . '" data-oid="' . $row->id . '" > '. $status->name .' </li>';
                                    }
    
                                    $html .= '</div>
                                </div>
                                <div class="status-action-btn mt-2 position-relative -z-1">
                                    <button class="status-save-btn btn-primary f-500 f-14 d-inline-block" disabled>Save</button>
                                    <button class="refresh-dt hide-dropdown btn-default f-500 f-14 d-inline-block ms-1">Cancel</button>
                                </div>
                            </div>
                        </div>';
                    } else {
                        $html = "<strong> " . strtoupper($row->ostatus->name ?? '-') . " </strong>";
                    }



                    return $html;
                })
                ->addColumn('date', function ($row) {
                    return date('d-m-Y', strtotime($row->date));
                })
                ->addColumn('amount', function ($row) {
                    return Helper::currency($row->items->sum('amount'));
                })
                ->rawColumns(['order_no', 'checkbox', 'status', 'action'])
                ->with(['totalOrders' => $tempCount])
                ->make(true);
    }

    public function status (Request $request) {
        $response = false;
        $message = Helper::$errorMessage;
        $color = $text = '';

        if (!empty($request->status) && !empty($request->order)) {
            $isStatus = SalesOrderStatus::custom()->where('id', $request->status);
            if ($isStatus->exists()) {
                $color = $isStatus->first()->color;
                $text = $isStatus->first()->name;

                if (SalesOrder::where('id', $request->order)->first()->status == $request->status) {
                    $response = true;
                    $message = 'Status Updated successfully';

                    return response()->json(['status' => $response, 'message' => $message, 'color' => $color, 'text' => $text]);
                }

                $oldStatus = SalesOrder::where('id', $request->order)->select('status')->first()->status ?? 1;

                if (SalesOrder::where('id', $request->order)->update(['status' => $request->status])) {
                    $response = true;
                    $message = 'Status Updated successfully';

                    AddTaskToOrderTrigger::where('order_id', $request->order)->where('status_id', '!=',$request->status)->where('executed', 0)->delete();
                    ChangeOrderUser::where('order_id', $request->order)->where('status_id', '!=', $request->status)->where('executed', 0)->delete();
                    ChangeOrderStatusTrigger::where('order_id', $request->order)->where('status_id', '!=', $request->status)->where('executed', 0)->delete();

                    $disOrder = SalesOrder::where('id', $request->order)->first();

                    event(new \App\Events\OrderStatusEvent('order-status-change', [
                        'orderId' => $request->order,
                        'orderStatus' => $request->status,
                        'orderOldStatus' => $oldStatus,
                        'windowId' => $request->windowId,
                        'users' => [Deliver::where('so_id', $disOrder->id)->where('status', 1)->first()->user_id ?? null, $disOrder->added_by]
                    ]));

                    $fromStatus = SalesOrderStatus::custom()->withTrashed()->where('id', $oldStatus)->first();
                    $toStatus = SalesOrderStatus::custom()->withTrashed()->where('id', $request->status)->first();

                    \App\Models\TriggerLog::create([
                        'trigger_id' => 0,
                        'cron_id' => $disOrder->id,
                        'order_id' => $disOrder->id,
                        'watcher_id' => auth()->user()->id,
                        'next_status_id' => $request->status,
                        'current_status_id' => $oldStatus,
                        'type' => 2,
                        'description' => $request->comment,
                        'time_type' => $disOrder->time_type,
                        'main_type' => $disOrder->main_type,
                        'hour' => $disOrder->hour,
                        'minute' => $disOrder->minute,
                        'time' => $disOrder->time,
                        'executed_at' => $disOrder->executed_at,
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

                    $newStatus = Trigger::where('status_id', $request->status)->where('type', 2)
                    ->whereIn('action_type', [1, 3])->first()->next_status_id ?? 0;
        
                    /** TRIGGERS **/
        
                    /** TASKS **/
                    $currentTime1 = date('Y-m-d H:i:s');
                    $y = [];
        
                    try {
        
                        $triggers = Trigger::where('type', 1)->where('status_id', $request->status)->whereIn('action_type', [1, 3]);
                        if ($triggers->count() > 0) {
        
                            foreach ($triggers->get() as $t) {
        
                                $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                                
                                $record = AddTaskToOrderTrigger::create([
                                    'order_id' => $request->order,
                                    'status_id' => $request->status,
                                    'added_by' => auth()->user()->id,
                                    'time' => $t->time,
                                    'type' => $t->time_type,
                                    'main_type' => $t->action_type,
                                    'description' => $t->task_description,
                                    'current_status_id' => $oldStatus,
                                    'executed_at' => $currentTime1,
                                    'trigger_id' => $t->id
                                ]);
        
                                if ($t->time_type == 1) {
                                    $y[] = $record->id;
                                }
                            }
                        }
        
                        (new \App\Console\Commands\TaskTrigger)->handle($y);
        
                    } catch (\Exception $e) {
                        Helper::logger($e->getMessage());
                    }
        
                    /** TASKS **/
        
                    /** Change User **/
                    $currentTime1 = date('Y-m-d H:i:s');
                    $y = [];
        
                    try {
        
                        $triggers = Trigger::where('type', 3)->where('status_id', $request->status)->whereIn('action_type', [1, 3]);
                        if ($triggers->count() > 0) {
        
                            foreach ($triggers->get() as $t) {
        
                                $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                                
                                $record = ChangeOrderUser::create([
                                    'order_id' => $request->order,
                                    'status_id' => $request->status,
                                    'added_by' => auth()->user()->id,
                                    'time' => $t->time,
                                    'type' => $t->time_type,
                                    'main_type' => $t->action_type,
                                    'user_id' => $t->user_id,
                                    'current_status_id' => $oldStatus,
                                    'executed_at' => $currentTime1,
                                    'trigger_id' => $t->id
                                ]);
        
                                if ($t->time_type == 1) {
                                    $y[] = $record->id;
                                }
                            }
                        }
        
                        (new \App\Console\Commands\ChangeUserForOrderTrigger)->handle($y);
        
                    } catch (\Exception $e) {
                        Helper::logger($e->getMessage());
                    }
        
                    /** Change User **/
        
        
                    /** Change order status **/
                    $currentTime = date('Y-m-d H:i:s');
                    $x = [];
        
                    try {
        
                        $triggers = Trigger::where('type', 2)->where('status_id', $request->status)->whereIn('action_type', [1, 3]);
                        if ($triggers->count() > 0) {
                            foreach ($triggers->get() as $t) {
        
                                $currentTime = date('Y-m-d H:i:s', strtotime("{$t->time}"));
        
                                $record = ChangeOrderStatusTrigger::create([
                                    'order_id' => $request->order,
                                    'status_id' => $newStatus,
                                    'added_by' => auth()->user()->id,
                                    'time' => $t->time,
                                    'type' => $t->time_type,
                                    'main_type' => $t->action_type,
                                    'current_status_id' => $request->status,
                                    'executed_at' => $currentTime,
                                    'trigger_id' => $t->id
                                ]);
                                
                                if ($t->time_type == 1) {
                                    $x[] = $record->id;
                                }
                            }
                        }
        
                        (new \App\Console\Commands\StatusTrigger)->handle($x);
        
                    } catch (\Exception $e) {
                        Helper::logger($e->getMessage());
                    }
                    /** Change order status **/



                }
            }
        }
        return response()->json(['status' => $response, 'message' => $message, 'color' => $color, 'text' => $text]);
    }

    public function statusBulkUpdate(Request $request) {
        if (SalesOrderStatus::custom()->where('id', $request->status)->doesntExist()) {
            return redirect()->back()->with('error', Helper::$errorMessage);
        }

        if (in_array(1, User::getUserRoles())) { //admin
            if ($request->ids == 'all') {
                $resp = SalesOrder::query()->update(['status' => $request->status]);
                return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for all orders successfully.' : Helper::$errorMessage));
            } else {
                $resp = SalesOrder::whereIn('id', explode(',', $request->ids))->update(['status' => $request->status]);
                return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for orders successfully.' : Helper::$errorMessage));
            }
        } else if (in_array(2, User::getUserRoles())) { //seller
            if ($request->ids == 'all') {
                $resp = SalesOrder::where('seller_id', auth()->user()->id)->update(['status' => $request->status]);
                return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for all orders successfully.' : Helper::$errorMessage));
            } else {
                $resp = SalesOrder::where('seller_id', auth()->user()->id)->whereIn('id', explode(',', $request->ids))->update(['status' => $request->status]);
                return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for orders successfully.' : Helper::$errorMessage));
            }
        } else if (in_array(3, User::getUserRoles())) { //driver
            $driver = Deliver::where('user_id', auth()->user()->id);

            if ($driver->exists()) {

                $driver = $driver->select('so_id')->pluck('so_id')->toArray();

                if ($request->ids == 'all') {
                    $resp = SalesOrder::whereIn('id', $driver)->update(['status' => $request->status]);
                    return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for all orders successfully.' : Helper::$errorMessage));
                } else {
                    $resp = SalesOrder::whereIn('id', $driver)->whereIn('id', explode(',', $request->ids))->update(['status' => $request->status]);
                    return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for orders successfully.' : Helper::$errorMessage));
                }

            } else {
                return redirect()->back()->with('success', 'Status updated for all orders successfully.');
            }

        } else {
            return redirect()->back()->with('success', 'Status updated for all orders successfully.');
        }

    }

    public function manageStatus(Request $request) {

        $mstatuses = $request->mstatus;

        $validated = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id' => 'required',
            'mstatus.*' => ['sometimes', function ($y, $x, $fail) use ($mstatuses) {
                if (count(array_filter($mstatuses)) !== count(array_unique(array_filter($mstatuses)))) {
                    $fail("Same status can't be selected more than once.");
                }
            }]
        ], [
            'id.required' => Helper::$errorMessage
        ]);

        if($validated->fails()){
            return response()->json([
                "status" => false,
                "messages" => $validated->messages()->toArray() ?? []
            ]);
        }

        DB::beginTransaction();

        try {
            ManageStatus::updateOrCreate(['status_id' => $request->id], ['possible_status' => !is_null($request->mstatus) ? implode(',', array_filter($request->mstatus)) : '']);
            
            DB::commit();
            return response()->json(['status' => true, 'messages' => 'Status data saved successfully.']);

        } catch (\Exception $e) {
            Helper::logger($e->getMessage() . ' Line No: ', $e->getLine());
            DB::rollBack();
            return response()->json(['status' => false, 'messages' => [Helper::$errorMessage]]);
        }

    }

    public function getManagedStatus(Request $request) {

        $updatedStatuses = SalesOrderStatus::custom()->select('id', 'name')->when(!empty($request->id), fn ($builder) => ($builder->where('id', '!=', $request->id)))->orderBy('sequence', 'ASC')->pluck('name', 'id')->toArray();

        if (ManageStatus::where('status_id', $request->id)->exists()) {
            return response()->json(['exists' => true, 'data' => ManageStatus::where('status_id', $request->id)->first()->toArray(), 'updatedStatuses' => $updatedStatuses]);
        }

        return response()->json(['exists' => false, 'updatedStatuses' => $updatedStatuses]);
    }


    public function nextStatus(Request $request) {
        $thisStatus = SalesOrderStatus::custom()->where('id', $request->id);
        $possibleStatuses = [];
        $view = '-';
        
        if ($thisStatus->exists()) {

            $possibleStatuses = ManageStatus::where('status_id', $request->id)
            ->where('possible_status', '!=', '')
            ->first()->ps ?? [];

            if (!empty($request->trigger)) {
                $thisSts = Trigger::where('id', $request->trigger)->first()->next_status_id ?? null;
                if (!is_null($thisSts)) {
                    array_push($possibleStatuses, $thisSts);
                }
            }

            $statuses = SalesOrderStatus::custom()->whereIn('id', $possibleStatuses)->select('id', 'name', 'color')->get();
            $possibleStatuses = SalesOrderStatus::custom()->whereIn('id', $possibleStatuses)->select('id', 'name')->pluck('name', 'id')->toArray();
            $cs = $thisStatus->first()->name ?? '';
            
            $view = view('sales-orders-status.status', compact('statuses', 'cs'))->render();
        }

        return response()->json(['data' => $possibleStatuses, 'view' => $view]);
    }

    public function nextStatusForTask(Request $request) {
        $thisStatus = SalesOrderStatus::custom()->where('id', $request->id);
        $possibleStatuses = [];
        $view = '-';
        
        if ($thisStatus->exists()) {

            $possibleStatuses = ManageStatus::where('status_id', $request->id)
            ->where('possible_status', '!=', '')
            ->first()->ps ?? [];

            $statuses = SalesOrderStatus::custom()->whereIn('id', $possibleStatuses)->select('id', 'name', 'color')->get();
            $possibleStatuses = SalesOrderStatus::custom()->whereIn('id', $possibleStatuses)->select('id', 'name')->pluck('name', 'id')->toArray();
            $cs = $thisStatus->first()->name ?? '';
            
            $view = view('sales-orders-status.status', compact('statuses', 'cs'))->render();
        }

        return response()->json(['data' => $possibleStatuses, 'view' => $view]);
    }

    public static function getStringToTime($type, $hour, $minute) {
        $additionalTime = '+0 seconds';

        if ($type == '1') {
            $additionalTime = '+0 seconds';
        } else if ($type == '2') {
            $additionalTime = '+5 minutes';
        } else if ($type == '3') {
            $additionalTime = '+10 minutes';
        } else if ($type == '4') {
            $additionalTime = '+24 hours';
        } else if ($type == '5') {
            if (!is_numeric($hour)) {
                $hour = 1;
            }
            if ($hour > 720) {
                $hour = 720;
            }
            if (!is_numeric($minute)) {
                $minute = 0;
            }
            if ($minute > 60) {
                $minute = 60;
            }

            $additionalTime = "+{$hour} hours +{$minute} minutes";
        }

        return $additionalTime;
    }

    public function putOnCron(Request $request) {
        $orderId = $request->clid;
        $time = $request->cltime;
        $orderStatus = $request->clstatus;
        $hour = $request->hour;
        $minute = $request->minute;
        $additionalTime = '+0 seconds';

        if (!empty($orderId) && !empty($time) && !empty($orderStatus)) {
                if ($time == '1') {
                    $additionalTime = '+0 seconds';
                } else if ($time == '2') {
                    $additionalTime = '+5 minutes';
                } else if ($time == '3') {
                    $additionalTime = '+10 minutes';
                } else if ($time == '4') {
                    $additionalTime = '+24 hours';
                } else if ($time == '5') {
                    if (!is_numeric($request->hour)) {
                        $hour = '1';
                    }
                    if (!is_numeric($request->minute)) {
                        $minute = '0';
                    }

                    $additionalTime = "+{$hour} hours +{$minute} minutes";
                } else {
                    return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
                }

                $currentStatusId = SalesOrder::where('id', $orderId)->first()->status ?? 0;

                if (ChangeOrderStatusTrigger::where('order_id', $orderId)->where('executed', 0)->exists()) {
                    ChangeOrderStatusTrigger::where('order_id', $orderId)
                    ->where('executed', 0)->update([
                        'updated_by' => auth()->user()->id,
                        'time' => $additionalTime,
                        'type' => $time,
                        'executed_at' => date('Y-m-d H:i:s', strtotime($additionalTime)),
                        'executed' => true
                    ]);

                }

                ChangeOrderStatusTrigger::create([
                    'order_id' => $orderId,
                    'status_id' => $orderStatus,
                    'added_by' => auth()->user()->id,
                    'time' => $additionalTime,
                    'type' => $time,
                    'current_status_id' => $currentStatusId,
                    'executed_at' => date('Y-m-d H:i:s', strtotime($additionalTime))
                ]);

                try {
                    if (isset($additionalTime) && ($additionalTime == '+0 seconds' || ($hour == '0' && $minute == '1'))) {
                        (new \App\Console\Commands\StatusTrigger())->handle();
                    }
                } catch (\Exception $e) {
                    Helper::logger($e->getMessage());
                }

                return response()->json(['status' => true, 'message' => 'Order trigger data saved successfully.']);

        } else {
            return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
        }
    }

    public function orderDetailInBoard(Request $request) {
        $order = SalesOrder::with(['tstatus', 'ostatus', 'items', 'task', 'userchanges'])->where('id', $request->id);

        if ($order->exists()) {
            $order = $order->first();
            $logs = \App\Models\TriggerLog::where('order_id', $order->id)->orderBy('id', 'DESC')->get();
            return response()->json(['status' => true, 'view' => view('sales-orders-status.order-details', compact('order', 'logs'))->render()]);
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function putTaskForOrder(Request $request) {
        $orderId = $request->atid;
        $time = $request->attime;
        $type = $request->attype;
        $orderStatus = $request->atstatus;
        $hour = $request->add_task_hour;
        $minute = $request->add_task_minute;
        $desc = $request->task_desc;
        $additionalTime = '+0 seconds';


        if (!empty($orderId) && !empty($time) && !empty($type) && !empty($orderStatus)) {
            if ($time == '1') {
                $additionalTime = '+0 seconds';
            } else if ($time == '2') {
                $additionalTime = '+5 minutes';
            } else if ($time == '3') {
                $additionalTime = '+10 minutes';
            } else if ($time == '4') {
                $additionalTime = '+24 hours';
            } else if ($time == '5') {
                if (!is_numeric($request->add_task_hour)) {
                    $hour = '1';
                }
                if (!is_numeric($request->add_task_minute)) {
                    $minute = '0';
                }

                $additionalTime = "+{$hour} hours +{$minute} minutes";
            } else {
                return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
            }

            $currentStatusId = SalesOrder::where('id', $orderId)->first()->status ?? 0;

            AddTaskToOrderTrigger::create([
                'order_id' => $orderId,
                'status_id' => $orderStatus,
                'added_by' => auth()->user()->id,
                'time' => $additionalTime,
                'type' => $time,
                'main_type' => 2,
                'description' => $desc,
                'current_status_id' => $currentStatusId
            ]);

            return response()->json(['status' => true, 'message' => 'Order task data saved successfully.']);

        } else {
            return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
        }
    }

    public function removeTask(Request $request) {
        $task = AddTaskToOrderTrigger::where('id', $request->id);

        if ($task->exists()) {
            return response()->json(['status' => $task->delete(), 'message' => 'Task deleted successfully.', 'count' => AddTaskToOrderTrigger::where('order_id', $request->order)->where('executed', 1)->count()]);
        }

        return response()->json(['status' => false, 'message' => 'Task not found.']);
    }

    public function saveDescription(Request $request) {
        if (AddTaskToOrderTrigger::where('id', $request->id)->exists()) {
            AddTaskToOrderTrigger::where('id', $request->id)->update([
                'completed_description' => $request->text,
                'completed' => true
            ]);
            return response()->json(['status' => true, 'message' => 'Task completed successfully.']);
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function salesOrderResponsibleUser(Request $request) {
        $selectedUser = null;
        $addedData = ['user' => null, 'type' => null];

        $users = "<option value='' selected> Select a user </option>";

        if ($request->has('trigger') && !empty($request->trigger)) {
            $selectedUser = Trigger::where('id', $request->trigger)->first()->user_id ?? null;
        }

        $users .= "<option value='1' " . ($selectedUser == 1 ? 'selected' : '') . " data-name='DRIVER' data-label='DRIVER' > DRIVER </option>
        <option value='2' " . ($selectedUser == 2 ? 'selected' : '') . " data-name='SELLER' data-label='SELLER' > SELLER </option>";

        return response()->json(['status' => true, 'users' => $users, 'current' => $selectedUser, 'total' => 2, 'addedData' => $addedData]);
    }

    public function salesOrderResponsibleUserSave(Request $request) {
        $order = ChangeOrderUser::where('order_id', $request->cuid)->where('status_id', $request->custatus);

        if ($order->exists()) {
            $order->delete();
        }

        if (!empty($request->user) && !empty($request->cuid) && !empty($request->custatus)) {
            ChangeOrderUser::create([
                'order_id' => $request->cuid,
                'status_id' => $request->custatus,
                'added_by' => auth()->user()->id,
                'user_id' => $request->user
            ]);
        }

        return response()->json(['status' => true, 'message' => 'User changed successfully for this order.']);
    }

    public function getTriggerTasks(Request $request) {
        if (Trigger::where('id', $request->id)->exists()) {
            return response()->json(['status' => true, 'data' => Trigger::where('id', $request->id)->first()]);
        }

        return response()->json(['status' => false, 'message' => 'No trigger found.']);
    }

    public function deleteStatus(Request $request) {
        $status = SalesOrderStatus::custom()->where('id', $request->id);

        if ($status->exists()) {

            DB::beginTransaction();
            $status = $status->first()->id ?? null;

            try {
                AddTaskToOrderTrigger::where('status_id', $status)->delete();
                ChangeOrderStatusTrigger::where('status_id', $status)->delete();
                ChangeOrderUser::where('status_id', $status)->delete();
                Trigger::where('status_id', $status)->whereIn('type', [1, 3])->delete();
                Trigger::where('next_status_id', $status)->where('type', [2])->delete();
                ManageStatus::where('status_id', $status)->delete();
                SalesOrderStatus::custom()->where('id', $status)->delete();

                foreach (ManageStatus::orWhereRaw('FIND_IN_SET(?, possible_status)', [$status])->get() as $s) {
                    $arr = $s->ps;

                    if (in_array($status, $arr)) {
                        $arr = array_diff($arr, [$status]);
                    }

                    ManageStatus::where('id', $s->id)->update([
                        'possible_status' => implode(',', $arr)
                    ]);
                }

                $allSalesOrders = SalesOrder::where('status', $status)->select('id')->pluck('id')->toArray();
                SalesOrder::where('status', $status)->update(['status' => 2]);


                foreach ($allSalesOrders as $soId) {

                    /** TRIGGERS **/

                    $oldStatus = SalesOrder::where('id', $soId)->select('status')->first()->status;

                    $newStatus = Trigger::where('status_id', 1)->where('type', 2)
                    ->whereIn('action_type', [1, 3])->first()->next_status_id ?? 0;
        
                    /** TASKS **/
                    $currentTime1 = date('Y-m-d H:i:s');
                    $y = [];
        
                    try {
        
                        $triggers = Trigger::where('type', 1)->where('status_id', 1)->whereIn('action_type', [1, 3]);
                        if ($triggers->count() > 0) {
        
                            foreach ($triggers->get() as $t) {
        
                                $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                                
                                $record = AddTaskToOrderTrigger::create([
                                    'order_id' => $soId,
                                    'status_id' => 2,
                                    'added_by' => auth()->user()->id,
                                    'time' => $t->time,
                                    'type' => $t->time_type,
                                    'main_type' => $t->action_type,
                                    'description' => $t->task_description,
                                    'current_status_id' => $oldStatus,
                                    'executed_at' => $currentTime1,
                                    'trigger_id' => $t->id
                                ]);
        
                                if ($t->time_type == 1) {
                                    $y[] = $record->id;
                                }
                            }
                        }
        
                        (new \App\Console\Commands\TaskTrigger)->handle($y);
        
                    } catch (\Exception $e) {
                        Helper::logger($e->getMessage());
                    }
        
                    /** TASKS **/

                    /** Change User **/
                    $currentTime1 = date('Y-m-d H:i:s');
                    $y = [];
        
                    try {
        
                        $triggers = Trigger::where('type', 3)->where('status_id', 1)->whereIn('action_type', [1, 3]);
                        if ($triggers->count() > 0) {
        
                            foreach ($triggers->get() as $t) {
        
                                $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                                
                                $record = ChangeOrderUser::create([
                                    'order_id' => $soId,
                                    'status_id' => 2,
                                    'added_by' => auth()->user()->id,
                                    'time' => $t->time,
                                    'type' => $t->time_type,
                                    'main_type' => $t->action_type,
                                    'user_id' => $t->user_id,
                                    'current_status_id' => $oldStatus,
                                    'executed_at' => $currentTime1,
                                    'trigger_id' => $t->id
                                ]);
        
                                if ($t->time_type == 1) {
                                    $y[] = $record->id;
                                }
                            }
                        }
        
                        (new \App\Console\Commands\ChangeUserForOrderTrigger)->handle($y);
        
                    } catch (\Exception $e) {
                        Helper::logger($e->getMessage());
                    }
        
                    /** Change User **/
        
        
                    /** Change order status **/
                    $currentTime = date('Y-m-d H:i:s');
                    $x = [];
        
                    try {
        
                        $triggers = Trigger::where('type', 2)->where('status_id', 1)->whereIn('action_type', [1, 3]);
                        if ($triggers->count() > 0) {
                            foreach ($triggers->get() as $t) {
        
                                $currentTime = date('Y-m-d H:i:s', strtotime("{$t->time}"));
        
                                $record = ChangeOrderStatusTrigger::create([
                                    'order_id' => $soId,
                                    'status_id' => $newStatus,
                                    'added_by' => auth()->user()->id,
                                    'time' => $t->time,
                                    'type' => $t->time_type,
                                    'main_type' => $t->action_type,
                                    'current_status_id' => 2,
                                    'executed_at' => $currentTime,
                                    'trigger_id' => $t->id
                                ]);
                                
                                if ($t->time_type == 1) {
                                    $x[] = $record->id;
                                }
                            }
                        }
        
                        (new \App\Console\Commands\StatusTrigger)->handle($x);
        
                    } catch (\Exception $e) {
                        Helper::logger($e->getMessage());
                    }
                    /** Change order status **/

                }

                DB::commit();
                return response()->json(['status' => true, 'message' => "Status deleted successfully."]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
            }

        }

        return response()->json(['status' => false, 'message' => "Status not found."]);
    }

    public function acceptOrder(Request $request) {
        if (!empty($request->id)) {
            $order = SalesOrder::where('id', $request->id);
            if ($order->exists()) {
                Deliver::where('user_id', auth()->user()->id)->where('so_id', $request->id)->where('status', 0)->update(['status' => 1]);
                SalesOrder::where('id', $request->id)->update(['status' => 2, 'responsible_user' => auth()->user()->id]);
                $disOrder = SalesOrder::with('items')->where('id', $request->id)->first();
                $soId = $disOrder->id;

                \App\Models\TriggerLog::create([
                    'trigger_id' => 0,
                    'order_id' => $soId,
                    'watcher_id' => null,
                    'next_status_id' => 3,
                    'current_status_id' => 2,
                    'type' => 2,
                    'time_type' => $disOrder->time_type,
                    'main_type' => $disOrder->main_type,
                    'hour' => $disOrder->hour,
                    'minute' => $disOrder->minute,
                    'time' => $disOrder->time,
                    'executed_at' => date('Y-m-d H:i:s'),
                    'executed' => 1,
                    'from_status' => [
                       'name' => 'INCOMING ORDER',
                       'color' => '#4237ce'
                    ],
                    'to_status' => [
                        'name' => 'NEW',
                        'color' => '#a9ebfc'
                     ]
                ]);

                $htmlElement = '<div class="card card-light card-outline mb-2 draggable-card portlet" data-cardchild="' . $disOrder->id . '" data-otitle="'. $disOrder->order_no .'">
                    <div class="card-body bg-white border-0 p-2 d-flex justify-content-between portlet-header ui-sortable-handle ui-corner-all">
                        <div>
                            <p class="color-blue">'. $disOrder->order_no .'</p>
                            <p class="no-m font-13">
                                '. Helper::currency($disOrder->items->sum('amount')) .' </p>
                        </div>
                        <div class="d-flex align-items-end flex-column">
                            <div class="card-date f-12 c-7b">
                                '. \Carbon\Carbon::parse($disOrder->date)->diffForHumans() .'
                            </div>
                        </div>
                    </div>
                </div>';

                event(new \App\Events\OrderStatusEvent('order-status-change', [
                    'orderId' => $disOrder->id,
                    'orderStatus' => 2,
                    'element' => $htmlElement,
                    'windowId' => Str::random(30),
                    'users' => [auth()->user()->id, $disOrder->added_by]
                ]));

                /** TRIGGERS **/

                $oldStatus = SalesOrder::where('id', $soId)->select('status')->first()->status;

                $newStatus = Trigger::where('status_id', 2)->where('type', 2)
                ->whereIn('action_type', [1, 2, 3])->first()->next_status_id ?? 0;
    
                /** TASKS **/
                $currentTime1 = date('Y-m-d H:i:s');
                $y = [];
    
                try {
    
                    $triggers = Trigger::where('type', 1)->where('status_id', 2)->whereIn('action_type', [2, 3]);
                    if ($triggers->count() > 0) {
    
                        foreach ($triggers->get() as $t) {
    
                            $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                            
                            $record = AddTaskToOrderTrigger::create([
                                'order_id' => $soId,
                                'status_id' => 2,
                                'added_by' => auth()->user()->id,
                                'time' => $t->time,
                                'type' => $t->time_type,
                                'main_type' => $t->action_type,
                                'description' => $t->task_description,
                                'current_status_id' => $oldStatus,
                                'executed_at' => $currentTime1,
                                'trigger_id' => $t->id
                            ]);
    
                            if ($t->time_type == 1) {
                                $y[] = $record->id;
                            }
                        }
                    }
    
                    (new \App\Console\Commands\TaskTrigger)->handle($y);
    
                } catch (\Exception $e) {
                    Helper::logger($e->getMessage());
                }
    
                /** TASKS **/

                /** Change User **/
                $currentTime1 = date('Y-m-d H:i:s');
                $y = [];
    
                try {
    
                    $triggers = Trigger::where('type', 3)->where('status_id', 2)->whereIn('action_type', [2, 3]);
                    if ($triggers->count() > 0) {
    
                        foreach ($triggers->get() as $t) {
    
                            $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));
                            
                            $record = ChangeOrderUser::create([
                                'order_id' => $soId,
                                'status_id' => 2,
                                'added_by' => auth()->user()->id,
                                'time' => $t->time,
                                'type' => $t->time_type,
                                'main_type' => $t->action_type,
                                'user_id' => $t->user_id,
                                'current_status_id' => $oldStatus,
                                'executed_at' => $currentTime1,
                                'trigger_id' => $t->id
                            ]);
    
                            if ($t->time_type == 1) {
                                $y[] = $record->id;
                            }
                        }
                    }
    
                    (new \App\Console\Commands\ChangeUserForOrderTrigger)->handle($y);
    
                } catch (\Exception $e) {
                    Helper::logger($e->getMessage());
                }
    
                /** Change User **/
    
    
                /** Change order status **/
                $currentTime = date('Y-m-d H:i:s');
                $x = [];
    
                try {
    
                    $triggers = Trigger::where('type', 2)->where('status_id', 2)->whereIn('action_type', [2, 3]);
                    if ($triggers->count() > 0) {
                        foreach ($triggers->get() as $t) {
    
                            $currentTime = date('Y-m-d H:i:s', strtotime("{$t->time}"));
    
                            $record = ChangeOrderStatusTrigger::create([
                                'order_id' => $soId,
                                'status_id' => $newStatus,
                                'added_by' => auth()->user()->id,
                                'time' => $t->time,
                                'type' => $t->time_type,
                                'main_type' => $t->action_type,
                                'current_status_id' => 2,
                                'executed_at' => $currentTime,
                                'trigger_id' => $t->id
                            ]);
                            
                            if ($t->time_type == 1) {
                                $x[] = $record->id;
                            }
                        }
                    }
    
                    (new \App\Console\Commands\StatusTrigger)->handle($x);
    
                } catch (\Exception $e) {
                    Helper::logger($e->getMessage());
                }
                /** Change order status **/
                
                return response()->json(['status' => true, 'message' => 'Accepted order successfully.']);
            }
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function rejectOrder(Request $request) {
        if (!empty($request->id)) {
            $order = SalesOrder::where('id', $request->id);
            if ($order->exists()) {
                Deliver::where('user_id', auth()->user()->id)->where('so_id', $request->id)->where('status', 0)->update(['status' => 2]);
                SalesOrder::where('id', $request->id)->update(['status' => 1]);
                return response()->json(['status' => true, 'message' => 'Rejected order successfully.']);
            }
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function reassignDriverToOrder(Request $request, $id) {
        if (!empty($request->driver)) {
            $soId = decrypt($id);
            $order = SalesOrder::where('id', $soId)->where('status', 1);
            $driver = User::where('id', $request->driver);
            if ($order->exists() && $driver->exists()) {
                $order = $order->first();
                $driver = $driver->first();

                Deliver::create([
                    'user_id' => $request->driver,
                    'so_id' => $soId,
                    'added_by' => auth()->user()->id,
                    'driver_lat' => $driver->lat,
                    'driver_long' => $driver->long,
                    'delivery_location_lat' => $order->lat,
                    'delivery_location_long' => $order->long,
                    'range' => Distance::measure($driver->lat, $driver->long, $order->lat, $order->long)
                ]);

                SalesOrder::where('id', $soId)->update(['status' => 1]);

                return redirect()->back()->with('success', 'Driver assigned successfully');
            }
        }

        return redirect()->back()->with('error', Helper::$errorMessage);
    }
}