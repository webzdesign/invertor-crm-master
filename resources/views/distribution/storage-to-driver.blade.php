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

                        <th class="block-a">Product <span class="text-danger">*</span> </th>

                        <th class="block-b"> Driver <span class="text-danger">*</span> </th>

                        <th class="block-c">Quantity <span class="text-danger">*</span> </th>

                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody class="upsertable">
                    <tr>

                        <td class="block-a">
                            <div style="min-width: 200px;width: 100%" class="removable-product">
                                <select name="product[0]" data-indexid="0" id="product-0" class="product2 select2-hidden-accessible m-product" style="width:100%" data-placeholder="Select a Product">
                                    @forelse($products as $did => $dname)
                                        @if($loop->first)
                                        <option value="" selected> --- Select a Product --- </option>
                                        @endif
                                        <option value="{{ $did }}">{{ $dname }}</option>
                                        @empty
                                        <option value="" selected> --- No Product Available --- </option>
                                    @endforelse
                                </select>
                            </div>
                        </td>

                        <td class="block-b">
                            <div style="min-width: 200px;width: 100%" class="removable-driver">
                                <select name="driver[0]" data-indexid="0" id="driver-0" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver">
                                    @forelse($drivers as $did => $dname)
                                        @if($loop->first)
                                        <option value="" selected> --- Select a Driver --- </option>
                                        @endif
                                        <option value="{{ $did }}">{{ $dname }}</option>
                                        @empty
                                        <option value="" selected> --- No Driver Available --- </option>
                                    @endforelse
                                </select>
                            </div>
                        </td>

                        <td class="block-c">
                            <div style="min-width: 200px;">
                                <input type="number" data-indexid="0" name="quantity[0]" id="quantity-0" class="form-control m-quantity" style="background:#ffffff">
                            </div>
                        </td>

                        <td style="width:100px;">
                            <div style="min-width: 100px;">
                                <button type="button" class="btn btn-primary btn-sm addNewRow">+</button>
                                <button type="button" class="btn btn-danger btn-sm removeRow" tabindex="-1">-</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td class="block-a"></td>
                        <td class="block-b"></td>
                        <td class="block-c"> 
                            <div style="min-width: 200px;">
                                <input type="number" class="form-control mt-quantity" style="background:#efefef" value="0" readonly>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    var lastElementIndex = 0;
    var driversHtml = '';

    var drivers = {!! json_encode($drivers) !!};
    driversHtml = `<option value="" selected> --- Select a Driver --- </option>`;

    for (key in drivers) {
        driversHtml += `<option value="${key}"> ${drivers[key]} </option>`;
    }

$(document).ready(function () {

    $('.select2').each(function() {
        $(this).select2({
            width: '100%',
            allowClear: true,
        }).on("load", function(e) { 
            $(this).prop('tabindex',0);
        }).trigger('load');
        $(this).css('width', '100%');
    });

    var makeAjaxSelect2 = (el) => {
        
        $(el).select2({
            width: '100%',
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: "{{ route('getProducts') }}",
                type: "POST",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        searchQuery: params.term,
                        type: 1
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item, id) {
                            return {
                                text: item,
                                id: id
                            }
                        })
                    };
                },
                cache: true
            }
        });
    }

    makeAjaxSelect2('#product-0');

    $(document).on('click', '.addNewRow', function (event) {

        cloned = $('.upsertable').find('tr').eq(0).clone();
        lastElementIndex++;
        
        cloned.find('.removable-driver').empty().append(`<select data-indexid="${lastElementIndex}" name="driver[${lastElementIndex}]" id="driver-${lastElementIndex}" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver"> ${driversHtml} </select> `);
        cloned.find('.m-driver').select2({
            width: '100%',
            allowClear: true
        });

        cloned.find('.removable-product').empty().append(`<select data-indexid="${lastElementIndex}" name="product[${lastElementIndex}]" id="product-${lastElementIndex}" class="product2 select2-hidden-accessible m-product" style="width:100%" data-placeholder="Select a Product"> </select> `);
        makeAjaxSelect2(cloned.find('.m-product'));

        cloned.find('.m-quantity').attr('id', `quantity-${lastElementIndex}`).attr('data-indexid', lastElementIndex).attr('name', `quantity[${lastElementIndex}]`).val(null);

        cloned.find('label.error').remove();
        $('.upsertable').append(cloned.get(0));

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

        $('.m-product').not(this).each(function (index, element) {
            if ($(element).val() !== null && thisProductId == $(element).val()) {
                indexIdForDriver = $(element).data('indexid');

                if ($(`#driver-${indexIdForDriver}`).val() !== null && thisDriverId == $(`#driver-${indexIdForDriver}`).val()) {
                    $(that).val(null).trigger('change');
                    Swal.fire('Warning', 'Product is already selected with this driver.', 'warning');
                    return false;                    
                }
            }
        });


    });

    $(document).on('change', '.m-driver', function (event) {
        let that = $(this);
        let indexId = $(this).data('indexid');
        
        let thisDriverId = $(this).val();
        let thisProductId = $(`#product-${indexId}`).val();

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


    });

    $(document).on('change', '.m-quantity', function (event) {
        calculateAmount($(this).data('indexid'));
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

});
</script>