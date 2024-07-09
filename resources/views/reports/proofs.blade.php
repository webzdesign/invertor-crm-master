@forelse (json_decode($transaction->attachments) as $image)
    <div class="col-md-4 col-sm-12">
        <a target="_blank" href="{{ asset("storage/payment-receipt/driver/$image") }}">
            <img src="{{ asset("storage/payment-receipt/driver/$image") }}" style="width: -webkit-fill-available;">
        </a>
    </div>
@empty
<h4> <strong> No proofs uploaded for this transaction </strong> </h4>    
@endforelse