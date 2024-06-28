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

                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Driver Name</label>
                        <input class="form-control" placeholder="" type="text" value="{{ isset($driverDetails->user->name) ? $driverDetails->user->name : '-' }}" readonly style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Driver Email</label>
                        <input class="form-control" placeholder="" type="text" value="{{ isset($driverDetails->user->email) ? $driverDetails->user->email : '-' }}" readonly style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Distance</label>
                        <input class="form-control"  placeholder="" type="text" value="{{ isset($driverDetails->range) ? number_format($driverDetails->range, 2, '.', "") : '0' }}" readonly style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label for="order_number" class="c-gr f-500 f-16 w-100 mb-2">Order Number</label>
                        <input class="form-control" id="order_number" placeholder="" type="text" value="{{ $so->order_no }}" readonly style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Date</label>
                        <input type="text" readonly placeholder="Order Date" id="order_date" class="form-control" value="{{ date('d-m-Y', strtotime($so->date)) }}" style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Delivery Date</label>
                        <input type="text" readonly id="order_del_date" value="{{ date('d-m-Y', strtotime($so->delivery_date)) }}" class="form-control" style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Name </label>
                        <input type="text" class="form-control" id="customer-name" readonly value="{{ $so->customer_name }}" style="background:#efefef">
                    </div>
                </div>

                <div class="col-sm-12 col-md-2">
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

                <div>
                    <div class="col-md-12">
                        <div
                            class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                            <span>Products</span>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="row">

                            <div class="table-responsive">
                                <input type="hidden">
                                <table class="table table-bordered customLayout">
                                    <thead>
                                        <tr>

                                            <th >Category </th>

                                            <th >Product  </th>

                                            <th >Quantity </th>

                                            <th >Price  </th>

                                            <th >Amount </th>

                                            <th >Remarks </th>

                                        </tr>
                                    </thead>

                                    <tbody class="upsertable">

                                        @forelse($so->items as $key => $item)
                                        <tr>

                                            <td>
                                                <div style="min-width: 200px;width: 100%" class="removable-category">
                                                    <input type="text" readonly class="form-control" value="{{ $item->category->status == 1 ? $item->category->name : '' }}" style="background:#efefef">
                                                </div>
                                            </td>


                                            <td>
                                                <div style="min-width: 200px;width: 100%" class="removable-product">
                                                    <input type="text" readonly class="form-control" value="{{ $item->category->status == 1 ? $item->product->name : '' }}" style="background:#efefef">
                                                </div>
                                            </td>


                                            <td >
                                                <div style="min-width: 200px;">
                                                    <input type="number" class="form-control" style="background:#efefef;" value="{{ $item->qty }}" readonly>
                                                </div>
                                            </td>


                                            <td >
                                                <div style="min-width: 200px;">
                                                    <input type="text" class="form-control" style="background:#efefef;" value="{{ round($item->price) }}" readonly>
                                                </div>
                                            </td>

                                            <td >
                                                <div style="min-width: 200px;">
                                                    <input type="text" class="form-control" style="background:#efefef;" value="{{ round($item->amount) }}" readonly>
                                                </div>
                                            </td>


                                            <td >
                                                <div style="min-width: 200px;">
                                                    <input type="text" class="form-control" style="background:#efefef;" value="{{ $item->remarks }}" readonly>
                                                </div>
                                            </td>

                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6">
                                                <h4>This sales order has no products.</h4>
                                            </td>
                                           </tr>
                                        @endforelse

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td>
                                                <div style="min-width: 200px;">
                                                    <input type="number" class="form-control mt-quantity" style="background:#efefef" value="{{ $so->items->sum('qty') }}" readonly>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="min-width: 200px;">
                                                    <input type="text" class="form-control mt-price" style="background:#efefef" value="{{ ($so->items->sum('price')) }}" readonly>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="min-width: 200px;">
                                                    <input type="text" class="form-control mt-amount" style="background:#efefef" value="{{ round($so->items->sum('amount')) }}" readonly>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($so->price_matched && $so->sold_amount != round($so->items->sum('amount')))

                    <div class="col-md-12">
                        <div
                            class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                            <span>Different Price Agreement Proof</span>
                        </div>

                        <table class="table table-bordered customLayout">
                            <thead>
                               <tr>
                                  <th> Amount </th>
                               </tr>
                            </thead>
                            <tbody>
                               <tr>
                                  <td>
                                     <div style="min-width: 200px;width: 100%" class="removable-category">
                                        <input type="text" readonly="" class="form-control" value="{{ $so->sold_amount }}" style="background:#efefef">
                                     </div>
                                  </td>
                               </tr>
                            </tbody>
                         </table>

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
