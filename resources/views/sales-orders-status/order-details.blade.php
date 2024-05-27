<div class="col-md-12">
    <h6>Order Status Trigger Activity</h6>

    <div class="row">
        @forelse($order->tstatus as $key => $o)
            <div class="activity py-2 border-bottom">
                <strong>{{ $o->adder->name ?? '-' }}</strong> 
                <p class="py-2" style="margin-bottom:0px;">
                    <span class="status-lbl" style="background: {{ $o->mainstatus->color }};color:{{ Helper::generateTextColor($o->mainstatus->color) }};"> {{ $o->mainstatus->name }} </span>
                     to be triggered at {{ date('Y-m-d H:i:s', strtotime($o->executed_at)) }}
                </p>
                <p class="activity-date" style="margin-bottom:0px;"> {{ date('d F, Y', strtotime($o->created_at)) }} </p>
            </div>
        @empty
        <div class="activity py-2 border-bottom">
            No Activity to Show
        </div>
        @endforelse
    </div>
</div>