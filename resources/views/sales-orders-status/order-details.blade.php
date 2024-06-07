<div class="col-md-12">

    <div class="">

        <h6 class="f-14 mb-1 mt-2 mb-2"><i class="fa fa-user" aria-hidden="true"></i> Customer Details</h6>

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

        <h6 class="f-14 mb-1 mt-2 mb-2"><i class="fa fa-tag" aria-hidden="true"></i> Order Details</h6>

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

    <div class="d-flex align-items-center justify-content-between">
        <h6 class="f-14 mb-4 mt-2"><i class="fa fa-list-alt" aria-hidden="true"></i> Trigger Activity</h6>
        @if($order->tstatus->count() > 3)
        <button class="show-less-more-btn small-btn btn-primary f-500 f-12" id="toggle-status-trigger-list"> Show All </button>
        @endif
    </div>

    <div class="status-trigger-activity-row">
        @forelse($order->tstatus as $key => $o)
            <div class="activity py-1 actvt @if(in_array($loop->iteration, [1,2,3])) show-first @else d-none @endif">
                <p class="pb-1 f-12" style="margin-bottom:0px;">
                    <strong>{{ date('d-m-Y H:i:s', strtotime($o->created_at)) }}</strong> :
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
                            <strong style="text-transform: uppercase;color:#009688;" title="to be triggered"> PENDING </strong>
                        @else
                            <strong style="text-transform: uppercase;color:green;" title="Done"> DONE </strong>
                        @endif
                    @else
                        <strong style="text-transform: uppercase;color:#3d0000;" title="Status changed before triggered"> CHANGED </strong>
                    @endif ]
                </p>
            </div>
        @empty
        <div class="activity py-2 f-13 border-bottom">
            No Activity to Show
        </div>
        @endforelse
    </div>

    <hr>

    <div class="d-flex align-items-center justify-content-between">
        <h6 class="f-14 mb-4 mt-2"><i class="fa fa-list-alt" aria-hidden="true"></i> Task Activity</h6>
        @if($order->task->count() > 3)
        <button class="show-less-more-btn small-btn btn-primary f-500 f-12" id="toggle-task-trigger-list"> Show All </button>
        @endif
    </div>

    <div class="task-trigger-activity-row">
        @forelse($order->task as $key => $o)
            <div class="activity mb-3 px-2 py-1 border actvt-at @if(in_array($loop->iteration, [1,2,3])) show-first-at @else d-none @endif">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="w-100 pe-3">
                        <span class="f-15"> <strong>{{ $o->description ?? 'Task' }}</strong> </span>
                        <p class="mb-0 f-14">
                            <strong>{{ date('d-m-Y H:i:s', strtotime($o->created_at)) }}</strong> : 
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
                                    <strong style="text-transform: uppercase;color:#009688;" title="to be triggered"> PENDING </strong>
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
        <div class="activity py-2 f-13 border-bottom">
            No Activity to Show
        </div>
        @endforelse
    </div>

    {{-- <hr> --}}

    {{-- <div class="d-flex align-items-center justify-content-between">
        <h6 class="f-14 mb-4 mt-2"><i class="fa fa-list-alt" aria-hidden="true"></i> User changes Activity</h6>
        @if($order->userchanges->count() > 3)
        <button class="show-less-more-btn small-btn btn-primary f-500 f-12" id="toggle-user-changes-trigger-list"> Show All </button>
        @endif
    </div>

    <div class="status-trigger-activity-row">
        @forelse($order->userchanges as $key => $o)
            <div class="activity py-1 actvt-cu @if(in_array($loop->iteration, [1,2,3])) show-first-cu @else d-none @endif">
                <p class="pb-1 f-12" style="margin-bottom:0px;">
                    <strong>{{ date('d-m-Y H:i:s', strtotime($o->created_at)) }}</strong> : 
                    order assigned to 
                    <strong>{{ $o->user->roles->first()->name ?? 'user' }}</strong>
                    <a target="_blank" href="{{ route('users.view', ($o->user->encid ?? '')) }}"> {{ $o->user->name ?? '' }} </a>
                    when order in 
                    <span class="status-lbl f-12" style="background: {{ $o->mainstatus->color }};color:{{ Helper::generateTextColor($o->mainstatus->color) }};text-transform:uppercase;"> {{ $o->mainstatus->name }} </span>
                    [ @if(empty($o->deleted_at))
                        <strong style="text-transform: uppercase;color:#009688;" title="to be triggered"> CURRENT </strong>
                    @else
                        <strong style="text-transform: uppercase;color:#3d0000;" title="assigned in past"> CHANGED </strong>
                    @endif ]
                </p>
            </div>
        @empty
        <div class="activity py-2 f-13 border-bottom">
            No Activity to Show
        </div>
        @endforelse
    </div> --}}

</div>