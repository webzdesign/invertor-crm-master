@extends('layouts.master')

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
    <h2 class="f-24 f-700 c-36 my-2"> {{ $moduleName }}</h2>
    <form action="{{ route('distribution.store') }}" method="POST" id="assignStock" enctype="multipart/form-data"> @csrf
        <div class="cards">
            <div class="cardsBody pb-0">

                <div class="row">

                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label for="type" class="c-gr f-500 f-16 w-100 mb-2">Distribution Type :
                                <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="type" class="select2-hidden-accessible select2" data-placeholder="--- Select a Type ---">
                                @forelse($types as $id => $type)
                                @if($loop->first)
                                <option value="" selected> --- Select a Distribution Type --- </option>
                                @endif
                                <option value="{{ $id }}">{{ $type }}</option>
                                @empty
                                <option value="" selected> --- No Distribution Type Available --- </option>
                                @endforelse
                            </select>
                            @if ($errors->has('supplier'))
                                <span class="text-danger d-block">{{ $errors->first('supplier') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="container-for-blade">

                    </div>
                </div>



                @if($errors->any())
                <div class="alert alert-danger removable-alert" role="alert">
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
                <a href="{{ route('distribution.index') }}">
                    <button type="button" class="btn-default f-500 f-14">Cancel</button>
                </a>
                <button type="submit" class="btn-primary f-500 f-14">Save</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
<script>

var thisDisType = null;
var fromDriverId = null;
var lastElementIndex = 0;
var driversHtml = '';

var drivers = {!! json_encode($drivers) !!};
driversHtml = `<option value="" selected> --- Select a Driver --- </option>`;

for (key in drivers) {
    driversHtml += `<option value="${key}"> ${drivers[key]} </option>`;
}

$(document).ready(function() {

    $('#type').on('change', function () {
        let type = $(this).val();

        fromDriverId = null;
        lastElementIndex = 0;

        if (type !== '' && type !== null && type >= 1 && type <= 3) {
            thisDisType = type;

            $.ajax({
                url: "{{ route('get-blade-for-distribution') }}",
                type: 'POST',
                data: {
                    type : type
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success: function (response) {
                    if (response.status) {
                        $('.container-for-blade').html(response.html);
                    } else {
                        $('.container-for-blade').html('');
                    }
                },
                complete: function (response) {
                    $('[data-toggle="tooltip"]').tooltip();
                    $('body').find('.LoaderSec').addClass('d-none');
                    if (response.responseJSON.status) {
                        $('.select2').each(function() {
                            $(this).select2({
                                width: '100%',
                                allowClear: true,
                            }).on("load", function(e) {
                                $(this).prop('tabindex',0);
                            }).trigger('load');
                            $(this).css('width', '100%');
                        });
                        makeAjaxSelect2('#product-0');
                    }
                }
            });
        } else {
            $('.container-for-blade').html('');
        }

    });

    $.validator.addMethod('diffDriver', function (value, element) {
        let bool = true;
        let thisInd = $(element).data('indexid');

        if (value !== '' && value !== null && value == $(`#driver-${thisInd}`).val()) {
            bool = false;
        }

        return bool;
    }, 'You can\'t assign stock to self(driver).');

    $.validator.addMethod('maxStock', function (value, element) {
        let bool = true;
        let validatorThisIndex = $(element).data('indexid');
        let validatableProduct = $(`#product-${validatorThisIndex}`);

        if (validatableProduct.length > 0) {
            let maxStock = $('option:selected', validatableProduct).attr('data-stock');
            if (exists(maxStock)) {
                if (parseFloat(value) > parseFloat(maxStock)) {
                    return false;
                }
            }
        }

        return bool;
    }, function (result, element) {
        let validatorThisIndex = $(element).data('indexid');
        let validatableProduct = $(`#product-${validatorThisIndex}`);
        let maxStock = $('option:selected', validatableProduct).attr('data-stock');

        if (result) {
            if (thisDisType == 1) {
                return `${maxStock} quantity of this product are available in storage`;
            } else {
                return `Driver has ${maxStock} quantity of this product are available`;
            }
        }

        return "Select a product.";
    });

    $.validator.addMethod("fileType", function(value, element, param) {
        var fileTypes = param.split('|');
        var files = element.files;
        for (var i = 0; i < files.length; i++) {
            var extension = files[i].name.split('.').pop().toLowerCase();
            if ($.inArray(extension, fileTypes) === -1) {
                return false;
            }
        }
        return true;
    }, "Only .png, .jpg, .jpeg and .pdf extensions supported");

    $.validator.addMethod("maxFiles", function(value, element, param) {
        return element.files.length <= param;
    }, "Maximum 10 files can be uploaded at a time.");

    $.validator.addMethod("fileSizeLimit", function(value, element, param) {
        var totalSize = 0;
        var files = element.files;
        for (var i = 0; i < files.length; i++) {
            totalSize += files[i].size;
        }
        return totalSize <= param;
    }, "Total file size must not exceed 20 MB.");

    $('#assignStock').validate({
        rules: {
            'type': {
                required: true
            },
            'docs[]': {
                fileType: "png|jpg|jpeg|pdf",
                maxFiles: 10,
                fileSizeLimit: (10 * 1024 * 1024) * 2
            },
            'product[0]': {
                required: true
            },
            'driver[0]': {
                required: true
            },
            'receiver[0]': {
                required: true
            },
            'quantity[0]': {
                required: true,
                digits: true,
                min: 1,
                maxStock: true
            },
            'from_driver[0]' : {
                required: true,
                diffDriver: true
            }
        },
        messages: {
            'type': {
                required: "Select a type."
            },
            'product[0]': {
                required: "Select a product."
            },
            'driver[0]': {
                required: "Select a driver."
            },
            'receiver[0]': {
                required: "Select a receiver driver."
            },
            'quantity[0]': {
                required: "Enter quantity",
                digits: "Enter valid format.",
                min: "Quantity can\'t be less than 1."
            },
            'from_driver[0]': {
                required: "Select a driver."
            },
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent("div"));
        }
    });

    var makeAjaxSelect2 = (el) => {

        $(el).select2({
            width: '100%',
            minimumInputLength: 1,
            allowClear: true,
            ajax: {
                url: "{{ route('getProducts') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                     var selectedDriver = $(el).data('indexid');
                     var typ = 'driver';

                     if (thisDisType == 2) {
                         typ = 'from-driver';
                     }

                     var selectedDriver = $(`#${typ}-${selectedDriver}`).val();

                    return {
                        searchQuery: params.term,
                        driver: selectedDriver ? selectedDriver : fromDriverId,
                        type: thisDisType
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                id: item.id,
                                text: item.text,
                                stock: item.stock
                            };
                        })
                    };
                },
                cache: true
            },
            templateResult: function (data) {
                if (data.loading) {
                    return data.text;
                }

                var $result = $('<span></span>');
                $result.text(data.text);
                $result.attr('data-stock', data.stock);
                return $result;
            },
            templateSelection: function (container) {
                $(container.element).attr("data-stock", container.stock);
                return container.text;
            }
        });
    }

    $(document).on('click', '.addNewRow', function (event) {

        cloned = $('.upsertable').find('tr').eq(0).clone();
        lastElementIndex++;

        cloned.find('.removable-from-driver').empty().append(`<select data-indexid="${lastElementIndex}" name="from_driver[${lastElementIndex}]" id="from-driver-${lastElementIndex}" class="select2 select2-hidden-accessible m-from-driver" style="width:100%" data-placeholder="Select a Driver"> ${driversHtml} </select> `);
        cloned.find('.m-from-driver').select2({
            // width: '100%',
            allowClear: true
        });

        cloned.find('.removable-driver').empty().append(`<select data-indexid="${lastElementIndex}" name="driver[${lastElementIndex}]" id="driver-${lastElementIndex}" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver"> ${driversHtml} </select> `);
        cloned.find('.m-driver').select2({
            // width: '100%',
            allowClear: true
        });

        cloned.find('.removable-product').empty().append(`<select data-indexid="${lastElementIndex}" name="product[${lastElementIndex}]" id="product-${lastElementIndex}" class="product2 select2-hidden-accessible m-product" style="width:100%" data-placeholder="Select a Product"> </select> `);
        makeAjaxSelect2(cloned.find('.m-product'));

        cloned.find('.m-quantity').attr('id', `quantity-${lastElementIndex}`).attr('data-indexid', lastElementIndex).attr('name', `quantity[${lastElementIndex}]`).val(null);

        cloned.find('label.error').remove();
        $('.upsertable').append(cloned.get(0));

        cloned.find('.m-from-driver').rules('add', {
            required: true,
            diffDriver: true,
            messages: {
                required: "Select a driver."
            }
        });

        cloned.find('.m-driver').rules('add', {
            required: true,
            messages: {
                required: "Select a driver."
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
            maxStock: true,
            messages: {
                required: "Enter quantity.",
                digits: "Enter valid format.",
                min: "Quantity can\'t be less than 1.",
            }
        });

    });

    $(document).on('click', '.removeRow', function(event) {
        if ($('.upsertable tr').length > 1) {
            $(this).closest("tr").remove();
        }

        let iid = $(this).parent().parent().prev().find('.m-quantity').data('indexid');

        if (typeof iid !== 'undefined' && iid !== '' && iid !== null) {
            calculateAmount(iid);
        }
    });

    $(document).on('change', '.m-product', function (event) {
        let that = $(this);
        let indexId = $(this).data('indexid');

        let thisProductId = $(this).val();
        let thisDriverId = $(`#driver-${indexId}`).val();

        if (thisDisType != 2) {
            $('.m-product').not(this).each(function (index, element) {
                if ($(element).val() !== null && thisProductId == $(element).val()) {
                    indexIdForDriver = $(element).data('indexid');

                    if ($(`#driver-${indexIdForDriver}`).val() !== '' && $(`#driver-${indexIdForDriver}`).val() !== null && thisDriverId == $(`#driver-${indexIdForDriver}`).val()) {
                        $(that).val(null).trigger('change');
                        Swal.fire('Warning', 'Product is already selected with this driver.', 'warning');
                        return false;
                    }
                }
            });
        }

    });

    $(document).on('select2:unselecting', '.m-product', function (e) {
        $(this).empty();
    });

    $(document).on('change', '.m-driver', function (event) {
        let that = $(this);
        let indexId = $(this).data('indexid');

        let thisDriverId = $(this).val();
        let thisProductId = $(`#product-${indexId}`).val();

        if (thisDisType == 3) {
            $(`#product-${indexId}`).val(null).trigger('change');
            $(`#product-${indexId}`).empty();
        }

        if (thisDriverId !== '0' && thisDriverId !== '' && thisDriverId !== null) {
            fromDriverId = thisDriverId;

            if (thisDisType != 2) {
                $('.m-driver').not(this).each(function (index, element) {
                    if ($(element).val() !== null && thisDriverId == $(element).val()) {
                        indexIdForProduct = $(element).data('indexid');

                        if ($(`#product-${indexIdForProduct}`).val() !== null && thisProductId == $(`#product-${indexIdForProduct}`).val()) {
                            $(that).val(null).trigger('change');
                            Swal.fire('Warning', 'Driver is already selected with this product.', 'warning');
                            return false;
                        }
                    }
                });
            }


        } else {
            fromDriverId = null;
        }
    });

    $(document).on('change', '.m-quantity', function (event) {
        calculateAmount($(this).data('indexid'));
    });

    $(document).on('change', '.m-from-driver', function (event) {
        let indexId = $(this).data('indexid');
        let thisDriver = $(this).val();

        if (thisDriver !== '0' && thisDriver !== '' && thisDriver !== null) {
            fromDriverId = thisDriver;
        } else {
            fromDriverId = null;
        }

        $(`#product-${indexId}`).val(null).trigger('change');
        $(`#product-${indexId}`).empty();
    });

    var calculateAmount = (indexId = 0) => {
        let quantity = $(`#quantity-${indexId}`).val();

        if (isNaN(quantity) || quantity == '') {
            quantity = 0;
        }

        let total = quantity;

        /** Final Total for Each Row **/
        let mtQuantity = 0;

        $('.upsertable > tr').each(function (index, element) {
            let tempQuantity = $(this).find('.m-quantity').val();

            if (isNaN(tempQuantity) || tempQuantity == '') {
                tempQuantity = 0;
            }

            mtQuantity += parseInt(tempQuantity);
        });

        $('.mt-quantity').val(mtQuantity);

        /** Final Total for Each Row **/
    }

    setTimeout(() => {
        $('.removable-alert').remove();
    }, 5000);
});
</script>
@endsection
