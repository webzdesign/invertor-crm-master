
<table class="table" id="withdrawable-orders">
    <thead>
        <tr>
            <th>ORDER NUMBER</th>
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
                        @if(count($transactions) == 1)
                            <input type="hidden" name="to_date" value="{{ date('Y-m-d H:i:s', strtotime($item->created_at)) }}">
                        @endif
                    @elseif($loop->last)
                        <input type="hidden" name="to_date" value="{{ date('Y-m-d H:i:s', strtotime($item->created_at)) }}">
                    @endif
                    @php 
                    $orderAmt += $item->amount;
                    $commissionAmount += $item->order->sold_amount;
                    @endphp
                </td>
                <td>{{ Helper::currency($item->amount) }}</td>
                {{-- <td>{{ Helper::currency($item->order->sold_amount) }}</td> --}}
            </tr>            
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td> Total </td>
            <td> {{ Helper::currency($orderAmt) }} </td>
            {{-- <td> {{ Helper::currency($commissionAmount) }} </td> --}}
        </tr>
    </tfoot>
</table>