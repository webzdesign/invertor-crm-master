<?php

namespace App\Console\Commands;

use App\Models\{AddTaskToOrderTrigger, CallAddTaskToCallHistoryTrigger, CallHistory};
use App\Helpers\Helper;
use Illuminate\Console\Command;

class CallTaskTrigger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call-task:trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'When call is moved then add task';

    public function handle($triggers = [], $executor = null) {

        $iterable = CallAddTaskToCallHistoryTrigger::query()
            ->whereHas('trigger', function ($builder) {
                $builder->where('id', '>', 0);
            })
            ->where('executed', 0)
            ->where(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(executed_at, '%Y-%m-%d %H:%i')"), '<=', date('Y-m-d H:i:s'));

        if (!empty($triggers)) {
            $iterable = $iterable->whereIn('id', $triggers);
        }


        foreach ($iterable->get() as $call) {
            $thisCall = CallAddTaskToCallHistoryTrigger::findOrFail($call->id);

            if (isset($thisCall->call_id)) {
                $callHistory = CallHistory::findOrFail($thisCall->call_id ?? null);

                event(new \App\Events\CallStatusEvent('add-task-to-call-history', [
                    'uid' => $callHistory->uid,
                    'client' => Helper::getNumberFormated($callHistory->client)
                ]));

                \App\Models\TriggerLog::create([
                    'trigger_id' => $call->trigger_id,
                    'cron_id' => $call->id,
                    'order_id' => $call->order_id,
                    'watcher_id' => $executor,
                    'next_status_id' => $call->status_id,
                    'current_status_id' => $call->current_status_id,
                    'description' => $call->description,
                    'type' => 1,
                    'time_type' => $call->time_type,
                    'main_type' => $call->main_type,
                    'hour' => $call->hour,
                    'minute' => $call->minute,
                    'time' => $call->time,
                    'executed_at' => $call->executed_at,
                    'executed' => 1
                ]);

                $thisCall->executed = true;
                $thisCall->save();
            }
        }
    }
}
