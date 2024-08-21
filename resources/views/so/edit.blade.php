@extends('layouts.master')

@section('breadcumb')
    <li class="f-14 f-400 c-7b">
        /
    </li>
    <li class="f-14 f-400 c-36">Edit </li>
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
    <h2 class="f-24 f-700 c-36 my-2">Edit {{ $moduleName }}</h2>
    <form action="{{ route('sales-orders.update', $id) }}" method="POST" id="addSo"> @csrf @method('PUT')
        <div class="cards">
            <div class="cardsBody pb-0">

                <div class="row">

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="order_number" class="c-gr f-500 f-16 w-100 mb-2">Order Number :</label>
                            <input class="form-control" id="order_number" type="text" value="{{ $so->order_no }}" readonly style="background:#efefef">
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Delivery Date :
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" readonly name="order_del_date" placeholder="Order Delivery Date" id="order_del_date" value="{{ old('order_del_date', date('d-m-Y', strtotime($so->delivery_date))) }}" class="form-control datepicker" style="background:#ffffff">
                            @if ($errors->has('order_del_date'))
                                <span class="text-danger d-block">{{ $errors->first('order_del_date') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Name :
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="customer-name" placeholder="Enter customer name" name="customername" value="{{ old('customername', $so->customer_name) }}">
                            @if ($errors->has('customername'))
                                <span class="text-danger d-block">{{ $errors->first('customername') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Phone Number :
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="customerphone" id="customer-phone" value="{{ old('customerphone', $so->customer_phone) }}">
                            <input type="hidden" name="country_dial_code" id="country_dial_code" value="{{ old('country_dial_code', $so->country_dial_code) }}">
                            <input type="hidden" name="country_iso_code" id="country_iso_code" value="{{ old('country_iso_code', $so->country_iso_code) }}">
                            @if ($errors->has('customerphone'))
                                <span class="text-danger d-block">{{ $errors->first('customerphone') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Facebook URL :
                            </label>
                            <input type="url" class="form-control" name="customerfb" id="customer-fb" placeholder="Enter customer facebook url" value="{{ old('customerfb', $so->customer_facebook) }}">
                            @if ($errors->has('customerfb'))
                                <span class="text-danger d-block">{{ $errors->first('customerfb') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Postal Code : <span class="text-danger">*</span></label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $so->customer_postal_code) }}" class="form-control" placeholder="Enter postal code">
                            @if ($errors->has('postal_code'))
                                <span class="text-danger d-block">{{ $errors->first('postal_code') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Address Line : <span class="text-danger">*</span></label>
                            <textarea name="address_line_1" id="address_line_1" class="form-control" style="height: 60px;">{{ old('address_line_1', $so->customer_address_line_1) }}</textarea>
                            @if ($errors->has('address_line_1'))
                                <span class="text-danger d-block">{{ $errors->first('address_line_1') }}</span>
                            @endif
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

                                                <th style="">Category <span class="text-danger">*</span> </th>

                                                <th style="">Product <span class="text-danger">*</span> </th>

                                                <th style="">Quantity <span class="text-danger">*</span> </th>

                                                <th style="">Price <span class="text-danger">*</span> </th>

                                                <th style="">Amount </th>

                                                <th style="">Remarks </th>

                                                <th class="">Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody class="upsertable">

                                            @forelse($so->items as $key => $item)
                                            <tr>

                                                <td>
                                                    <div style="min-width: 200px;width: 100%" class="removable-category">
                                                        <select name="category[{{ $key }}]" data-indexid="{{ $key }}" id="category-{{ $key }}" class="select2 select2-hidden-accessible m-category" style="width:100%" data-placeholder="Select a Category">
                                                            @forelse($categories as $cid => $category)
                                                            @if($loop->first)
                                                            <option value="" selected> --- Select a Category --- </option>
                                                            @endif
                                                            <option value="{{ $cid }}" @if($cid == $item->category_id) selected @endif >{{ $category }}</option>
                                                            @empty
                                                            <option value="" selected> --- No Category Available --- </option>
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </td>


                                                <td>
                                                    <div style="min-width: 200px;width: 100%" class="removable-product">
                                                        <select name="product[{{ $key }}]" data-indexid="{{ $key }}" id="product-{{ $key }}" class="select2 select2-hidden-accessible m-product" style="width:100%" data-placeholder="Select a Product">
                                                            <option value="">Select Product</option>
                                                            @php
                                                                $cats = [];
                                                                if ($item?->category?->status == 1) {
                                                                    $cats = $item?->category?->product ?? [];
                                                                }
                                                            @endphp
                                                            @forelse ($cats as $product)
                                                            <option value="{{ $product->id }}" data-availablestock="{{ $product->stockin->sum('qty') ?? 0 }}"
                                                            @if(isset($htmlAttributes[$product->id])) data-baseprice="{{ $htmlAttributes[$product->id]['baseprice'] }}" data-minsalesprice="{{ $htmlAttributes[$product->id]['minsalesprice'] }}" data-defcomprice="{{ $htmlAttributes[$product->id]['defcomprice'] }}" @endif
                                                            @if($product->id == $item->product_id) selected @endif > {{ $product->name }} </option>
                                                            @empty
                                                            <option value="" data-price="0" selected> --- No Product Available --- </option>
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </td>

                                                <td style="">
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="{{ $key }}" name="quantity[{{ $key }}]" id="quantity-{{ $key }}" class="form-control m-quantity" value="{{ $item->qty }}" style="background:#ffffff">
                                                    </div>
                                                </td>


                                                <td style="">
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="{{ $key }}" name="price[{{ $key }}]" id="price-{{ $key }}" class="form-control m-price" value="{{ $item->price }}" style="background:#ffffff">
                                                    </div>
                                                </td>

                                                <td style="">
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="{{ $key }}" name="amount[{{ $key }}]" id="amount-{{ $key }}"  class="form-control m-amount" value="{{ $item->amount }}" style="background:#efefef" readonly>
                                                    </div>
                                                </td>


                                                <td style="">
                                                    <div style="min-width: 200px;">
                                                        <input type="text" data-indexid="{{ $key }}" tabindex="{{ $key }}" maxlength="255" name="remarks[{{ $key }}]" id="remarks-{{ $key }}" value="{{ $item->remarks }}" class="form-control m-remarks" style="background:#ffffff">
                                                    </div>
                                                </td>

                                                <td style="width:100px;" class="">
                                                    <div style="min-width: 100px;">
                                                        <button type="button" class="btn btn-primary addNewRow">+</button>
                                                        <button type="button" class="btn btn-danger removeRow" tabindex="-1">-</button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>

                                                <td>
                                                    <div style="min-width: 200px;width: 100%" class="removable-category">
                                                        <select name="category[0]" data-indexid="0" id="category-0" class="select2 select2-hidden-accessible m-category" style="width:100%" data-placeholder="Select a Category">
                                                            @forelse($categories as $cid => $category)
                                                            @if($loop->first)
                                                            <option value="" selected> --- Select a Category --- </option>
                                                            @endif
                                                            <option value="{{ $cid }}">{{ $category }}</option>
                                                            @empty
                                                            <option value="" selected> --- No Category Available --- </option>
                                                            @endforelse
                                                        </select>
                                                    </div>
                                                </td>


                                                <td>
                                                    <div style="min-width: 200px;width: 100%" class="removable-product">
                                                        <select name="product[0]" data-indexid="0" id="product-0" class="select2 select2-hidden-accessible m-product" style="width:100%" data-placeholder="Select a Product">
                                                            <option value="">Select Product
                                                            </option>
                                                        </select>
                                                    </div>
                                                </td>


                                                <td style="">
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="0" name="quantity[0]" id="quantity-0" class="form-control m-quantity" style="background:#ffffff">
                                                    </div>
                                                </td>


                                                <td style="">
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="0" name="price[0]" id="price-0" class="form-control m-price" style="background:#ffffff">
                                                    </div>
                                                </td>

                                                <td style="">
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="0" name="amount[0]" id="amount-0"  class="form-control m-amount" style="background:#efefef" readonly>
                                                    </div>
                                                </td>


                                                <td style="">
                                                    <div style="min-width: 200px;">
                                                        <input type="text" data-indexid="0" tabindex="0" maxlength="255" name="remarks[0]" id="remarks-0" class="form-control m-remarks" style="background:#ffffff">
                                                    </div>
                                                </td>

                                                <td style="width:100px;" class="">
                                                    <div style="min-width: 100px;">
                                                        <button type="button" class="btn btn-primary addNewRow">+</button>
                                                        <button type="button" class="btn btn-danger removeRow" tabindex="-1">-</button>
                                                    </div>
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
                                                        <input type="number" class="form-control mt-price" style="background:#efefef" value="{{ $so->items->sum('price') }}" readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="min-width: 200px;">
                                                        <input type="number" class="form-control mt-amount" style="background:#efefef" value="{{ $so->items->sum('amount') }}" readonly>
                                                    </div>
                                                </td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>




@if($errors->any())
<div class="alert alert-danger" role="alert">
    @php
        $ers = array_unique(\Illuminate\Support\Arr::flatten($errors->toArray()));
    @endphp

    @forelse($ers as $key => $value)
    <p> {{ $value }} </p>
    @empty
    <p>Something went wrong.</p>
    @endforelse

</div>
@endif

            </div>

            <div class="cardsFooter d-flex justify-content-center">
                <a href="{{ route('sales-orders.index') }}">
                    <button type="button" class="btn-default f-500 f-14">Cancel</button>
                </a>
                <button type="submit" class="btn-primary f-500 f-14">Save Changes</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
<script src="{{ asset('assets/js/intel.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            const input = document.querySelector('#customer-phone');
            const errorMap = ["Phone number is invalid.", "Invalid country code", "Too short", "Too long"];

            const iti = window.intlTelInput(input, {
                initialCountry: "{{ $so->country_iso_code ?? 'gb' }}",
                separateDialCode:true,
                nationalMode:false,
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

            $.validator.addMethod('minSalesPrice', function (value, element) {
                let bool = true;
                let validatorThisIndex = $(element).data('indexid');
                let validatorThisProduct = $(`#product-${validatorThisIndex}`);

                if (validatorThisProduct.length > 0) {
                    let minSP = $('option:selected', validatorThisProduct).attr('data-minsalesprice');
                    if (exists(minSP)) {
                        if (parseFloat(value) < parseFloat(minSP)) {
                            return false;
                        }
                    }
                }

                return bool;
            }, function (result, element) {

                let validatorThisIndex = $(element).data('indexid');
                let validatorThisProduct = $(`#product-${validatorThisIndex}`);
                let minSP = $('option:selected', validatorThisProduct).attr('data-minsalesprice');

                if (result) {
                    return `Minimum sales price must be atleast ${minSP}.`;
                }

                return "Select a product.";
            });

            input.addEventListener('keyup', () => {
                if (iti.isValidNumber()) {
                    $('#country_dial_code').val(iti.s.dialCode);
                    $('#country_iso_code').val(iti.j);
                }
            });
            input.addEventListener("countrychange", function() {
                if (iti.isValidNumber()) {
                    $('#country_dial_code').val(iti.s.dialCode);
                    $('#country_iso_code').val(iti.j);
                }
            });
            var categories = {!! json_encode($categories) !!};
            var categoriesHtml = `<option value="" selected> --- Select a Category --- </option>`;
            let lastElementIndex = {{ count($so->items) > 0 ? count($so->items) : 0 }};

            for (key in categories) {
                categoriesHtml += `<option value="${key}"> ${categories[key]} </option>`;
            }

            $(document).on('click', '.addNewRow', function (event) {
                cloned = $('.upsertable').find('tr').eq(0).clone();
                lastElementIndex++;

                cloned.find('.removable-category').empty().append(`<select data-indexid="${lastElementIndex}" name="category[${lastElementIndex}]" id="category-${lastElementIndex}" class="select2 select2-hidden-accessible m-category" style="width:100%" data-placeholder="Select a Category"> ${categoriesHtml} </select> `);
                cloned.find('.m-category').select2({
                    width: '100%',
                    allowClear: true
                });

                cloned.find('.removable-product').empty().append(`<select data-indexid="${lastElementIndex}" name="product[${lastElementIndex}]" id="product-${lastElementIndex}" class="select2 select2-hidden-accessible m-product" style="width:100%" data-placeholder="Select a Product"> </select> `);
                cloned.find('.m-product').select2({
                    width: '100%',
                    allowClear: true
                });

                cloned.find('.m-quantity').attr('id', `quantity-${lastElementIndex}`).attr('data-indexid', lastElementIndex).attr('name', `quantity[${lastElementIndex}]`).val(null);
                cloned.find('.m-price').attr('id', `price-${lastElementIndex}`).attr('data-indexid', lastElementIndex).attr('name', `price[${lastElementIndex}]`).val(null);
                cloned.find('.m-amount').attr('id', `amount-${lastElementIndex}`).attr('data-indexid', lastElementIndex).attr('name', `amount[${lastElementIndex}]`).val(null);
                cloned.find('.m-remarks').attr('id', `remarks-${lastElementIndex}`).attr('data-indexid', lastElementIndex).attr('name', `remarks[${lastElementIndex}]`).val(null);

                cloned.find('label.error').remove();
                $('.upsertable').append(cloned.get(0));

                cloned.find('.m-category').rules('add', {
                    required: true,
                    messages: {
                        required: "Select a category."
                    }
                });

                cloned.find('.m-product').rules('add', {
                    required: true,
                    messages: {
                        required: "Select a product."
                    }
                });

                cloned.find('.m-quantity').rules('add', {
                    required: true,
                    digits: true,
                    min: 1,
                    messages: {
                        required: "Enter quantity.",
                        digits: "Enter valid format.",
                        min: "Quantity can\'t be less than 1.",
                    }
                });

                cloned.find('.m-price').rules('add', {
                    required: true,
                    number: true,
                    min: 0,
                    minSalesPrice: true,
                    messages: {
                        required: "Enter price.",
                        number: "Enter valid format.",
                        min: "Price can\'t be less than 0.",
                    }
                });

            });


            $(document).on('click', '.removeRow', function(event) {
                if ($('.upsertable tr').length > 1) {
                    $(this).closest("tr").remove();
                }

                let iid = $(this).parent().parent().prev().find('.m-remarks').data('indexid');

                if (typeof iid !== 'undefined' && iid !== '' && iid !== null) {
                    calculateAmount(iid);
                }
            });

            let calculateAmount = (indexId = 0) => {
                let quantity = $(`#quantity-${indexId}`).val();
                let price = $(`#price-${indexId}`).val();

                if (isNaN(quantity) || quantity == '') {
                    quantity = 0;
                }

                if (isNaN(price) || price == '') {
                    price = 0;
                }

                let total = (parseFloat(price) * parseInt(quantity));

                $(`#amount-${indexId}`).val(total.toFixed(2));

                /** Final Total for Each Row **/
                let mtQuantity = 0;
                let mtPrice = 0;
                let mtAmount = 0;

                $('.upsertable > tr').each(function (index, element) {
                    let tempQuantity = $(this).find('.m-quantity').val();
                    let tempPrice = $(this).find('.m-price').val();
                    let tempAmount = $(this).find('.m-amount').val();

                    if (isNaN(tempQuantity) || tempQuantity == '') {
                        tempQuantity = 0;
                    }

                    if (isNaN(tempPrice) || tempPrice == '') {
                        tempPrice = 0;
                    }

                    if (isNaN(tempAmount) || tempAmount == '') {
                        tempAmount = 0;
                    }

                    mtQuantity += parseInt(tempQuantity);
                    mtPrice += parseFloat(tempPrice);
                    mtAmount += parseFloat(tempAmount);
                });

                $('.mt-quantity').val(mtQuantity);
                $('.mt-price').val(mtPrice.toFixed(2));
                $('.mt-amount').val(mtAmount.toFixed(2));

                /** Final Total for Each Row **/
            }

            $(document).on('change', '.m-category', function (event) {
                let indexId = $(this).data('indexid');
                let thisId = $(this).val();

                if (thisId !== '') {
                    $.ajax({
                        url: "{{ route('get-products-on-category-so') }}",
                        type: 'POST',
                        data: {
                            id: thisId
                        },
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function (response) {
                            if (response !== '') {
                                $(`#product-${indexId}`).empty().append(response);
                                $(`#product-${indexId}`).select2({
                                    width: '100%',
                                    allowClear: true,
                                    placeholder: "Select a Product"
                                });
                            }
                        },
                        complete: function () {
                            $('body').find('.LoaderSec').addClass('d-none');
                        }
                    });
                } else {
                    $(`#product-${indexId}`).empty().append(`<option value="" selected> Select a Product </option>`);
                    $(`#product-${indexId}`).select2({
                        width: '100%',
                        allowClear: true,
                        placeholder: "Select a Product"
                    });
                    $(`#quantity-${indexId}`).val(null);
                    $(`#price-${indexId}`).val(null);
                    calculateAmount(indexId);
                }
            })

            $(document).on('change', '.m-product', function (event) {
                let indexId = $(this).data('indexid');
                let thisId = $(this).val();

                calculateAmount(indexId);

                let that = $(this);

                $('.m-product').not(this).each(function (index, element) {
                    if ($(element).val() !== null && thisId == $(element).val()) {
                        $(that).val(null).trigger('change');
                        Swal.fire('Warning', 'Product is already selected.', 'warning');
                        return false;
                    }
                });
            });

            $(document).on('change', '.m-quantity, .m-price', function (event) {
                calculateAmount($(this).data('indexid'));
            });

            $('#order_del_date').click(function(){
                $('#order_del_date').datepicker('show');
            })
            $('#order_del_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom"
            });

            $("#addSo").validate({
                rules: {
                    status: {
                        required: true
                    },
                    order_del_date: {
                        required: true
                    },
                    customername: {
                        required: true
                    },
                    customerphone: {
                        required: true,
                        inttel: true
                    },
                    customerfb: {
                        url: true
                    },
                    postal_code: {
                        required: true,
                        maxlength: 8
                    },
                    address_line_1: {
                        required: true
                    },
                    @forelse ($so->items as $key => $val)
                    'category[{{ $key }}]': {
                        required: true
                    },
                    'product[{{ $key }}]': {
                        required: true
                    },
                    'quantity[{{ $key }}]': {
                        required: true,
                        digits: true,
                        min: 1
                    },
                    'price[{{ $key }}]': {
                        required: true,
                        number: true,
                        min: 0,
                        minSalesPrice: true
                    },
                    @empty
                    'category[0]': {
                        required: true
                    },
                    'product[0]': {
                        required: true
                    },
                    'quantity[0]': {
                        required: true,
                        digits: true,
                        min: 1
                    },
                    'price[0]': {
                        required: true,
                        number: true,
                        min: 0,
                        minSalesPrice: true
                    }
                    @endforelse
                },
                messages: {
                    customerphone: {
                        required: "Enter phone number."
                    },
                    status: {
                        required: "Select a status.",
                    },
                    order_del_date: {
                        required: "Select order delivery date.",
                    },
                    customername: {
                        required: "Enter customer name."
                    },
                    customerfb: {
                        url: "Enter valid url."
                    },
                    postal_code: {
                        required: "Enter a postal code.",
                        maxlength: 'Maximum 8 characters allowed for postal code.'
                    },
                    address_line_1: {
                        required: "Address Line is required."
                    },
                    @forelse ($so->items as $key => $val)
                    'category[{{ $key }}]': {
                        required: "Select a category."
                    },
                    'product[{{ $key }}]': {
                        required: "Select a product."
                    },
                    'quantity[{{ $key }}]': {
                        required: "Enter quantity.",
                        digits: "Enter valid format.",
                        min: "Quantity can\'t be less than 1.",
                    },
                    'price[{{ $key }}]': {
                        required: "Enter price.",
                        number: "Enter valid format.",
                        min: "Price can\'t be less than 0.",
                    },
                    @empty
                    'category[0]': {
                        required: "Select a category."
                    },
                    'product[0]': {
                        required: "Select a product."
                    },
                    'quantity[0]': {
                        required: "Enter quantity.",
                        digits: "Enter valid format.",
                        min: "Quantity can\'t be less than 1.",
                    },
                    'price[0]': {
                        required: "Enter price.",
                        number: "Enter valid format.",
                        min: "Price can\'t be less than 0.",
                    }
                    @endforelse
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                },
                submitHandler: function(form) {
                    $('button[type="submit"]').attr('disabled', true);
                    if (!this.beenSubmitted) {
                        this.beenSubmitted = true;
                        form.submit();
                    }
                }
            });
        });
    </script>
@endsection
