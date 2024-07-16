<div class="row">

    <table>
        <tbody>
            @if(User::isAdmin())
            <tr>
                <td>SELLER NAME</td>
                <td> {{ $data->user->name ?? '' }} </td>
            </tr>
            @endif
            <tr>
                <td>COMMISSION AMOUNT</td>
                <td> {{ Helper::currency($data->amount) }} </td>
            </tr>
            <tr>
                <td>FIRST NAME (BANK)</td>
                <td> {{ $data->bank->name ?? '-' }} </td>
            </tr>
            <tr>
                <td>SURNAME (BANK)</td>
                <td> {{ $data->bank->surname ?? '-' }} </td>
            </tr>
            <tr>
                <td>IBAN NUMBER (BANK)</td>
                <td> {{ $data->bank->iban_number ?? '' }} </td>
            </tr>
            <tr>
                <td>REQUEST STATUS</td>
                <td>
                    <strong>
                        @if($data->status == 1)
                        <span class="text-success"> Accepted </span>
                        @elseif($data->status == 2)
                        <span class="text-danger"> Rejected </span>
                        @else
                        <span class="text-secondary"> Pending </span>
                        @endif
                    </strong>
                </td>
            </tr>
            <tr>
                <td>REQUEST DATE</td>
                <td> {{ date('d-m-Y', strtotime($data->created_at)) }} </td>
            </tr>
            <tr>
                <td>ORDERS FROM DATE</td>
                <td> {{ date('d-m-Y', strtotime($data->from)) }} </td>
            </tr>
            <tr>
                <td>ORDERS TO DATE</td>
                <td> {{ date('d-m-Y', strtotime($data->to)) }} </td>
            </tr>
        </tbody>
    </table>

    @php
    $transactions = \App\Models\Transaction::with('order')->whereIn('so_id', json_decode($data->orders, true))
        ->where('amount_type', 3)
        ->get();
    @endphp

<table class="table" id="withdrawable-orders" style="margin-top: 20px!important;">
    <thead>
        <tr>
            <th>ORDER NUMBER</th>
            <th>POSTAL CODE</th>
            <th>COMMISSION AMOUNT</th>
            {{-- <th>ORDER AMOUNT</th> --}}
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
                <td> <a target="_blank" href="https://www.google.com/maps/place/{{ $item->order->customer_postal_code }}"> {{ $item->order->customer_postal_code }} </a> </td>
                <td>{{ Helper::currency($item->amount) }}</td>
                {{-- <td>{{ Helper::currency($item->order->sold_amount) }}</td> --}}
            </tr>            
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"> Total </td>
            <td> {{ Helper::currency($orderAmt) }} </td>
            {{-- <td> {{ Helper::currency($commissionAmount) }} </td> --}}
        </tr>
    </tfoot>
</table>

<hr class="hr mt-2">

<div class="row">
    @if(!empty($data->attachments))
    @foreach (json_decode($data->attachments) as $image)
    <div class="col-4">
        <a target="_blank" href="{{ asset("storage/payment-receipt/seller/$image") }}">
            <img class="inline-image-preview" src="{{ asset("storage/payment-receipt/seller/$image") }}" alt="{{ $image }}">
        </a>
    </div>
    @endforeach
    @else
    @if(in_array($data->status, [1,2]))
        <strong class="text-center mt-4 mb-2">No payment receipt uploaded for this transaction.</strong>
    @endif
    @endif
</div>

</div>
