<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\CallTaskStatus; // DONE
use App\Models\Deliver;
use App\Models\CallHistory; // DONE
use App\Models\Role;
use App\Models\Setting;
use App\Models\CallAddTaskToCallHistoryTrigger; // DONE
use App\Models\CallChangeCallHistoryStatusTrigger; // DONE
use App\Models\CallChangeCallHistoryUser; // DONE
use App\Models\CallTrigger; // DONE
use App\Models\CallTriggerLog; // DONE
use App\Models\TwilloMessageNotification;
use App\Models\TwilloTemplate;
use Illuminate\Support\Facades\Http;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CallTaskStatusController extends Controller
{
    public function index(Request $request) {

        $moduleName = 'Tasks';
        $statuses = CallTaskStatus::sequence()
            ->custom()
            ->orderBy('sequence', 'ASC')
            ->get();

        $tasks = [];
        foreach ($statuses as $status) {
            $query = CallHistory::query()
                ->where('status_id', $status->id);

            if (User::isAdmin()) {

            } else {
                $query->where("assigned_user_id", auth()->user()->id);
            }


            $callHistories = $query->orderBy('start', 'DESC')
                ->get()
                ->toArray();
            $tasks[$status->id] = $callHistories;
        }

        return view('task-status.index', compact('moduleName', 'statuses', 'tasks'));
    }

    public function edit() {

        $moduleName = 'Tasks';
        $statuses = CallTaskStatus::sequence()->custom()->orderBy('sequence', 'ASC')->get();
        $statusesOnlyForShow = CallTaskStatus::sequence()->orderBy('sequence', 'ASC')->get();
        $s = CallTaskStatus::sequence()->custom()->select('id', 'name')->orderBy('sequence', 'ASC')->pluck('name', 'id')->toArray();

        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];
        $roles = Role::active()->whereIn('id', [1, 2, 3])->pluck('name', 'id')->toArray();
        $maxTriggers = Setting::first()->triggers_per_status ?? 10;

        $allocate_notificationorders = TwilloMessageNotification::where('status_id',1)->where('responsibale_user_type',1)->first();
        $allocate_notification = $accept_notification = null;
        if(!empty($allocate_notificationorders)) {
            $allocate_notification = $allocate_notificationorders->template_id;
        }
        $accept_notificationorders = TwilloMessageNotification::where('status_id',1)->where('responsibale_user_type',2)->first();
        if(!empty($accept_notificationorders)) {
            $accept_notification = $accept_notificationorders->template_id;
        }
        $twillotemplate = TwilloTemplate::where('templatestatus','approved')->get(['templatename','contentsid']);


        return view('task-status.edit', compact('moduleName', 'statuses', 'colours', 'roles', 's', 'maxTriggers', 'statusesOnlyForShow','allocate_notification','accept_notification','twillotemplate'));
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
                return response()->json([
                    'status' => false,
                    'message' => Helper::$errorMessage
                ]);
            }

            $currentStatusId = CallHistory::where('id', $orderId)->first()->status_id ?? 0;

            CallAddTaskToCallHistoryTrigger::create([
                'order_id' => $orderId,
                'status_id' => $orderStatus,
                'added_by' => auth()->user()->id,
                'time' => $additionalTime,
                'type' => $time,
                'main_type' => 2,
                'description' => $desc,
                'current_status_id' => $currentStatusId
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Task data saved successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => Helper::$errorMessage
            ]);
        }
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

        if (is_null($sequences) || is_null($names)) {
            return redirect()->route('task-status.edit')->with('error', 'Atleast add a status to save.');
        }

        if (count($sequences) != count($names)) {
            return redirect()->route('task-status.edit')->with('error', 'Atleast add a status to save.');
        }

        $userId = auth()->user()->id;
        $allStatusList = CallTaskStatus::custom()->select('id', 'name')->pluck('name', 'id')->toArray();
        $toNotBeDeleted = [];

        DB::beginTransaction();

        try {
            if (count($sequences) > 0) {
                foreach ($sequences as $key => $value) {
                    if (!is_null($value)) {
                        CallTaskStatus::custom()->where('id', $value)->update([
                            'name' => strtoupper($names[$key]),
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                    } else {
                        CallTaskStatus::custom()->create([
                            'name' => strtoupper($names[$key]),
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                        $ids = CallTaskStatus::pluck('id')->toArray();
                        if(!empty($ids)) {
                            Role::where('slug','admin')->update(['filter_status'=>implode(',',$ids)]);
                        }
                    }
                }

                if (is_array($tasks) && count($tasks) > 0) {
                    foreach ($tasks as $thisStatus => $array) {
                        if (isset($allStatusList[$thisStatus])) {
                            foreach ($array as $k => $v) {
                                if (isset($v['edit_id']) && $v['edit_id'] > 0) {
                                    CallTrigger::where('id', $v['edit_id'])->update([
                                        'status_id' => $v['status'],
                                        'hour' => $v['hour'],
                                        'minute' => $v['minute'],
                                        'sequence' => $k,
                                        'type' => 1,
                                        'time' => SalesOrderStatusController::getStringToTime($v['timetype'], $v['hour'], $v['minute']),
                                        'action_type' => $v['maintype'],
                                        'time_type' => $v['timetype'],
                                        'user_id' => null,
                                        'task_description' => $v['desc'],
                                        'updated_by' => $userId
                                    ]);
                                    $toNotBeDeleted[] = $v['edit_id'];
                                }
                                else {
                                    $toNotBeDeleted[] = CallTrigger::create([
                                        'status_id' => $v['status'],
                                        'sequence' => $k,
                                        'hour' => $v['hour'] ?? null,
                                        'minute' => $v['minute'] ?? null,
                                        'type' => 1,
                                        'time' => SalesOrderStatusController::getStringToTime($v['timetype'], ($v['hour'] ?? null), ($v['minute'] ?? null)),
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

                /*
                 * $disabled = true;
                if ($disabled == false) {
                    if (is_array($changeStatus) && count($changeStatus) > 0) {
                        foreach ($changeStatus as $thisStatus => $array) {
                            if (isset($allStatusList[$thisStatus])) {
                                foreach ($array as $k => $v) {
                                    if (isset($v['edit_id']) && $v['edit_id'] > 0) {
                                        CallTrigger::where('id', $v['edit_id'])->update([
                                            'status_id' => $v['status'],
                                            'next_status_id' => $v['nextstatus'],
                                            'hour' => $v['hour'],
                                            'minute' => $v['minute'],
                                            'sequence' => $k,
                                            'type' => 2,
                                            'time' => SalesOrderStatusController::getStringToTime($v['timetype'], $v['hour'], $v['minute']),
                                            'action_type' => $v['maintype'],
                                            'time_type' => $v['timetype'],
                                            'user_id' => null,
                                            'task_description' => null,
                                            'updated_by' => $userId
                                        ]);
                                        $toNotBeDeleted[] = $v['edit_id'];
                                    } else {
                                        $toNotBeDeleted[] = CallTrigger::create([
                                            'status_id' => $v['status'],
                                            'next_status_id' => $v['nextstatus'],
                                            'sequence' => $k,
                                            'hour' => $v['hour'] ?? null,
                                            'minute' => $v['minute'] ?? null,
                                            'type' => 2,
                                            'time' => SalesOrderStatusController::getStringToTime($v['timetype'], ($v['hour'] ?? null), ($v['minute'] ?? null)),
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
                            $ids = Trigger::whereNotIn('id', $toNotBeDeleted)->where('type', '!=', 4)->select('id')->pluck('id')->toArray();
                            $ids = Trigger::whereIn('id', $ids)->select('id')->pluck('id')->toArray();

                            if (count($ids) > 0) {
                                AddTaskToOrderTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                                ChangeOrderStatusTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                                ChangeOrderUser::where('executed', 0)->whereIn('trigger_id', $ids)->delete();

                                Trigger::whereIn('id', $ids)->delete();
                            }
                        } else if (empty($toNotBeDeleted)) {
                            $ids = Trigger::select('id')->where('type', '!=', 4)->pluck('id')->toArray();
                            AddTaskToOrderTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                            ChangeOrderStatusTrigger::where('executed', 0)->whereIn('trigger_id', $ids)->delete();
                            ChangeOrderUser::where('executed', 0)->whereIn('trigger_id', $ids)->delete();

                            Trigger::whereIn('id', $ids)->where('type', '!=', 4)->delete();
                        }
                    }
                }*/

                DB::commit();
                return redirect()->route('task-status.index')->with('success', 'Status updated successfully.');
            }

        } catch (\Exception $e) {
            Helper::logger($e->getMessage() . ' ' . $e->getLine());
            DB::rollBack();
            return redirect()->route('task-status.edit')->with('error', Helper::$errorMessage);
        }

        DB::rollBack();
        return redirect()->route('task-status.edit')->with('error', 'Add atleast a card to save.');
    }

    public function deleteStatus(Request $request) {
        $status = CallTaskStatus::custom()->where('id', $request->id);

        if ($status->exists()) {

            DB::beginTransaction();
            $status = $status->first()->id ?? null;

            try {
                CallAddTaskToCallHistoryTrigger::where('status_id', $status)->delete();
                ChangeOrderStatusTrigger::where('status_id', $status)->delete();
                ChangeOrderUser::where('status_id', $status)->delete();
                CallTrigger::where('status_id', $status)->whereIn('type', [1, 3])->delete();
                CallTrigger::where('next_status_id', $status)->where('type', [2])->delete();
                ManageStatus::where('status_id', $status)->delete();
                CallTaskStatus::custom()->where('id', $status)->delete();

                $ids = CallTaskStatus::pluck('id')->toArray();
                if(!empty($ids)) {
                    Role::where('slug','admin')->update(['filter_status'=>implode(',',$ids)]);
                }

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
                    $oldStatus = SalesOrder::where('id', $soId)
                        ->select('status')
                        ->first()
                        ->status;

                    $newStatus = Trigger::where('status_id', 1)
                            ->where('type', 2)
                            ->whereIn('action_type', [1, 3])
                            ->first()->next_status_id ?? 0;

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

                        (new \App\Console\Commands\CallTrigger)->handle($y);

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

        return response()->json([
            'status' => false,
            'message' => "Status not found."
        ]);
    }

    public function sequence(Request $request) {
        $callId = $request->order;
        $toStatus = $request->status;

        if (CallTaskStatus::custom()->where('id', $toStatus)->doesntExist()) {
            return response()->json([
                'status' => false,
                'container' => true
            ]);
        }

        if (CallHistory::where('id', $callId)->doesntExist()) {
            return response()->json([
                'status' => false,
                'card' => true
            ]);
        }

        $oldStatus = CallHistory::where('id', $callId)->select('status_id')->first()->status_id;

        if (CallHistory::where('id', $callId)->update(['status_id' => $toStatus]) && isset($oldStatus)) {

            CallAddTaskToCallHistoryTrigger::where('call_id', $callId)->where('status_id', '!=',$toStatus)->where('executed', 0)->delete();
            CallChangeCallHistoryUser::where('call_id', $callId)->where('status_id', '!=', $toStatus)->where('executed', 0)->delete();
            CallChangeCallHistoryStatusTrigger::where('call_id', $callId)->where('status_id', '!=', $toStatus)->where('executed', 0)->delete();

            $call = CallHistory::where('id', $callId)->first();

            event(new \App\Events\CallStatusEvent('call-history-status-change', [
                'call_id' => $callId,
                'call_status' => $toStatus,
                'call_old_status' => $oldStatus,
                'window_id' => $request->windowId,
                'users' => [
                    Deliver::where('so_id', $call->id)->where('status', 1)->first()->user_id ?? null,
                    $call->added_by
                ]
            ]));

            $fromStatus = CallTaskStatus::custom()->withTrashed()->where('id', $oldStatus)->first();
            $toStatus = CallTaskStatus::custom()->withTrashed()->where('id', $toStatus)->first();

            CallTriggerLog::create([
                'trigger_id' => 0,
                'cron_id' => $call->id,
                'call_id' => $call->id,
                'watcher_id' => auth()->user()->id,
                'next_status_id' => $toStatus->id,
                'current_status_id' => $oldStatus,
                'type' => 2,
                'time_type' => $call->time_type,
                'main_type' => $call->main_type,
                'hour' => $call->hour,
                'minute' => $call->minute,
                'time' => $call->time,
                'executed_at' => $call->executed_at,
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

            /** TRIGGERS **/

            /** TASKS **/
            $y = [];
            try {
                $triggers = CallTrigger::where('type', 1)
                    ->where('status_id', $toStatus->id)
                    ->whereIn('action_type', [1, 3]);
                
                if ($triggers->count() > 0) {
                    foreach ($triggers->get() as $t) {
                        $currentTime1 = date('Y-m-d H:i:s', strtotime("{$t->time}"));

                        $record = CallAddTaskToCallHistoryTrigger::create([
                            'call_id' => $callId,
                            'status_id' => $toStatus->id,
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

                (new \App\Console\Commands\CallTaskTrigger())->handle($y);

            } catch (\Exception $e) {
                Helper::logger($e->getMessage());
            }

            /** TASKS **/
            return response()->json(['status' => true]);
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function callDetailInBoard(Request $request) {

        $call = CallHistory::query()
            ->with(['tstatus', 'ostatus', 'userchanges'])
            ->where('id', $request->id)
            ->first();

        if ($call) {

            $logs = \App\Models\CallTriggerLog::query()
                ->with([
                    'watcher' => fn ($builder) => ($builder->withTrashed()),
                    'user' => fn ($builder) => ($builder->withTrashed()),
                    'assigneddriver' => fn ($builder) => ($builder->withTrashed())
                ])
                ->where('call_id', $call->id)
                ->orderBy('id', 'DESC')
                ->get();

            return response()->json([
                'status' => true,
                'view' => view('task-status.call-details', compact('call', 'logs'))->render()
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => Helper::$errorMessage
        ]);
    }

    public function saveDescription(Request $request) {
        if(CallAddTaskToCallHistoryTrigger::where('id', $request->id)->exists()) {
            CallAddTaskToCallHistoryTrigger::where('id', $request->id)->update([
                'completed_description' => $request->text,
                'completed' => true
            ]);
            return response()->json(['status' => true, 'message' => 'Call task completed successfully.']);
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }
    public function removeCallTask(Request $request) {
        $task = CallAddTaskToCallHistoryTrigger::where('id', $request->id);

        if ($task->exists()) {
            return response()->json(['status' => $task->delete(), 'message' => 'Task deleted successfully.', 'count' => CallAddTaskToCallHistoryTrigger::where('call_id', $request->order)->where('executed', 1)->count()]);
        }

        return response()->json(['status' => false, 'message' => 'Call task not found.']);
    }
}
