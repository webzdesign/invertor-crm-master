@extends('layouts.master')

@section('css')
<style>
.bg-success, .bg-success:hover {
    background: #269e0e!important;
}

.bg-error, .bg-error:hover {
    background: #dd2d20!important;        
}
</style>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">Manage {{ $moduleName }}</h2>
    @permission("sales-orders.create")
    <a href="{{ route('sales-orders.create') }}" class="btn-primary f-500 f-14">
        <svg class="me-1" width="16" height="16" viewBox="0 0 16 16" fill="#ffffff" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.00008 13.3332V7.99984M8.00008 7.99984V2.6665M8.00008 7.99984H13.3334M8.00008 7.99984H2.66675" stroke="#ffffff" stroke-width="2" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="paint0_linear_1524_12120" x1="8.00008" y1="2.6665" x2="8.00008" y2="13.3332" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#ffffff"></stop>
                    <stop offset="1" stop-color="#ffffff"></stop>
                </linearGradient>
            </defs>
        </svg>
        Add Sales Order
    </a>
    @endpermission
</div>
@endsection
<div class="cards">
    <div class="row m-0 filterColumn">

        @if(in_array(1, User::getUserRoles()))
        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Seller</label>
                <select name="filterSeller" id="filterSeller" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Seller ---">
                    @forelse($sellers as $sid => $sname)
                    @if($loop->first)
                    <option value="" selected> --- Select a Seller --- </option>
                    @endif
                    <option value="{{ $sid }}">{{ $sname }}</option>
                    @empty
                    <option value="" selected> --- No Seller Available --- </option>
                    @endforelse
                </select>
            </div>
        </div>
        @endif

        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">From Date</label>
                <input readonly type="text" id="filterFrom" name="filterFrom"
                     class="form-control"
                    placeholder="From date">
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">To Date</label>
                <input readonly type="text" id="filterTo" name="filterTo"
                     class="form-control"
                    placeholder="To date">
            </div>
        </div>

        <div class="col-xl-3 col-sm-4 position-relative">
            <div class="form-group mb-0">
                <label class="c-gr f-500 f-14 w-100 mb-1 d-none-500">&nbsp;</label>
                <button class="btn-default f-500 f-14 clearData" style="display:none;"><i class="fa fa-remove" aria-hidden="true"></i> Clear filters</button>
            </div>
        </div>
    </div>
    <table class="datatables-po table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th>Sr No.</th>
                <th>Order No.</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Order Amount</th>
                @if(in_array(1, User::getUserRoles()) || in_array(2, User::getUserRoles()) || in_array(6, User::getUserRoles()) || in_array(3, User::getUserRoles()))
                <th>Added By</th>
                @endif
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

@include('so.modal.confirm-order')

@endsection

@section('script')
<script>
    $(document).ready(function() {

        $('#filterFrom').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom"
        }).on('changeDate', function(selected) {
            var minDate = new Date(selected.date.valueOf());
            $('#filterTo').datepicker('setStartDate', minDate);
        });

        $('#filterTo').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom"
        }).on('changeDate', function(selected) {
            var maxDate = new Date(selected.date.valueOf());
            $('#filterFrom').datepicker('setEndDate', maxDate);
        });

        var ServerDataTable = $('.datatables-po').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here"
            },
            processing: true,
            serverSide: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here"
            },
            "dom":"<'filterHeader d-block-500 cardsHeader'l<'#filterInput'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('sales-orders.index') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    filterSeller:function() {
                        return $("#filterSeller").val();
                    },
                    filterFrom: function () {
                        return $('#filterFrom').val();
                    },
                    filterTo: function () {
                        return $('#filterTo').val();
                    }
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'order_no',
                },
                {
                    data: 'product',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'quantity',
                },
                {
                    data: 'total',
                },
                @if(in_array(1, User::getUserRoles()) || in_array(2, User::getUserRoles()) || in_array(6, User::getUserRoles()) || in_array(3, User::getUserRoles()))
                {
                    data: 'addedby.name',
                    orderable: false,
                    searchable: false,
                },
                @endif
                {
                    data: 'option',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function () {
                $('#validateDriver').validate({
                    rules: {
                        driver : {
                            required: true
                        }
                    },
                    messages: {
                        driver : {
                            required: "Select a driver to assign."
                        }
                    },
                    errorPlacement: function(error, element) {
                        if ($(element).parent('form').next().hasClass('error')) {
                            $(element).parent('form').next().remove();
                        }
                        error.insertAfter(element.parent('form'));
                    }
                });

                $('.driver-selection').select2({
                    width: '80%',
                    allowClear: true,
                });

                $(document).on('change', '.driver-selection', function() {
                    $('#driver-error').remove();
                });

                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        /* filter Datatable */
        $('body').on('change', '#filterSeller, #filterFrom, #filterTo', function(e){
            var filterSeller = $(this).val();
            var filterFrom = $(this).val();
            var filterTo = $(this).val();

            if (filterSeller != '' || filterFrom != '' || filterTo != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }
            ServerDataTable.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterFrom').val('').trigger('change');
            $('body').find('#filterTo').val('').trigger('change');
            $('body').find('#filterSeller').val('').trigger('change');
            ServerDataTable.ajax.reload();
        });

        $(document).on('click', '#driver-approve-the-order', function () {
            let that = this;

            Swal.fire({
                title: 'Accept order?',
                text: 'This process is irreversible. are you sure?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value) {

                    $.ajax({
                        url: "{{ route('accept-the-order-from-driver') }}",
                        type: 'POST',
                        data: {
                            id : $(that).attr('data-oid')
                        },
                        success: function(response) {
                            if (response.status) {
                                ServerDataTable.ajax.reload();
                                Swal.fire('', 'Order accepted successfully.', 'success');
                            }
                        }
                    });

                }
            });            
        })

        $(document).on('click', '#driver-reject-the-order', function () {
            let that = this;

            Swal.fire({
                title: 'Reject order?',
                text: 'This process is irreversible. are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value) {

                    $.ajax({
                        url: "{{ route('reject-the-order-from-driver') }}",
                        type: 'POST',
                        data: {
                            id : $(that).attr('data-oid')
                        },
                        success: function(response) {
                            if (response.status) {
                                ServerDataTable.ajax.reload();
                                Swal.fire('', 'Order rejected successfully.', 'success');
                            }
                        }
                    });

                }
            });            
        })

        $(document).on('click', '.close-order', function () {
            let oId = $(this).attr('data-oid');
            let title = $(this).attr('data-title');

            if (isNumeric(oId)) {
                $('#close-order').find('#modal-title').text(title);
                $('#closing-order-id').val(oId);
                $('#close-order').modal('show');
            }
        });

        $(document).on('hidden.bs.modal', '#close-order', function (event) {
            if (event.namespace == 'bs.modal') {
                $('.document-field').hide();
                $('.amount-field').show();
                $('#closing-order-amount-form')[0].reset();
                $('#close-order-sbmt-btn').text('Next');
                $('#order-closing-amount-error').remove();
            }
        })

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
        }, "Only .png, .jpg, and .jpeg extensions supported");

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
        }, "Total file size must not exceed 20 MB");

        $('#closing-order-amount-form').validate({
            ignore: ":hidden",
            rules: {
                amount: {
                    required: true,
                    number: true,
                    min: 1
                },
                'file[]': {
                    required: true,
                    fileType: 'jpeg|png|jpg',
                    maxFiles: 10,
                    fileSizeLimit: (10 * 1024 * 1024) * 2
                }
            },
            messages: {
                amount: {
                    required: "Enter the amount",
                    number: "Enter valid amount",
                    min: "Enter valid amount."
                },
                'file[]': {
                    required: "Upload atleast an image proof"
                }
            }
        });

        $(document).on('submit', '#closing-order-amount-form', function (e) {
            e.preventDefault();

            if ($(this).find('.amount-field').is(':visible')) {
                $.ajax({
                    url: "{{ route('check-so-price') }}",
                    type: "POST",
                    data: $(this).serializeArray(),
                    beforeSend: function () {
                        $('button[type="submit"]').attr('disabled', true);
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.status) {
                            if (response.next) {
                                $('#close-order-sbmt-btn').text('Save');
                                $('.document-field').show();
                                $('.amount-field').hide();
                            } else {
                                Swal.fire('', 'Price added for order successfully.', 'success');
                                $('#close-order').modal('hide');
                                ServerDataTable.ajax.reload();
                            }
                        }
                    },
                    complete: function () {
                        $('button[type="submit"]').attr('disabled', false);
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            } else {

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('price-unmatched') }}",
                    type: "POST",
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    data: formData,
                    beforeSend: function () {
                        $('button[type="submit"]').attr('disabled', true);
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.status) {
                            Swal.fire('', 'Proof for price change uploaded successfully.', 'success');
                            $('#close-order').modal('hide');
                            ServerDataTable.ajax.reload();
                        } else {
                            Swal.fire('', response.messages, 'error');
                            $('#close-order').modal('hide');
                            ServerDataTable.ajax.reload();
                        }
                    },
                    error: function () {
                        Swal.fire('', 'Something went wrong. please try again later.', 'error');
                        $('#close-order').modal('hide');
                        ServerDataTable.ajax.reload();
                    },
                    complete: function () {
                        $('button[type="submit"]').attr('disabled', false);
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });                                             
            }
        });

    });
</script>
@endsection