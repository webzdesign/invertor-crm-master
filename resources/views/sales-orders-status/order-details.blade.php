<div class="col-md-12">

    <div class="row">

        <h6 class="f-14 mb-1 mt-2 mb-2"><i class="fa fa-user" aria-hidden="true"></i> Customer Details</h6>

        <div class="form-group f-12">
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Customer Name</strong></label> :
                <span> {{ $order->customer_name ?? '-' }} </span>
            </div>
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Customer Phone Number</strong></label> :
                <span> {{ !empty($order->country_dial_code) ? "+{$order->country_dial_code}" : '' }} {{ $order->customer_phone ?? '-' }} </span>
            </div>
            @if(!empty(trim($order->customer_facebook)))
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Customer Facebook</strong></label> :
                <span> {{ $order->customer_facebook ?? '-' }} </span>
            </div>
            @endif
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Address Line</strong></label> :
                <span> {{ $order->customer_address_line_2 ?? '-' }} </span>
            </div>
            <div class="col-12">
                <label for="c-gr f-500 f-16 w-100 mb-2"><strong>Postal Code</strong></label> :
                <span> {{ $order->customer_postal_code }} </span>
            </div>
        </div>

        <hr>

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

        <hr>

    </div>

    <h6 class="f-14 mb-1 mt-2"><i class="fa fa-list-alt" aria-hidden="true"></i> Trigger Activity</h6>

    <div class="row">
        @forelse($order->tstatus as $key => $o)
            <div class="activity py-1">
                <p class="py-1 f-12" style="margin-bottom:0px;">
                    <span class="status-lbl f-12" style="background: {{ $o->mainstatus->color }};color:{{ Helper::generateTextColor($o->mainstatus->color) }};"> {{ $o->mainstatus->name }} </span>
                     to be triggered at {{ date('Y-m-d H:i:s', strtotime($o->executed_at)) }}
                </p>
                <div class="d-flex align-items-center justify-content-between f-12">
                    <p class="activity-date" style="margin-bottom:0px;"> {{ date('d F, Y', strtotime($o->created_at)) }} </p>
                    @if(is_null($o->updated_by))
                        <strong style="text-transform: uppercase;color:green;"> DONE </strong>
                    @else
                        <strong style="text-transform: uppercase;color:grey;"> CHANGED </strong>
                    @endif
                </div>
            </div>
        @empty
        <div class="activity py-2 border-bottom">
            No Activity to Show
        </div>
        @endforelse
    </div>
</div>