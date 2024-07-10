<div class="row">

    <div class="col-12 f-14">
        <label> SELLER NAME : </label>
        <strong>
            {{ $data->user->name ?? '' }}
        </strong>
    </div>

    <div class="col-12 f-14">
        <label> COMMISSION AMOUNT : </label>
        <strong>
            {{ Helper::currency($data->amount) }}
        </strong>
    </div>

    <div class="col-12 f-14">
        <label> FIRST NAME (BANK) : </label>
        <strong>
            {{ $data->bank->name ?? '-' }}
        </strong>
    </div>

    <div class="col-12 f-14">
        <label> SURNAME (BANK) : </label>
        <strong>
            {{ $data->bank->surname ?? '-' }}
        </strong>
    </div>

    <div class="col-12 f-14">
        <label> IBAN NUMBER (BANK) : </label>
        <strong>
            {{ $data->bank->iban_number ?? '' }}
        </strong>
    </div>

    <div class="col-12 f-14">
        <label> REQUEST STATUS : </label>
        <strong>
            @if($data->status == 1)
            <span class="text-success"> Accepted </span>
            @elseif($data->status == 2)
            <span class="text-danger"> Rejected </span>
            @else
            <span class="text-secondary"> Pending </span>
            @endif
        </strong>
    </div>

    <div class="col-12 f-14">
        <label> DATE : </label>
        <strong>
            {{ date('d-m-Y', strtotime($data->created_at)) }}
        </strong>
    </div>

    <div class="col-12 f-14">
        <label> ORDES FROM DATE : </label>
        <strong>
            {{ date('d-m-Y', strtotime($data->from)) }}
        </strong>
    </div>

    <div class="col-12 f-14">
        <label> ORDES TO DATE : </label>
        <strong>
            {{ date('d-m-Y', strtotime($data->to)) }}
        </strong>
    </div>

    @php
    $transactions = \App\Models\Transaction::with('order')->whereIn('so_id', json_decode($data->orders, true))
        ->where('amount_type', 3)
        ->get();
    @endphp

<table class="table" id="withdrawable-orders">
    <thead>
        <tr>
            <th>ORDER NUMBER</th>
            <th>COMMISSION AMOUNT</th>
            <th>ORDER AMOUNT</th>
        </tr>
    </thead>
    <tbody>
        @php $orderAmt = $commissionAmount = 0; @endphp
        @foreach ($transactions as $item)
            <tr>
                <td>
                    <a target="_blank" href="{{ route('sales-orders.view', [encrypt($item->order->id)]) }} "> {{ $item->order->order_no }} </a>
                    <input type="hidden" name="orders[]" value="{{ $item->so_id }}">
                    <input type="hidden" name="transactions[]" value="{{ $item->id }}">
                    <input type="hidden" name="amount[]" value="{{ $item->amount }}">

                    @if($loop->first)
                        <input type="hidden" name="from_date" value="{{ date('Y-m-d H:i:s', strtotime($item->created_at)) }}">
                    @elseif($loop->last)
                        <input type="hidden" name="to_date" value="{{ date('Y-m-d H:i:s', strtotime($item->created_at)) }}">
                    @endif
                    @php 
                    $orderAmt += $item->amount;
                    $commissionAmount += $item->order->sold_amount;
                    @endphp
                </td>
                <td>{{ Helper::currency($item->amount) }}</td>
                <td>{{ Helper::currency($item->order->sold_amount) }}</td>
            </tr>            
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td> Total </td>
            <td> {{ Helper::currency($orderAmt) }} </td>
            <td> {{ Helper::currency($commissionAmount) }} </td>
        </tr>
    </tfoot>
</table>

</div>
