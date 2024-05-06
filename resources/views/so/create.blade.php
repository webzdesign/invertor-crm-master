@extends('layouts.master')

@section('breadcumb')
    <li class="f-14 f-400 c-7b">
        /
    </li>
    <li class="f-14 f-400 c-36">Add </li>
@endsection

@section('css')
<style>
    .customLayout th {
        white-space: nowrap;
        font-size: 13px;
    }

    .srNumberClass {
        font-size: 10px !important;
    }
</style>
@endsection

@section('content')
    {{ Config::set('app.module', $moduleName) }}
    <h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
    <form action="{{ route('sales-orders.store') }}" method="POST" id="addSo"> @csrf
        <div class="cards">
            <div class="cardsBody pb-0">

                <div class="row">

                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="order_number" class="c-gr f-500 f-16 w-100 mb-2">Order Number:</label>
                            <input class="form-control" id="order_number" placeholder="" type="text" value="{{ $orderNo }}" readonly style="background:#efefef">
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Date:
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" readonly name="order_date" placeholder="Order Date" id="order_date" value="{{ old('order_date') }}"
                                class="form-control datepicker"
                                style="background:#ffffff">
                                @if ($errors->has('order_date'))
                                    <span class="text-danger d-block">{{ $errors->first('order_date') }}</span>
                                @endif
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Delivery Date:
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" readonly name="order_del_date" placeholder="Order Delivery Date" id="order_del_date" class="form-control datepicker" style="background:#ffffff" value="{{ old('order_del_date') }}">
                            @if ($errors->has('order_del_date'))
                                <span class="text-danger d-block">{{ $errors->first('order_del_date') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Name:
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="customer-name" placeholder="Enter customer name" name="customername" value="{{ old('customername') }}">
                            @if ($errors->has('customername'))
                                <span class="text-danger d-block">{{ $errors->first('customername') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Phone Number:
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="customerphone" id="customer-phone" placeholder="Enter customer phone number" value="{{ old('customerphone') }}">
                            @if ($errors->has('customerphone'))
                                <span class="text-danger d-block">{{ $errors->first('customerphone') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-sm-12 col-md-2">
                        <div class="form-group">
                            <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Customer Facebook URL:
                            </label>
                            <input type="url" class="form-control" name="customerfb" id="customer-fb" placeholder="Enter customer facebook url" value="{{ old('customerfb') }}">
                            @if ($errors->has('customerfb'))
                                <span class="text-danger d-block">{{ $errors->first('customerfb') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Country: <span class="text-danger">*</span></label>
                            <select name="country" id="country" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Country ---">
                                @forelse($countries as $cid => $cname)
                                    @if($loop->first)
                                    <option value="" selected> --- Select a Country --- </option>
                                    @endif
                                    <option value="{{ $cid }}"> {{ $cname }} </option>
                                @empty                                
                                    <option value=""> --- No Country Found --- </option>
                                @endforelse
                            </select>
                            @if ($errors->has('country'))
                                <span class="text-danger d-block">{{ $errors->first('country') }}</span>
                            @endif
                        </div>
                    </div>
    
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">State: <span class="text-danger">*</span></label>
                            <select name="state" id="state" class="select2 select2-hidden-accessible" data-placeholder="--- Select a State ---">
                                <option value="" selected> --- Select State --- </option>
                            </select>
                            @if ($errors->has('state'))
                                <span class="text-danger d-block">{{ $errors->first('state') }}</span>
                            @endif
                        </div>
                    </div>
    
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">City: <span class="text-danger">*</span></label>
                            <select name="city" id="city" class="select2 select2-hidden-accessible" data-placeholder="--- Select a City ---">
                                <option value="" selected> --- Select City --- </option>
                            </select>
                            @if ($errors->has('city'))
                                <span class="text-danger d-block">{{ $errors->first('city') }}</span>
                            @endif
                        </div>
                    </div>
    
                    <div class="col-md-3 col-sm-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Postal Code: <span class="text-danger">*</span></label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" class="form-control" placeholder="Enter postal code">
                            @if ($errors->has('postal_code'))
                                <span class="text-danger d-block">{{ $errors->first('postal_code') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Address Line 1: <span class="text-danger">*</span></label>
                            <textarea name="address_line_1" id="address_line_1" class="form-control" style="height: 60px;">{{ old('address_line_1') }}</textarea>
                            @if ($errors->has('address_line_1'))
                                <span class="text-danger d-block">{{ $errors->first('address_line_1') }}</span>
                            @endif
                        </div>
                    </div>
    
                    <div class="col-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Address Line 2: <span class="text-danger">*</span></label>
                            <textarea name="address_line_2" id="address_line_2" class="form-control" style="height: 60px;">{{ old('order_line_2') }}</textarea>
                            @if ($errors->has('address_line_2'))
                                <span class="text-danger d-block">{{ $errors->first('address_line_2') }}</span>
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

                                                <th >Category <span class="text-danger">*</span> </th>

                                                <th >Product <span class="text-danger">*</span> </th>

                                                <th style="width:100px;">Available Stock </th>

                                                <th >Quantity <span class="text-danger">*</span> </th>

                                                <th >Price <span class="text-danger">*</span> </th>

                                                <th >Amount </th>

                                                <th >Remarks </th>

                                                <th class="">Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody class="upsertable">
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


                                                <td >
                                                    <div style="min-width: 100px;">
                                                        <input type="number" data-indexid="0" id="as-0" class="form-control m-as" style="background:#efefef" readonly>
                                                    </div>
                                                </td>

                                                <td >
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="0" name="quantity[0]" id="quantity-0" class="form-control m-quantity" style="background:#ffffff">
                                                    </div>
                                                </td>


                                                <td >
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="0" name="price[0]" id="price-0" class="form-control m-price" style="background:#ffffff">
                                                    </div>
                                                </td>

                                                <td >
                                                    <div style="min-width: 200px;">
                                                        <input type="number" data-indexid="0" name="amount[0]" id="amount-0"  class="form-control m-amount" style="background:#efefef" readonly>
                                                    </div>
                                                </td>


                                                <td >
                                                    <div style="min-width: 200px;">
                                                        <input type="text" data-indexid="0" tabindex="0" maxlength="255" name="remarks[0]" id="remarks-0" class="form-control m-remarks" style="background:#ffffff">
                                                    </div>
                                                </td>

                                                <td style="width:100px;" class="">
                                                    <div style="min-width: 100px;">
                                                        <button type="button" class="btn btn-primary btn-sm addNewRow">+</button>
                                                        <button type="button" class="btn btn-danger btn-sm removeRow" tabindex="-1">-</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td> 
                                                    <div style="min-width: 200px;">
                                                        <input type="number" class="form-control mt-quantity" style="background:#efefef" value="0" readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="min-width: 200px;">
                                                        <input type="number" class="form-control mt-price" style="background:#efefef" value="0" readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="min-width: 200px;">
                                                        <input type="number" class="form-control mt-amount" style="background:#efefef" value="0" readonly>
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
                <button type="submit" class="btn-primary f-500 f-14">Save</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            
            var categories = {!! json_encode($categories) !!};
            var categoriesHtml = `<option value="" selected> --- Select a Category --- </option>`;
            let lastElementIndex = 0;

            (function writeCategories() {
                for (key in categories) {
                    categoriesHtml += `<option value="${key}"> ${categories[key]} </option>`;
                }
            })();

            $.validator.addMethod("inStock", function(value, element){
                let dIndex = $(element).data('indexid');
                let availableStock = parseInt($(`#as-${dIndex}`).val());
                let stock = $(element).val();

                if ($(`#product-${dIndex}`).val() == null || $(`#product-${dIndex}`).val() == '') {
                    return true;
                }

                if (isNaN(availableStock) || availableStock === '' || availableStock === null) {
                    availableStock = 0;
                }

                if (availableStock >= stock) {
                    return true;
                } else {
                    return false;
                }
            }, "Enter quantity out of avaialble stock."); 

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

                cloned.find('.m-as').attr('id', `as-${lastElementIndex}`).attr('data-indexid', lastElementIndex).val(null);
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
                    inStock: true,
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
                    $(`#amount-${indexId}`).val(null);
                    $(`#remarks-${indexId}`).val(null);
                    $(`#as-${indexId}`).val(null);
                    calculateAmount(indexId);
                }
            })

            $(document).on('change', '.m-product', function (event) {
                let indexId = $(this).data('indexid');
                let thisId = $(this).val();

                if (thisId !== '') {
                    $(`#price-${indexId}`).val($(this).find(':selected').data('price'));
                    $(`#as-${indexId}`).val($(this).find(':selected').data('availablestock'));
                    calculateAmount(indexId);
                } else {
                    $(`#quantity-${indexId}`).val(null);
                    $(`#price-${indexId}`).val(null);
                    $(`#amount-${indexId}`).val(null);
                    $(`#remarks-${indexId}`).val(null);
                    $(`#as-${indexId}`).val(null);
                    calculateAmount(indexId);
                }

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

            $('#country').on('change', function (event) {
                let country = event.target.value;

                if (country !== '') {
                    $.ajax({
                        url: "{{ route('getStates') }}",
                        type: 'POST',
                        data: {
                            id: country
                        },
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');                    
                        },
                        success: function (response) {
                            if (response.status) {
                                $('#state').empty().append(response.states);
                                $("#state").select2({
                                    width: '100%',
                                    allowClear: true,
                                    placeholder: "--- Select a State ---"
                                });

                                $('#city').empty();
                                $("#city").select2({
                                    width: '100%',
                                    allowClear: true,
                                    placeholder: "--- Select a City ---"
                                });
                            }
                        },
                        complete: function () {
                            $('body').find('.LoaderSec').addClass('d-none');
                        }
                    });
                }
            });

            $('#state').on('change', function (event) {
                let state = event.target.value;
                
                if (state !== '') {
                    $.ajax({
                        url: "{{ route('getCities') }}",
                        type: 'POST',
                        data: {
                            id: state
                        },
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');                    
                        },
                        success: function (response) {
                            if (response.status) {
                                $('#city').empty().append(response.cities);
                                $("#city").select2({
                                    width: '100%',
                                    allowClear: true,
                                    placeholder: "--- Select a City ---"
                                });
                            }
                        },
                        complete: function () {
                            $('body').find('.LoaderSec').addClass('d-none');
                        }
                    });
                }
            });

            $('#order_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom"
            });

            $('#order_del_date').datepicker({
                format: 'dd-mm-yyyy',
                autoclose: true,
                todayHighlight: true,
                orientation: "bottom"
            });

            $("#addSo").validate({
                rules: {
                    order_date: {
                        required: true
                    },
                    order_del_date: {
                        required: true
                    },
                    customername: {
                        required: true
                    },
                    customerphone: {
                        required: true
                    },
                    customerfb: {
                        url: true
                    },
                    country: {
                        required: true
                    },
                    state: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                    postal_code: {
                        required: true
                    },
                    address_line_1: {
                        required: true
                    },
                    address_line_2: {
                        required: true
                    },
                    'category[0]': {
                        required: true
                    },
                    'product[0]': {
                        required: true
                    },
                    'quantity[0]': {
                        required: true,
                        digits: true,
                        min: 1,
                        inStock: true
                    },
                    'price[0]': {
                        required: true,
                        number: true,
                        min: 0,
                    }
                },
                messages: {
                    order_date: {
                        required: "Select order date.",
                    },
                    order_del_date: {
                        required: "Select order delivery date.",
                    },
                    customername: {
                        required: "Enter customer name."
                    },
                    customerphone: {
                        required: "Enter customer phone number."
                    },
                    customerfb: {
                        url: "Enter valid url."
                    },
                    country: {
                        required: "Select a country."
                    },
                    state: {
                        required: "Select a state."
                    },
                    city: {
                        required: "Select a city."
                    },
                    postal_code: {
                        required: "Enter a postal code."
                    },
                    address_line_1: {
                        required: "Enter address line 1."
                    },
                    address_line_2: {
                        required: "Enter address line 2."
                    },
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
