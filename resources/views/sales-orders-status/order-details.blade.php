<div class="col-md-12">

    <div class="">

        <h6 class="f-14 mb-1 mt-2 mb-2"><i class="fa fa-user" aria-hidden="true"></i> Customer details</h6>

        <div class="form-group f-12">
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Customer Name</strong></label> :
                <span> {{ $order->customer_name ?? '-' }} </span>
            </div>
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Customer Phone Number</strong></label> :
                <span> {{ $order->customer_phone ?? '-' }} </span>
            </div>
            @if(!empty(trim($order->customer_facebook)))
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Customer Facebook</strong></label> :
                <span> {{ $order->customer_facebook ?? '-' }} </span>
            </div>
            @endif
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Address Line</strong></label> :
                <span> {{ $order->customer_address_line_1 ?? '-' }} </span>
            </div>
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Postal Code</strong></label> :
                <span> {{ $order->customer_postal_code }} </span>
            </div>
        </div>

        
        <hr class="border-secondary">

        <h6 class="f-14 mb-1 mt-2 mb-2"><i class="fa fa-tag" aria-hidden="true"></i> Order details</h6>

        <div class="form-group f-12">
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Order Amount</strong></label> :
                <span> {{ Helper::currencyFormatter($order->items->sum('amount'), true) }} </span>
            </div>
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Date</strong></label> :
                <span> {{ date('d-m-Y H:i', strtotime($order->date)) }} </span>
            </div>
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Delivery Date</strong></label> :
                <span> {{ date('d-m-Y H:i', strtotime($order->delivery_date)) }} </span>
            </div>
            
            @php
                $driver = isset($order->items->first()->driver->user->name) ? ($order->items->first()->driver->user->name . ' - (' . $order->items->first()->driver->user->email . ')') : 'Not Assigned yet.';
            @endphp

            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Driver</strong></label> :
                <span> {{ $driver }} </span>
            </div>
        </div>
        <hr class="border-secondary">
    </div>

    {{-- History --}}
    
    <div class="d-flex align-items-center justify-content-between">
        <h6 class="f-14 mb-4 mt-2"><i class="fa fa-clock-o" aria-hidden="true"></i> Order trigger history</h6>
        @if(count($logs) > 3)
        <button class="show-less-more-btn small-btn btn-primary f-500 f-12" id="toggle-history"> Show All </button>
        @endif
    </div>

    <div class="order-history">
        @forelse($logs as $key => $l)
            <div class="activity py-1 hist @if(in_array($loop->iteration, [1,2,3])) show-first-history @else d-none @endif">
                <p class="f-12" style="margin-bottom:0px;">
                    <strong> {{ date('d-m-Y H:i', strtotime($l->created_at)) }} @if(!empty($l->watcher_id)) {{ $l->watcher->name }} @else Robot @endif </strong> :
                    @if($l->type == 1)
                        added a task [ <strong>{{ $l->description }}</strong> ]
                    @elseif($l->type == 2)
                        moved to
                        <span class="status-lbl f-12" style="background: {{ $l->to_status->color }};color:{{ Helper::generateTextColor($l->to_status->color) }};text-transform:uppercase;"> {{ $l->to_status->name }} </span>
                        from
                        <span class="status-lbl f-12" style="background: {{ $l->from_status->color }};color:{{ Helper::generateTextColor($l->from_status->color) }};text-transform:uppercase;"> {{ $l->from_status->name }} </span>
                    @elseif($l->type == 3)
                        assigned order to it's seller
                    @endif
                </p>
            </div>
        @empty
        <div class="activity f-13">
            History not available
        </div>
        @endforelse
    </div>

    <hr>
    {{-- History --}}

    @php
        $allChangeOrderStatusLogs = \App\Models\ChangeOrderStatusTrigger::with(['oldstatus' => fn ($builder) => $builder->withTrashed(), 'mainstatus' => fn ($builder) => $builder->withTrashed()])->where('order_id', $order->id)->withTrashed()->orderBy('id', 'DESC')->get();
    @endphp
    <div class="d-flex align-items-center justify-content-between">
        <h6 class="f-14 mb-4 mt-2"><i class="fa fa-clock-o" aria-hidden="true"></i> Status change triggers</h6>
        @if(count($allChangeOrderStatusLogs) > 3)
        <button class="show-less-more-btn small-btn btn-primary f-500 f-12" id="toggle-status-trigger-list"> Show All </button>
        @endif
    </div>

    <div class="status-trigger-activity-row">
        @forelse($allChangeOrderStatusLogs as $key => $o)
            <div class="activity py-1 actvt @if(in_array($loop->iteration, [1,2,3])) show-first @else d-none @endif">
                <p class="pb-1 f-12" style="margin-bottom:0px;">
                    <strong>{{ date('d-m-Y H:i', strtotime($o->created_at)) }}</strong> :
                    from
                    <span class="status-lbl f-12" style="background: {{ $o->oldstatus->color }};color:{{ Helper::generateTextColor($o->oldstatus->color) }};text-transform:uppercase;"> {{ $o->oldstatus->name }} </span>
                    to be triggered to
                    <span class="status-lbl f-12" style="background: {{ $o->mainstatus->color }};color:{{ Helper::generateTextColor($o->mainstatus->color) }};text-transform:uppercase;"> {{ $o->mainstatus->name }} </span>
                    @php
                        $time = str_replace('+', '', $o->time);

                        if (trim($time) == '0 seconds') {
                            $time = 'Immediately';
                        } else {
                            $time = " after {$time}";
                        }
                    @endphp
                    {{ $time }}

                    [ @if(is_null($o->updated_by))
                        @if(!$o->executed)
                            @if(!$o->skipped)
                            <strong style="text-transform: uppercase;color:#009688;" title="to be triggered"> PENDING </strong>
                            @else
                            <strong style="text-transform: uppercase;color:#963900;" title="skipped"> SKIPPED </strong>
                            @endif
                        @else
                            <strong style="text-transform: uppercase;color:green;" title="Done"> DONE </strong>
                        @endif
                    @else
                        <strong style="text-transform: uppercase;color:#3d0000;" title="Status changed before triggered"> CHANGED </strong>
                    @endif ]
                </p>
            </div>
        @empty
        <div class="activity f-13">
            No triggers set for this order
        </div>
        @endforelse
    </div>

    <hr>

    @php
        $allAddTaskToOrderTrigger = \App\Models\AddTaskToOrderTrigger::with(['mainstatus' => fn ($builder) => $builder->withTrashed()])->where('order_id', $order->id)->withTrashed()->orderBy('id', 'DESC')->get();
    @endphp

    <div class="d-flex align-items-center justify-content-between">
        <h6 class="f-14 mb-4 mt-2"><i class="fa fa-clock-o" aria-hidden="true"></i> Tasks triggers </h6>
        @if(count($allAddTaskToOrderTrigger) > 3)
        <button class="show-less-more-btn small-btn btn-primary f-500 f-12" id="toggle-task-trigger-list"> Show All </button>
        @endif
    </div>

    <div class="task-trigger-activity-row">
        @forelse($allAddTaskToOrderTrigger as $key => $o)
            <div class="activity mb-3 px-2 py-1 border actvt-at @if(in_array($loop->iteration, [1,2,3])) show-first-at @else d-none @endif">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="w-100 pe-3">
                        <span class="f-15"> <strong>{{ $o->description ?? 'Task' }}</strong> </span>
                        <p class="mb-0 f-14">
                            <strong>{{ date('d-m-Y H:i', strtotime($o->created_at)) }}</strong> : 
                            Task added when
                            <strong>
                            @if($o->main_type == '1')
                                created order with
                            @elseif($o->main_type == '2')
                                order moved to  
                            @elseif($o->main_type == '3')
                                created or moved order in
                            @endif
                            </strong>
                            <span class="status-lbl f-12" style="background: {{ $o->mainstatus->color }};color:{{ Helper::generateTextColor($o->mainstatus->color) }};text-transform:uppercase;"> {{ $o->mainstatus->name }} </span>
                            @php
                                $time = str_replace('+', '', $o->time);
        
                                if (trim($time) == '0 seconds') {
                                    $time = 'Immediately';
                                } else {
                                    $time = " after {$time}";
                                }
                            @endphp
                             {{ $time }}

                             [ @if(is_null($o->updated_by))
                                @if(!$o->executed)
                                    @if(!$o->skipped)
                                    <strong style="text-transform: uppercase;color:#009688;" title="to be triggered"> PENDING </strong>
                                    @else
                                    <strong style="text-transform: uppercase;color:#963900;" title="skipped"> SKIPPED </strong>
                                    @endif
                                @else
                                    <strong style="text-transform: uppercase;color:green;" title="Done"> DONE </strong>
                                @endif
                            @else
                                <strong style="text-transform: uppercase;color:#3d0000;" title="Status changed before triggered"> CHANGED </strong>
                            @endif ]
                        </p>

                        <div class="w-100 f-12 outline-none mb-3 completion-content" style="word-break: break-word;">{{ $o->completed_description }}</div>
                        <span class="text-danger this-error f-12 mb-2"></span>
                    </div>
                    <div class="nowrap btn-opts">
                        <button class="btn btn-outline-success btn-sm py-0 px-1 edit-task" data-tid="{{ $o->id }}" data-oid="{{ $o->order_id }}">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                            Edit
                        </button>
                        <button class="btn btn-outline-danger btn-sm py-0 px-1 remove-task" data-tid="{{ $o->id }}" data-oid="{{ $o->order_id }}">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                            Delete
                        </button>
                    </div>
                </div>
                <div class="mb-2 d-none">
                    <button type="button" class="btn-default f-500 f-12 small-btn hide-complete-task-textarea"> Cancel </button>
                    <button type="button" class="btn-primary f-500 f-12 small-btn save-complete-task-textarea" data-taskid="{{ $o->id }}"> Done </button>
                </div>

            </div>
        @empty
        <div class="activity f-13">
            No triggers set for this order
        </div>
        @endforelse
    </div>

    <hr>

    @php
        $allChangeOrderUser = \App\Models\ChangeOrderUser::with(['mainstatus' => fn ($builder) => $builder->withTrashed()])->where('order_id', $order->id)->withTrashed()->orderBy('id', 'DESC')->get();
    @endphp

    <div class="d-flex align-items-center justify-content-between">
        <h6 class="f-14 mb-4 mt-2"><i class="fa fa-clock-o" aria-hidden="true"></i> User assignation triggers </h6>
        @if(count($allChangeOrderUser) > 3)
        <button class="show-less-more-btn small-btn btn-primary f-500 f-12" id="toggle-change-user-trigger-list"> Show All </button>
        @endif
    </div>

    <div class="change-user-trigger-activity-row">
        @forelse($allChangeOrderUser as $key => $o)
            <div class="activity py-1 actvt-cu @if(in_array($loop->iteration, [1,2,3])) show-first-cu @else d-none @endif">
                <p class="pb-1 f-12" style="margin-bottom:0px;">
                    <strong>{{ date('d-m-Y H:i', strtotime($o->created_at)) }}</strong> :
                    order @if(!$o->executed) will be @else was @endif assigned to it's when order 
                    <strong>
                    @if($o->main_type == '1')
                        moved to 
                    @elseif($o->main_type == '2')
                        created into
                    @else
                        moved or created into
                    @endif
                    </strong>
                    <span class="status-lbl f-12" style="background: {{ $o->mainstatus->color }};color:{{ Helper::generateTextColor($o->mainstatus->color) }};text-transform:uppercase;"> {{ $o->mainstatus->name }} </span>
                    @php
                        $time = str_replace('+', '', $o->time);

                        if (trim($time) == '0 seconds') {
                            $time = 'Immediately';
                        } else {
                            $time = " after {$time}";
                        }
                    @endphp
                    {{ $time }}

                    [ @if(is_null($o->updated_by))
                        @if(!$o->executed)
                            @if(!$o->skipped)
                            <strong style="text-transform: uppercase;color:#009688;" title="to be triggered"> PENDING </strong>
                            @else
                            <strong style="text-transform: uppercase;color:#963900;" title="skipped"> SKIPPED </strong>
                            @endif
                        @else
                            <strong style="text-transform: uppercase;color:green;" title="Done"> DONE </strong>
                        @endif
                    @else
                        <strong style="text-transform: uppercase;color:#3d0000;" title="Status changed before triggered"> CHANGED </strong>
                    @endif ]
                </p>
            </div>
        @empty
        <div class="activity f-13">
            No triggers set for this order
        </div>
        @endforelse
    </div>

</div>