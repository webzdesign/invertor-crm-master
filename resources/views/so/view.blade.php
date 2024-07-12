@extends('layouts.master')

@section('breadcumb')
    <li class="f-14 f-400 c-7b">
        /
    </li>
    <li class="f-14 f-400 c-36">View </li>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/intel.css') }}">
<style>
    .customLayout th {
        white-space: nowrap;
        font-size: 13px;
    }
    .iti__selected-flag {
        height: 32px!important;
    }
    .iti--show-flags {
        width: 100%!important;
    }
</style>
@endsection

@section('content')
    {{ Config::set('app.module', $moduleName) }}
    <h2 class="f-24 f-700 c-36 my-2">View {{ $moduleName }}</h2>
    <div class="cards">
        <div class="cardsBody pb-0">

            <div class="row">

                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label for="order_number" class="c-gr f-500 f-16 w-100 mb-2">Order Number</label>
                        <input class="form-control" id="order_number" placeholder="" type="text" value="{{ $so->order_no }}" readonly style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Name </label>
                        <input type="text" class="form-control" id="customer-name" readonly value="{{ $so->customer_name }}" style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Date</label>
                        <input type="text" readonly placeholder="Order Date" id="order_date" class="form-control" value="{{ date('d-m-Y', strtotime($so->date)) }}" style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Delivery Date</label>
                        <input type="text" readonly id="order_del_date" value="{{ date('d-m-Y', strtotime($so->delivery_date)) }}" class="form-control" style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Phone Number</label>
                        <input type="text" class="form-control" id="customer-phone" value="{{ $so->customer_phone }}" readonly style="background: #efefef;">
                        <input type="hidden" name="country_dial_code" id="country_dial_code" value="{{ old('country_dial_code', $so->country_dial_code) }}">
                        <input type="hidden" name="country_iso_code" id="country_iso_code" value="{{ old('country_iso_code', $so->country_iso_code) }}">
                    </div>
                </div>

                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label for="customer-fb" class="c-gr f-500 f-16 w-100 mb-2">Customer Facebook URL </label>
                        <input type="url" class="form-control" id="customer-fb" value="{{ $so->customer_facebook }}" readonly style="background: #efefef;">
                    </div>
                </div>

                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Postal Code </label>
                        <input type="text" id="postal_code" value="{{ $so->customer_postal_code }}" class="form-control" readonly style="background: #efefef;">
                    </div>
                </div>

                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">House Number </label>
                        <textarea id="address_line_1" class="form-control" style="height: 60px;background:#efefef;" readonly>{{ $so->customer_address_line_1 }}</textarea>
                    </div>
                </div>

                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Driver Name</label>
                        <input class="form-control" placeholder="" type="text" value="{{ isset($driverDetails->user->name) ? $driverDetails->user->name : '-' }}" readonly style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Driver Email</label>
                        <input class="form-control" placeholder="" type="text" value="{{ isset($driverDetails->user->email) ? $driverDetails->user->email : '-' }}" readonly style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Distance</label>
                        <input class="form-control"  placeholder="" type="text" value="{{ isset($driverDetails->range) ? number_format($driverDetails->range, 2, '.', "") : '0' }}" readonly style="background:#efefef">
                    </div>
                </div>

                <div>
                    <div class="col-md-12">
                        <div
                            class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                            <span>Products</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">

                            @forelse($so->items as $key => $item)

                            <div class="col-md-12">
                                <div class="row">
            
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <label class="c-gr f-500 f-16 w-100 mb-2">Category :</label>
                                            <input type="text" readonly class="form-control" value="{{ $item->category->status == 1 ? $item->category->name : '' }}" readonly>
                                        </div>
                                    </div>
            
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <label class="c-gr f-500 f-16 w-100 mb-2">Product :</label>
                                            <input type="text" readonly class="form-control" value="{{ $item->category->status == 1 ? $item->product->name : '' }}" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <label class="c-gr f-500 f-16 w-100 mb-2">Quantity :</label>
                                            <input type="number" data-indexid="0" name="quantity[]" id="mquantity" class="form-control" min="1" readonly value="{{$item->qty}}">
                                        </div>
                                    </div>
            
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <label class="c-gr f-500 f-16 w-100 mb-2"> Price :</label>
                                            <input type="number" data-indexid="0" id="mprice" name="price[]" id="mprice" readonly class="form-control" min="0" value="{{ $so->price_matched ? $item->sold_item_amount : $item->price }}">
                                        </div>
                                    </div>
            
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <label class="c-gr f-500 f-16 w-100 mb-2"> Amount :</label>
                                            <input type="number" data-indexid="0" id="mamount" name="amount[]" id="maount" class="form-control" readonly value="{{ $so->price_matched ? ($item->sold_item_amount * $item->qty) : $item->amount  }}">
                                        </div>
                                    </div>
            
                                    <div class="col-md-2 col-sm-12">
                                        <div class="form-group">
                                            <label class="c-gr f-500 f-16 w-100 mb-2">Remarks :</label>
                                            <input type="text" data-indexid="0" tabindex="0" maxlength="255" name="remarks[]" id="mremarks" class="form-control" readonly value="{{$item->remarks}}">
                                        </div>
                                    </div>
            
                                </div>
                            </div>

                            @empty
                            @endforelse

                        </div>
                    </div>

                    @if($so->price_matched)
                    
                    <div class="col-md-12">
                        <div
                            class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                            <span>Order price breakdown</span>
                        </div>

                        <div class="col-md-12">
                            <div class="row">
        
                                <div class="col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label class="c-gr f-500 f-16 w-100 mb-2">Amount received by driver :</label>
                                        <input type="text" class="form-control" value="{{ $so->sold_amount + $so->driver_amount }}" readonly>
                                    </div>
                                </div>
        
                                <div class="col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label class="c-gr f-500 f-16 w-100 mb-2">Driver amount :</label>
                                        <input type="text" class="form-control" value="{{ $so->driver_amount }}" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 col-sm-12">
                                    <div class="form-group">
                                        <label class="c-gr f-500 f-16 w-100 mb-2">New order amount :</label>
                                        <input type="text" class="form-control" readonly value="{{ $so->sold_amount }}">
                                    </div>
                                </div>
        
                            </div>
                        </div>

                    </div>


                    <div
                    class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                    <span>Different price agreement proof</span>
                </div>

                    <div class="row">
                        @forelse($so->proofimages as $key => $img)
                            <div class="col-md-2 d-flex align-items-center justify-content-center">
                                <a href="{{ $img->doc }}" target="_blank">
                                        <img src="{{ $img->doc }}" class="w-100 shadow-1-strong rounded" style="object-fit:cover;height:100px;width:100%;margin:5px 0px;border:1px solid black;">
                                </a>
                            </div>
                        @empty
                            <center>
                                <p><strong>No agreemnt for proof uploaded for this order.</strong></p>
                            </center>
                        @endforelse
                    </div>

                    @endif


                    <div class="col-md-12">
                        <div
                            class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                            <span>Order history</span>
                        </div>

                        <div class="col-md-12">
                            <div class="row">
        
                                @forelse($logs as $key => $l)
                                <div class="activity py-1 hist">
                                    <p class="f-14" style="margin-bottom:0px;">
                                        <strong> {{ date('d-m-Y H:i', strtotime($l->created_at)) }} @if(!empty($l->watcher_id)) {{ $l->watcher->name }} @else @if(!empty($l->user->name)) {{ $l->user->name }} @else Robot @endif @endif </strong> :
                                        @if($l->type == 1)
                                            added a task [ <strong>{{ $l->description }}</strong> ]
                                        @elseif($l->type == 2)
                                            @if(!empty(trim($l->description)))
                                                moved order to
                                                <span class="status-lbl f-12" style="background: {{ $l->to_status->color }};color:{{ Helper::generateTextColor($l->to_status->color) }};text-transform:uppercase;"> {{ $l->to_status->name }} </span> from
                                                <span class="status-lbl f-12" style="background: {{ $l->from_status->color }};color:{{ Helper::generateTextColor($l->from_status->color) }};text-transform:uppercase;"> {{ $l->from_status->name }} </span>
                                                <br> <strong>Comment</strong> : {{ $l->description }}
                                            @else
                                                @if(empty($l->from_status))
                                                created order
                                                @else
                                                moved to
                                                <span class="status-lbl f-12" style="background: {{ $l->to_status->color }};color:{{ Helper::generateTextColor($l->to_status->color) }};text-transform:uppercase;"> {{ $l->to_status->name }} </span>
                                                from
                                                <span class="status-lbl f-12" style="background: {{ $l->from_status->color }};color:{{ Helper::generateTextColor($l->from_status->color) }};text-transform:uppercase;"> {{ $l->from_status->name }} </span>
                                                @endif
                                            @endif
                                        @elseif($l->type == 3)
                                            assigned order to <strong>
                                                @if(isset($l->order->responsible->name)) {{ $l->order->responsible->name }} @else  it's seller @endif
                                            </strong>
                                        @elseif($l->type == 4)
                                            {!! $l->description !!}
                                        @endif
                                    </p>
                                </div>
                            @empty
                            <div class="activity f-13">
                                History not available
                            </div>
                            @endforelse
        
                            </div>
                        </div>

                    </div>


                </div>
            </div>

        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('sales-orders.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('assets/js/intel.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            const input = document.querySelector('#customer-phone');
            const errorMap = ["Phone number is invalid.", "Invalid country code", "Too short", "Too long"];

            const iti = window.intlTelInput(input, {
                initialCountry: "{{ $so->country_iso_code ?? 'gb' }}",
                preferredCountries: ['gb', 'pk'],
                utilsScript: "{{ asset('assets/js/intel2.js') }}"
            });

            $.validator.addMethod('inttel', function (value, element) {
                    if (value.trim() == '' || iti.isValidNumber()) {
                        return true;
                    }
                return false;
            }, function (result, element) {
                    return errorMap[iti.getValidationError()] || errorMap[0];
            });

            input.addEventListener('keyup', () => {
                if (iti.isValidNumber()) {
                    $('#country_dial_code').val(iti.s.dialCode);
                    $('#country_iso_code').val(iti.j);
                }
            });
        });
    </script>
@endsection
