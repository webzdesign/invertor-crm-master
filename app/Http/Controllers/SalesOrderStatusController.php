<?php

namespace App\Http\Controllers;

use App\Models\{ChangeOrderStatusTrigger, AddTaskToOrderTrigger, ChangeOrderUser, Setting, Trigger};
use App\Models\{SalesOrderStatus, SalesOrder, SalesOrderItem, Deliver, Role, ManageStatus, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class SalesOrderStatusController extends Controller
{
    public function index() {
        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::orderBy('sequence', 'ASC')->get();
        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];
        $orders = [];

        foreach ($statuses as $status) {
            $tempOrder = SalesOrder::join('sales_order_items', 'sales_order_items.so_id', '=', 'sales_orders.id')->selectRaw("sales_orders.id, sales_orders.order_no, sales_orders.date, SUM(sales_order_items.amount) as amount, sales_orders.status as status")->where('sales_orders.status', $status->id)->groupBy('sales_order_items.so_id');

            if ($tempOrder->exists()) {
                $orders[$status->id] = $tempOrder->get()->toArray();
            }
        }

        return view('sales-orders-status.index', compact('moduleName', 'statuses', 'colours', 'orders'));
    }

    public function edit() {
        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::orderBy('sequence', 'ASC')->get();
        $s = SalesOrderStatus::select('id', 'name')->orderBy('sequence', 'ASC')->pluck('name', 'id')->toArray();
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
        $sequences = $request->sequence;
        $names = $request->name;
        $colors = $request->color;

        if (count($sequences) != count($names)) {
            return redirect()->route('sales-order-status-edit')->with('error', 'Add atleast a card to save.');
        }

        $userId = auth()->user()->id;
        $allStatusList = SalesOrderStatus::select('id', 'name')->pluck('name', 'id')->toArray();
        $toBeDeleted = [];

        DB::beginTransaction();

        try {
            if (count($sequences) > 0) {

                foreach ($sequences as $key => $value) {

                    if (!is_null($value)) {
                        SalesOrderStatus::where('id', $value)->update([
                            'name' => $names[$key],
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                    } else {
                        SalesOrderStatus::create([
                            'name' => $names[$key],
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
                                $toBeDeleted[$thisStatus][] = $k;
                                if (Trigger::where('sequence', $k)->where('status_id', $thisStatus)->exists()) {
                                    Trigger::where('sequence', $k)->where('status_id', $thisStatus)->update([
                                        'status_id' => $v['status'],
                                        'hour' => $v['hour'],
                                        'minute' => $v['minute'],
                                        'type' => 1,
                                        'time' => self::getStringToTime($v['timetype'], $v['hour'], $v['minute']),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'user_id' => null,
                                        'task_description' => $v['desc'],
                                        'updated_by' => $userId
                                    ]);
                                } else {
                                    Trigger::create([
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
                                    ]);
                                }
                            }
                        }
                    }
                }
    
                if (is_array($changeStatus) && count($changeStatus) > 0) {
                    foreach ($changeStatus as $thisStatus => $array) {
                        if (isset($allStatusList[$thisStatus])) {
                            foreach ($array as $k => $v) {
                                $toBeDeleted[$thisStatus][] = $k;
                                if (Trigger::where('sequence', $k)->where('status_id', $thisStatus)->exists()) {
                                    Trigger::where('sequence', $k)->where('status_id', $thisStatus)->update([
                                        'status_id' => $v['status'],
                                        'next_status_id' => $v['nextstatus'],
                                        'hour' => $v['hour'],
                                        'minute' => $v['minute'],
                                        'type' => 2,
                                        'time' => self::getStringToTime($v['timetype'], $v['hour'], $v['minute']),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'user_id' => null,
                                        'task_description' => null,
                                        'updated_by' => $userId
                                    ]);
                                } else {
                                    Trigger::create([
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
                                    ]);
                                }
                            }
                        }
                    }
                }

                if (is_array($toBeDeleted) && count($toBeDeleted) > 0) {
                    $ids = [];
                    foreach ($toBeDeleted as $status => $sequence) {
                        $ids[] = Trigger::where('status_id', $status)->whereIn('sequence', $sequence)->select('id')->pluck('id')->toArray();
                    }

                    $ids = \Illuminate\Support\Arr::flatten($ids);
                    $ids = Trigger::whereNotIn('id', $ids)->select('id')->pluck('id')->toArray();

                    if (count($ids) > 0) {
                        AddTaskToOrderTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                        ChangeOrderStatusTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();

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
        if (SalesOrderStatus::where('id', $request->status)->doesntExist()) {
            return response()->json(['status' => false, 'container' => true]);
        }

        if (SalesOrder::where('id', $request->order)->doesntExist()) {
            return response()->json(['status' => false, 'card' => true]);
        }

        $oldStatus = SalesOrder::where('id', $request->order)->select('status')->first()->status;

        if (SalesOrder::where('id', $request->order)->update(['status' => $request->status]) && isset($oldStatus)) {

            event(new \App\Events\OrderStatusEvent('order-status-change', [
                'orderId' => $request->order,
                'orderStatus' => $request->status,
                'orderOldStatus' => $oldStatus,
                'windowId' => $request->windowId
            ]));
            
            foreach (Trigger::where('status_id', $request->status)->whereIn('action_type', [1, 3])->where('type', '1')->orderBy('sequence', 'ASC')->get() as $t) {
                AddTaskToOrderTrigger::create([
                    'order_id' => $request->order,
                    'status_id' => $request->status,
                    'added_by' => auth()->user()->id,
                    'time' => $t->time,
                    'type' => $t->time_type,
                    'main_type' => 2,
                    'description' => $t->task_description,
                    'current_status_id' => $oldStatus,
                    'trigger_id' => $t->id
                ]);
            }

            foreach (Trigger::where('status_id', $request->status)->whereIn('action_type', [1, 3])->where('type', '2')->orderBy('sequence', 'ASC')->get() as $t) {
                ChangeOrderStatusTrigger::create([
                    'order_id' => $request->order,
                    'status_id' => $t->next_status_id,
                    'added_by' => auth()->user()->id,
                    'time' => $t->time,
                    'type' => $t->time_type,
                    'current_status_id' => $request->status,
                    'executed_at' => date('Y-m-d H:i:s', strtotime($t->time)),
                    'trigger_id' => $t->id
                ]);
            }



            $tmp = AddTaskToOrderTrigger::where('executed', 0)->where('order_id', $request->order)->where('current_status_id', $oldStatus)->where('status_id', $request->status);

            if ($tmp->exists()) {
                $tmp = $tmp->first()->time ?? '+0 seconds';

                AddTaskToOrderTrigger::where('executed', 0)
                ->where('order_id', $request->order)
                ->where('current_status_id', $oldStatus)
                ->where('status_id', $request->status)
                ->update(['executed_at' => date('Y-m-d H:i:s', strtotime($tmp))]);
            }

            try {
                $cron = AddTaskToOrderTrigger::where('executed', 0)->where('order_id',$request->order)->whereNotNull('executed_at')->first();
                if ($cron !== null) {
                    (new \App\Console\Commands\TaskTrigger())->handle($request->order);
                }
            } catch (\Exception $e) {
                Helper::logger($e->getMessage());
            }

            return response()->json(['status' => true]);
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function list(Request $request) {
        if(!in_array(auth()->user()->roles->first()->id, [1,2,3])) {
            abort(403);
        }

        $statuses = SalesOrderStatus::active()->select('id', 'name', 'color')->get();

        if (!$request->ajax()) {
            $moduleName = 'Sales Order Status';
    
            return view('sales-orders-status.list', compact('moduleName', 'statuses'));
        }

        $orders = SalesOrder::with(['items', 'ostatus']);
        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();

        if (!in_array(1, $thisUserRoles)) {
            if (in_array(2, $thisUserRoles)) {
                $orders = $orders->where('seller_id', auth()->user()->id);
            } else if (in_array(3, $thisUserRoles)) {
                $driversOrder = Deliver::where('user_id', auth()->user()->id)->select('soi_id')->pluck('soi_id')->toArray();
                $driversOrder = SalesOrderItem::select('so_id')->whereIn('id', $driversOrder)->groupBy('so_id')->pluck('so_id')->toArray();

                $orders = $orders->whereIn('id', $driversOrder);
            }
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
                    $statuses = SalesOrderStatus::active()->whereIn('id', $manageSt)->select('id', 'name', 'color')->get();

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
                    return Helper::currencyFormatter($row->items->sum('amount'), true);
                })
                ->rawColumns(['order_no', 'checkbox', 'status'])
                ->with(['totalOrders' => $tempCount])
                ->make(true);
    }

    public function status (Request $request) {
        $response = false;
        $message = Helper::$errorMessage;
        $color = $text = '';

        if (!empty($request->status) && !empty($request->order)) {
            $isStatus = SalesOrderStatus::where('id', $request->status);
            if ($isStatus->exists()) {
                $color = $isStatus->first()->color;
                $text = $isStatus->first()->name;

                if (SalesOrder::where('id', $request->order)->update(['status' => $request->status])) {
                    $response = true;
                    $message = 'Status Updated successfully';
                }
            }
        }
        return response()->json(['status' => $response, 'message' => $message, 'color' => $color, 'text' => $text]);
    }

    public function statusBulkUpdate(Request $request) {
        if (SalesOrderStatus::where('id', $request->status)->doesntExist()) {
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

                $driver = SalesOrderItem::whereIn('id', $driver->select('soi_id')->pluck('soi_id')->toArray())->select('so_id')->pluck('so_id')->toArray();

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
            ManageStatus::where('status_id', $request->id)->forceDelete();
            ManageStatus::create([
                'status_id' => $request->id,
                'possible_status' => !is_null($request->mstatus) ? implode(',', array_filter($request->mstatus)) : ''
            ]);

            DB::commit();
            return response()->json(['status' => true, 'messages' => 'Status data saved successfully.']);

        } catch (\Exception $e) {
            Helper::logger($e->getMessage() . ' Line No: ', $e->getLine());
            DB::rollBack();
            return response()->json(['status' => false, 'messages' => [Helper::$errorMessage]]);
        }

    }

    public function getManagedStatus(Request $request) {

        if ($request->has('id') && !empty($request->id)) {
            $updatedStatuses = SalesOrderStatus::select('id', 'sequence')->where('id', $request->id)->first()->sequence ?? 0;
            $updatedStatuses = SalesOrderStatus::select('id', 'name')->where('sequence', '>', $updatedStatuses)->orderBy('sequence', 'ASC')->pluck('name', 'id')->toArray();
        } else {
            $updatedStatuses = SalesOrderStatus::select('id', 'name')->orderBy('sequence', 'ASC')->pluck('name', 'id')->toArray();
        }

        if (ManageStatus::where('status_id', $request->id)->exists()) {
            return response()->json(['exists' => true, 'data' => ManageStatus::where('status_id', $request->id)->first()->toArray(), 'updatedStatuses' => $updatedStatuses]);
        }

        return response()->json(['exists' => false, 'updatedStatuses' => $updatedStatuses]);
    }


    public function nextStatus(Request $request) {
        $thisStatus = SalesOrderStatus::where('id', $request->id);
        $possibleStatuses = [];
        $view = '-';
        
        if ($thisStatus->exists()) {

            $possibleStatuses = ManageStatus::where('status_id', $request->id)
            ->where('possible_status', '!=', '')
            ->first()->ps ?? [];

            $statuses = SalesOrderStatus::whereIn('id', $possibleStatuses)->select('id', 'name', 'color')->get();
            $possibleStatuses = SalesOrderStatus::whereIn('id', $possibleStatuses)->select('id', 'name')->pluck('name', 'id')->toArray();
            $cs = $thisStatus->first()->name ?? '';
            
            $view = view('sales-orders-status.status', compact('statuses', 'cs'))->render();
        }

        return response()->json(['data' => $possibleStatuses, 'view' => $view]);
    }

    public function nextStatusForTask(Request $request) {
        $thisStatus = SalesOrderStatus::where('id', $request->id);
        $possibleStatuses = [];
        $view = '-';
        
        if ($thisStatus->exists()) {

            $possibleStatuses = ManageStatus::where('status_id', $request->id)
            ->where('possible_status', '!=', '')
            ->first()->ps ?? [];

            $statuses = SalesOrderStatus::whereIn('id', $possibleStatuses)->select('id', 'name', 'color')->get();
            $possibleStatuses = SalesOrderStatus::whereIn('id', $possibleStatuses)->select('id', 'name')->pluck('name', 'id')->toArray();
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
            return response()->json(['status' => true, 'view' => view('sales-orders-status.order-details', compact('order'))->render()]);
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
        $orderId = $request->id;
        $order = ChangeOrderUser::where('order_id', $orderId)->where('status_id', $request->status);
        $selectedUser = null;

        $driver = Deliver::where('so_id', $orderId)->first()->user_id ?? null;
        $seller = SalesOrder::where('id', $orderId)->first()->seller_id ?? null;

        if ($order->exists()) {
            $selectedUser = $order->first()->user_id ?? null;
        }

        $usersList = User::with('roles')->whereIn('id', [$driver, $seller]);
        $users = "<option value='' selected> Select a Product </option>";

        foreach ($usersList->get() as $user) {
            $selected = '';

            if ($user->id == $selectedUser) {
                $selected = ' selected ';
            }

            $users .= "<option value='{$user->id}' {$selected} > {$user->name} - {$user->email} [{$user->roles->first()->name}] </option>";
        }
        

        return response()->json(['status' => true, 'users' => $users, 'current' => $selectedUser, 'total' => $usersList->count()]);
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
}