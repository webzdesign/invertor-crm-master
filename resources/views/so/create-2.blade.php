@extends('layouts.master')
@section('breadcumb')
    <li class="f-14 f-400 c-7b">
        /
    </li>
    <li class="f-14 f-400 c-36">Add </li>
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

    <div class="cards">
        <form action="{{ route('get-available-item') }}" id="checkDriver" method="POST">
        <div class="cardsBody pb-0">
            <div class="row">

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product : <span class="text-danger">*</span></label>
                        <select name="product" id="product" class="product select2-hidden-accessible m-product" style="width:100%" data-placeholder="Select a Product">
                            @forelse($items as $item)
                            @if($loop->first)
                            <option value="" selected> --- Select a Product --- </option>
                            @endif
                            <option value="{{ $item['id'] }}" @if(array_key_exists('price', $item)) data-price="{{ $item['price'] }}" @endif >{{ $item['name'] }}</option>
                            @empty
                            <option value="" selected> --- No Product Available --- </option>
                            @endforelse
                        </select>
                        <span class="text-danger error-div d-block eproduct">{{ $errors->first('name') }}</span>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Price : <span class="text-danger">*</span></label>
                        <input type="text" name="price" id="price" class="form-control" placeholder="Enter price">
                        <span class="text-danger error-div d-block eprice">{{ $errors->first('price') }}</span>
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Postal Code : <span class="text-danger">*</span></label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}" class="form-control" placeholder="Enter postal code">
                        <span class="text-danger error-div d-block epostal_code">{{ $errors->first('postal_code') }}</span>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Address Line : <span class="text-danger">*</span></label>
                        <textarea name="address_line_1" id="address_line_1" class="form-control" style="height: 60px;">{{ old('address_line_1') }}</textarea>
                        <span class="text-danger error-div d-block eaddress_line_1">{{ $errors->first('address_line_1') }}</span>
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
            <button type="submit" class="btn-primary f-500 f-14">Check</button>
        </div>
        </form>
    </div>

    <form action="{{ route('save-so') }}" method="POST" id="so"> @csrf
        <div id="so-container"></div>
    </form>

@endsection

@section('script')
<script src="{{ asset('assets/js/intel.min.js') }}"></script>
<script>

$(document).ready(function(){

    $.validator.addMethod('minSalesPrice', function (value, element) {
        let bool = true;
        let validatorThisProduct = $(`#product`);

        if (validatorThisProduct.length > 0) {
            let minSP = $('option:selected', validatorThisProduct).attr('data-price');
            if (exists(minSP)) {
                if (parseFloat(value) < parseFloat(minSP)) {
                    return false;
                }
            }
        }

        return bool;
    }, function (result, element) {

        let validatorThisProduct = $(`#product`);
        let minSP = $('option:selected', validatorThisProduct).attr('data-price');

        if (result) {
            return `Minimum sales price must be atleast ${minSP}.`;
        }

        return "Select a product.";
    });

    function initIntelValidation () {
        const input = document.querySelector('#customer-phone');
        const errorMap = ["Phone number is invalid.", "Invalid country code", "Too short", "Too long"];

        const iti = window.intlTelInput(input, {
        initialCountry: "gb",
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
        $.validator.addMethod('minSalesPriceM', function (value, element) {
            let bool = true;
            let validatorThisProduct = $(`#mproduct`);

            if (validatorThisProduct.length > 0) {
                let minSP = $(validatorThisProduct).attr('data-minprice');
                if (exists(minSP)) {
                    if (parseFloat(value) < parseFloat(minSP)) {
                        return false;
                    }
                }
            }

            return bool;
        }, function (result, element) {

            let validatorThisProduct = $(`#mproduct`);
            let minSP = $(validatorThisProduct).attr('data-minprice');

            if (result) {
                return `Minimum sales price must be atleast ${minSP}.`;
            }

            return "Select a product.";
        });
    }

    $('#product').select2({
        width: '100%',
        allowClear: true
    });

    $("#checkDriver").validate({
        rules: {
            product: {
                required: true
            },
            address_line_1: {
                required: true
            },
            postal_code: {
                required: true,
                maxlength: 8
            },
            price: {
                required: true,
                digits: true,
                min: 1,
                minSalesPrice: true,
            }
        },
        messages: {
            product: {
                required: "Select a product."
            },
            address_line_1: {
                required: "Address line is required."
            },
            postal_code: {
                required: "Enter a postal code.",
                maxlength: 'Maximum 8 characters allowed for postal code.'
            },
            price: {
                required: "Enter price.",
                digits: "Enter valid format.",
                min: "Price can\'t be less than 1.",
            }
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent("div"));
        },
        submitHandler:function(form, event) {
            event.preventDefault();

            $('#so-container').empty();

            if(!this.beenSubmitted) {
                $.ajax({
                    url: "{{ route('get-available-item') }}",
                    type: "POST",
                    data: $('#checkDriver').serialize(),
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                        $('button[type="submit"]').attr('disabled', true);
                    },
                    success: function (response) {
                        if (response.status) {
                            $('#so-container').html(response.html);
                        } else {
                            if ('messages' in response) {
                                $('.eproduct').text(response.messages.product);
                                $('.eprice').text(response.messages.price);
                                $('.epostal_code').text(response.messages.postal_code);
                                $('.eaddress_line_1').text(response.messages.address_line_1);
                            } else {
                                Swal.fire('', response.message, 'error');
                            }
                        }
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                        $('button[type="submit"]').attr('disabled', false);

                        try {

                            $('#order_del_date').datepicker({
                                format: 'dd-mm-yyyy',
                                autoclose: true,
                                todayHighlight: true,
                                orientation: "bottom",
                                startDate: '-0d'
                            });

                            initIntelValidation();
                        } catch (err) {
                            console.warn('No driver available.');
                        }
                    }
                });
            }
        }
    });

    $("#so").validate({
        rules: {
            order_del_date: {
                required: true
            },
            customername: {
                required: true
            },
            customerphone: {
                inttel: true,
                required: true
            },
            customerfb: {
                url: true
            },
            'product[]': {
                required: true
            },
            'quantity[]': {
                required: true,
                digits: true,
                min: 1
            },
            'price[]': {
                required: true,
                number: true,
                min: 0,
                minSalesPriceM: true
            }
        },
        messages: {
            customerphone: {
                required: "Enter phone number."
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
            'product[]': {
                required: "Select a product."
            },
            'quantity[]': {
                required: "Enter quantity.",
                digits: "Enter valid format.",
                min: "Quantity can\'t be less than 1.",
            },
            'price[]': {
                required: "Enter price.",
                number: "Enter valid format.",
                min: "Price can\'t be less than 0.",
            }
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent("div"));
        },
        submitHandler: function(form, event) {
            event.preventDefault();

            if (!this.beenSubmitted) {
                $.ajax({
                    url: "{{ route('get-available-item') }}",
                    type: "POST",
                    data: $('#checkDriver').serialize(),
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                        $('button[type="submit"]').attr('disabled', true);
                    },
                    success: function (response) {
                        if (response.status) {
                            this.beenSubmitted = true;
                            form.submit();
                        } else {
                            Swal.fire('', response.message, 'error');
                            $('#so-container').empty();
                        }
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                        $('button[type="submit"]').attr('disabled', false);
                    }
                });
            }
        }
    });

    $(document).on('change', '#product', function () {
        $('#so-container').html('');
    });

    let calculateAmount = () => {
        let quantity = $(`#mquantity`).val();
        let price = $(`#mprice`).val();

        if (isNaN(quantity) || quantity == '') {
            quantity = 0;
        }

        if (isNaN(price) || price == '') {
            price = 0;
        }

        let total = (parseFloat(price) * parseInt(quantity));
        $(`#mamount`).val(total.toFixed(2));
    }

    $(document).on('change', '#mquantity, #mprice', function () {
        calculateAmount();
    });

    $(document).on('change', '#product', function (event) {
        $('.eproduct').text('');
    });
    $(document).on('change', '#price', function (event) {
        $('.eprice').text('');
    });
    $(document).on('change', '#postal_code', function (event) {
        $('.epostal_code').text('');
    });
    $(document).on('change', '#address_line_1', function (event) {
        $('.eaddress_line_1').text('');
    });
    $(document).on('keyup', '#price', function (event) {
        if ($('#mprice').is(':visible')) {
            $('#mprice').val($(this).val());
            calculateAmount();
        }
    });

});
</script>
@endsection
