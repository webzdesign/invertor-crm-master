@extends('layouts.master')

@section('css')
<link href="{{ asset('assets/css/dataTables.bootstrap5.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/responsive.bootstrap5.css') }}" rel="stylesheet">
<style>
    .color-blue {
        color: #0057a9;
        cursor: pointer;
    }
    .select2-selection__clear {
        display: none!important;
    }
    .no-border {
        border: none;
    }
    .modal-padding {
        padding: 10px 14px;
    }
    .status-opener {
        background: #fff;
        border: 1px solid #ccc;
        height: 20px;
        width: 20px;
        font-size: 13px;
        border-radius: 2px;
    }
    .status-label{
        padding: 0 6px;
        border-radius: 4px;
    }
    .status-opener i{
        position: relative;
        top: 1px;
        left: 1px;
    }
    .status-opener:hover{
        background: #f0f0f0;
    }
    .status-main:hover .status-opener{
        visibility: visible;
    }
    .overflowTable .odd td:nth-child(3){
        width: 260px;
        min-width: 260px;
    }
    .overflowTable .odd td:nth-child(4){
        width: 150px;
        min-width: 150px;
    }
    .overflowTable .odd td:nth-child(2){
        width: 150px;
        min-width: 150px;
    }
    .status-modal{
        position: absolute;
        top: 0;
        left: 0;
        box-shadow: 0 5px 10px 0 rgba(0,0,0, .1);
        box-sizing: border-box;
        padding: 12px;
        border: 1px solid #e8eaeb;
        width: 100%;
        background: #fff;
        z-index: 9;
    }
    .status-dropdown-toggle{
        border: 1px solid rgba(146, 152, 155, 0.4);
        width: 100%;
        text-align: left;
        border-radius: 3px;
        padding: 4px 6px;
        background: white;
    }
    .status-dropdown-menu{
        border: 1px solid rgba(146, 152, 155, 0.4);
        width: 100%;
        text-align: left;
        border-radius: 3px;
        background: white;
        position: absolute;
        top: 0;
        z-index: 1;
        max-height: 185px;
        overflow: auto;
    }
    .status-dropdown-menu::-webkit-scrollbar-track{
        background-color: #ffffff;
    }

    .status-dropdown-menu::-webkit-scrollbar{
        width: 6px;
        background-color: #ffffff;
    }

    .status-dropdown-menu::-webkit-scrollbar-thumb{
        background-color: #c7c7c7;
    }
    .status-dropdown-menu div.cursor-pointer{
        padding: 3px 6px;
        cursor: pointer;
    }
    .dataTables_wrapper .col-sm-12{
        overflow: inherit;
    }
    .btn-primary:disabled:hover{
        background: #E9EAED !important;
    }
    .-z-1{
        z-index: -1;
    }

    div.dt-processing > div:last-child{
        display:none;
    }
    div.dt-processing {
        margin-left: 0;
        margin-top: 0;
        border: 0;
        background: transparent;
    }
    @media (min-width:992px) {
        .status-opener {
            /* visibility: hidden; */
        }
    }

    .bg-success, .bg-success:hover {
        background: #269e0e!important;
    }

    .bg-error, .bg-error:hover {
        background: #dd2d20!important;
    }
    .closedwin-statusupdate {
        display: none;
    }
    span.drivertitle {
        cursor: pointer;
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

        @if(in_array(1, User::getUserRoles()) || in_array(2, User::getUserRoles()) || in_array(6, User::getUserRoles()))
        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Driver</label>
                <select name="filterDriver" id="filterDriver" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Driver ---">
                    @forelse($drivers as $dname)
                    @if($loop->first)
                    <option value="" selected> --- Select a Driver --- </option>
                    @endif
                    <option value="{{ $dname['id'] }}">{{ $dname['name'] }}</option>
                    @empty
                    <option value="" selected> --- No Driver Available --- </option>
                    @endforelse
                </select>
            </div>
        </div>
        @endif

        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Status</label>
                <select name="filterStatus[]" id="filterStatus" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Status ---" multiple>
                    @forelse($statuses as $sid => $sname)
                    {{-- @if($loop->first)
                    <option value="" selected> --- Select a Status --- </option>
                    @endif --}}
                    <option value="{{ $sid }}">{{ $sname }}</option>
                    @empty
                    <option value="" selected> --- No Status Available --- </option>
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Product</label>
                <select name="filterProduct" id="filterProduct" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Product ---">
                    @forelse($products as $pid => $pname)
                    @if($loop->first)
                    <option value="" selected> --- Select a Product --- </option>
                    @endif
                    <option value="{{ $pid }}">{{ $pname }}</option>
                    @empty
                    <option value="" selected> --- No Product Available --- </option>
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">From Delivery Date</label>
                <input readonly type="text" id="filterFrom" name="filterFrom"
                     class="form-control"
                    placeholder="From date">
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">To Delivery Date</label>
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
                <th style="width: 5%!important;">Postal Code</th>
                <th>Order No.</th>
                <th>Product</th>
                <th style="width: 3%!important;">Quantity</th>
                <th style="width: 3%!important;">Order Amount</th>
                @if(in_array(1, User::getUserRoles()) || in_array(3, User::getUserRoles()))
                <th>Added By</th>
                @endif
                @if(in_array(1, User::getUserRoles()) || in_array(2, User::getUserRoles()) || in_array(6, User::getUserRoles()) || in_array(3, User::getUserRoles()))
                <th>Last Comment</th>
                @endif
                @if(in_array(1, User::getUserRoles()) || in_array(2, User::getUserRoles()) || in_array(6, User::getUserRoles()))
                <th style="width: 15%!important;">Allocated To</th>
                @endif
                <th style="width: 20%!important;">Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

@include('so.modal.confirm-order')

@include('so.modal.change-driver')

@include('sales-orders-status.modal.order-details')

@endsection

@section('script')
<script src="{{ asset('assets/js/dataTables.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.bootstrap5.js') }}"></script>
<script src="{{ asset('assets/js/dataTables.responsive.js') }}"></script>
<script src="{{ asset('assets/js/responsive.bootstrap5.js') }}"></script>
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
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here"
            },
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom":"<'filterHeader d-block-500 cardsHeader'l>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'<'float-end' p>>",
            pagingType: "simple_numbers",
            language: {
                paginate: {
                    previous: 'Previous',
                    next:     'Next'
                },
                aria: {
                    paginate: {
                        previous: 'Previous',
                        next:     'Next'
                    }
                }
            },
            ajax: {
                "url": "{{ route('sales-orders.index') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    filterSeller:function() {
                        return $("#filterSeller").val();
                    },
                    filterProduct:function() {
                        return $("#filterProduct").val();
                    },
                    filterDriver:function() {
                        return $("#filterDriver").val();
                    },
                    filterStatus:function() {
                        return $("#filterStatus").val();
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
                    data: 'postalcode',
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
                @if(in_array(1, User::getUserRoles()) || in_array(3, User::getUserRoles()))
                {
                    data: 'addedby.name',
                    orderable: false,
                    searchable: false,
                },
                @endif
                @if(in_array(1, User::getUserRoles()) || in_array(2, User::getUserRoles()) || in_array(6, User::getUserRoles()) || in_array(3, User::getUserRoles()))
                {
                    data: 'note',
                    orderable: false,
                    searchable: false,
                },
                @endif
                @if(in_array(1, User::getUserRoles()) || in_array(2, User::getUserRoles()) || in_array(6, User::getUserRoles()))
                {
                    data: 'allocated_to',
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
                    width: '60%',
                    allowClear: true,
                });

                $(document).on('change', '.driver-selection', function() {
                    $('#driver-error').remove();
                });

                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });


        /** Order History **/

        $(document).on('click', '.show-order-details', function(event) {

            let thisOrderId = $(this).attr('data-oid');
            let thisOrderTitle = $(this).attr('data-title');

            if (thisOrderId != '' && thisOrderId != null) {
                $.ajax({
                    url: "{{ route('order-detail-in-board') }}",
                    type: "POST",
                    data: {
                        id: thisOrderId
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#modal-title-1').text(thisOrderTitle);
                            $('#orderDetails').empty().html(response.view);
                            $('#order-details').modal('show');
                        }
                    }

                });
            }
        });


        $(document).on('click', '#toggle-status-trigger-list', function (event) {
            if ($('.actvt').hasClass('d-none')) {
                $('.actvt').removeClass('d-none')
                $(this).text('Show less');
            } else {
                $('.actvt').not('.show-first').addClass('d-none');
                $(this).text('Show All');
            }
        })

        $(document).on('click', '#toggle-task-trigger-list', function (event) {
            if ($('.actvt-at').hasClass('d-none')) {
                $('.actvt-at').removeClass('d-none')
                $(this).text('Show less');
            } else {
                $('.actvt-at').not('.show-first-at').addClass('d-none');
                $(this).text('Show All');
            }
        })

        $(document).on('click', '#toggle-change-user-trigger-list', function (event) {
            if ($('.actvt-cu').hasClass('d-none')) {
                $('.actvt-cu').removeClass('d-none')
                $(this).text('Show less');
            } else {
                $('.actvt-cu').not('.show-first-cu').addClass('d-none');
                $(this).text('Show All');
            }
        })

        $(document).on('click', '#toggle-history', function (event) {
            if ($('.hist').hasClass('d-none')) {
                $('.hist').removeClass('d-none')
                $(this).text('Show less');
            } else {
                $('.hist').not('.show-first-history').addClass('d-none');
                $(this).text('Show All');
            }
        })

        $(document).on('click', '.remove-task', function () {
            let id = $(this).attr('data-tid');
            let order = $(this).attr('data-oid');
            let element = $(this).parent().parent().parent();

            if (id !== '' && order !== '') {
                Swal.fire({
                    title: 'Are you sure want to delete task?',
                    text: "As that can be undone by doing reverse.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('remove-task') }}",
                            type: "POST",
                            data: {
                                id: id,
                                order: order
                            },
                            beforeSend: function () {
                                $('body').find('.LoaderSec').removeClass('d-none');
                            },
                            success: function (response) {
                                if (response.status) {
                                    Swal.fire('', response.message, 'success');
                                    $(element).remove();

                                    if (response.count > 0) {
                                        if (response.count <= 3) {
                                            $('#toggle-task-trigger-list').text('Show All');
                                        }

                                        if (!($('.actvt-at:eq(0)').hasClass('show-first-at') && $('.actvt-at:eq(1)').hasClass('show-first-at') && $('.actvt-at:eq(2)').hasClass('show-first-at'))) {
                                            if ($('.actvt-at').not('.show-first-at').first().hasClass('d-none')) {
                                                $('.actvt-at').not('.show-first-at').first().removeClass('d-none').addClass('show-first-at');
                                            } else if ($('.actvt-at').not('.show-first-at').first().length > 0) {
                                                $('.actvt-at').not('.show-first-at').first().addClass('show-first-at');
                                            }
                                        }
                                    } else {
                                        $('.task-trigger-activity-row').html(`<div class="activity py-2 f-13 border-bottom">No Activity to Show</div>`);
                                        $('#toggle-task-trigger-list').remove();
                                    }
                                } else {
                                    Swal.fire('', response.message, 'error');
                                }
                            },
                            complete: function () {
                                $('body').find('.LoaderSec').addClass('d-none');
                            }
                        });
                    }
                });
            }
        });

        $(document).on('click', '.edit-task', function () {
            $(this).addClass('d-none');
            $(this).parent().prev().find('.completion-content').attr('contenteditable', true)
            $(this).parent().prev().find('.completion-content').focus();
            $(this).parent().parent().next().removeClass('d-none');
        })

        $(document).on('click', '.hide-complete-task-textarea', function () {
            $(this).parent().addClass('d-none')
            $(this).parent().prev().find('.edit-task').removeClass('d-none');
            $(this).parent().prev().find('.completion-content').attr('contenteditable', false);
        })

        $(document).on('click', '.save-complete-task-textarea', function () {
            let description = $(this).parent().prev().find('.completion-content').text();
            let task = $(this).attr('data-taskid');
            let errorElement = $(this).parent().prev().find('.completion-content').next();
            let that = this;

            if (description.trim().length > 0 && task !== '') {
                if ($(this).parent().prev().find('.completion-content').next().hasClass('this-error')) {
                    $(this).parent().prev().find('.completion-content').next().text('');
                }

                $.ajax({
                    url: "{{ route('save-completion-description-for-task') }}",
                    type: "POST",
                    data: {
                        id: task,
                        text: description
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {

                        if (response.status) {
                            $(that).parent().addClass('d-none')
                            $(that).parent().prev().find('.edit-task').removeClass('d-none');
                            $(that).parent().prev().find('.completion-content').attr('contenteditable', false);
                        } else {
                            errorElement.text(response.message);
                        }

                    },
                    complete: function() {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });

            } else {
                if ($(this).parent().prev().find('.completion-content').next().hasClass('this-error')) {
                    $(this).parent().prev().find('.completion-content').next().text(`Description field can't be saved empty.`);
                }
            }

        })

        /** Order History **/

        /* filter Datatable */
        $('body').on('change', '#filterSeller, #filterFrom, #filterTo, #filterProduct, #filterStatus, #filterDriver', function(e){
            var thisValue = $(this).val();

            if (thisValue != '') {
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
            $('body').find('#filterProduct').val('').trigger('change');
            $('body').find('#filterStatus').val('').trigger('change');
            $('body').find('#filterDriver').val('').trigger('change');
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
                            } else {
                                Swal.fire('', response.message, 'error');
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

            let availableQty = $(this).attr('data-available');
            let wantedQty = $(this).attr('data-wanted');

            if (isNumeric(oId) && isNumeric(availableQty) && isNumeric(wantedQty)) {
                if (availableQty < wantedQty) {
                    Swal.fire('', "You don\'t have stock for this product.", 'error');
                } else {
                    $('#close-order').find('#modal-title').text(title);
                    $('#closing-order-id').val(oId);
                    $('#close-order').modal('show');
                }
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

        $('#closing-order-amount-form').validate({
            ignore: ":hidden",
            rules: {
                amount: {
                    required: true,
                    number: true,
                    min: 1
                },
                'file[]': {
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
                        } else {
                            Swal.fire('', response.message, 'error');
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

        $(document).on('click', '.refresh-dt', function () {
            ServerDataTable.ajax.reload();
        })

        function bindClickToHide(selector) {
            $(selector).on("click", function (event) {
                event.preventDefault();
                $(this).parent().fadeOut();
            });
        }

        $(document).on('click', '.dropdown-toggle', function() {
            var isHidden = $(this).parents(".button-dropdown").children(".dropdown-menu").is(":hidden");
            $(".button-dropdown .dropdown-menu").hide();
            $(".button-dropdown .dropdown-toggle").removeClass("active");

            if (isHidden) {
                $(this).parents(".button-dropdown").children(".dropdown-menu").toggle()
                    .parents(".button-dropdown")
                    .children(".dropdown-toggle").addClass("active");
            }

            $('.cmnt-er-lbl').addClass('d-none');
        });

        $(document).on('click', function() {
            var target = $(event.target);

            if (!target.parents().hasClass("button-dropdown")) {
                if ($('.dropdown-menu').is(':visible')) {
                    ServerDataTable.ajax.reload();
                }

                $(".button-dropdown .dropdown-menu").hide();
                $(".button-dropdown .dropdown-toggle").removeClass("active");
                //hide
            }

            if (!target.parents().hasClass("status-dropdown")) {
                $(".status-dropdown .status-dropdown-menu").hide();
                $(".status-dropdown .status-dropdown-toggle").removeClass("active");
            }
        });

        function bindClickToHideModal(selector) {
            $(selector).on("click", function (event) {
                event.preventDefault();
                $(this).parent().fadeOut();
            });
        }

        $(document).on('click', '.status-dropdown-toggle', function() {
            var isHidden = $(this).parents(".status-dropdown").children(".status-dropdown-menu").is(":hidden");
            $(".status-dropdown .status-dropdown-menu").hide();
            $(".status-dropdown .status-dropdown-toggle").removeClass("active");

            if (isHidden) {
                $(this).parents(".status-dropdown").children(".status-dropdown-menu").toggle()
                    .parents(".status-dropdown")
                    .children(".status-dropdown-toggle").addClass("active");
            }
        });

        $(document).on('click', '.status-dropdown-menu div', function() {
            var bgColor = rgbToHex($(this).css("background-color"));
            var text = $(this).text();
            var thisSid = $(this).data('sid');
            var thisOid = $(this).data('oid');
            let cwstatus = $(this).attr('data-cwstatus');

            if (thisSid == cwstatus) {
                $(this).closest('.status-modal').find('.closedwin-statusupdate').css('display', 'block');
            } else {
                $(this).closest('.status-modal').find('.closedwin-statusupdate').css('display', 'none');
            }

            var dropdownToggle = $(this).closest(".status-dropdown").find(".status-dropdown-toggle");
            var dropdownToggleText = $(this).closest(".status-dropdown").find(".status-dropdown-toggle").find("span");
            dropdownToggleText.text(text);

            dropdownToggle.css("background-color", bgColor);
            dropdownToggle.css("color", generateTextColor(bgColor));

            // Hide the dropdown menu and remove the active class
            $(this).parent().hide();
            dropdownToggle.removeClass("active");

            $(this).parent().parent().parent().find('.status-action-btn').find('.status-save-btn').removeAttr("disabled");

            if ($(this).data('isajax') === undefined) {
                $(this).parent().parent().parent().next().find('#save-status').attr('data-sid', thisSid)
                $(this).parent().prev().attr('data-sid', thisSid);
            } else if ($(this).data('isajax') == true) {
                $(this).parent().prev().attr('data-sid', thisSid);
                $(this).parent().prev().attr('data-oid', thisOid);
            }

        });

        $(document).on('click', '.hide-dropdown', function() {
            $('.dropdown-menu').hide();
        });

        $(document).on('change', '#cs-txtar', function () {
            $('.cmnt-er-lbl').addClass('d-none');
        });

        $(document).on('change', '#cs-fsp', function () {
            $('.fsp-er-lbl').text('');
            $('.fsp-er-lbl').addClass('d-none');
        });

        $(document).on('change', '#cs-pcp', function () {
            $('.pcp-er-lbl').text('');
            $('.pcp-er-lbl').addClass('d-none');
        });

        $(document).on('click', '.status-save-btn', function () {
            let el = $(this).parent().parent().find('.status-dropdown-toggle');
            let thisSid = $(el).attr('data-sid');
            let thisOrder = $(el).attr('data-oid');
            let lbl = $(this).parent().parent().prev().prev();
            let commentEle = $(this).parent().parent().find('textarea');
            let comment = $(this).parent().parent().find('textarea').val().trim();
            let errEle = $(this).parent().parent().find('.cmnt-er-lbl');
            let errEleFsp = $(this).parent().parent().find('.fsp-er-lbl');
            let errElePcp = $(this).parent().parent().find('.pcp-er-lbl');
            let proofElement = $(this).parent().parent().find('div.closedwin-statusupdate input#cs-pcp');
            let priceElement = $(this).parent().parent().find('div.closedwin-statusupdate input#cs-fsp');
            let cwstatus = $(this).attr('data-cwstatus');

            if (comment == null || comment == '') {
                $(errEle).removeClass('d-none');
                return false;
            } else {
                $(errEle).addClass('d-none');
            }

            let formData = new FormData();

            formData.append("status", thisSid);
            formData.append("order", thisOrder);
            formData.append("comment", comment);

            if (cwstatus == thisSid) {
                let priceChangeProof = $(proofElement).prop('files');
                let finalSalesPrice = $(priceElement).val();

                //Price input
                if (finalSalesPrice == null || finalSalesPrice == '') {
                    $(errEleFsp).text('Enter the amount.');
                    $(errEleFsp).removeClass('d-none');
                    return false;
                } else if (!isNumeric(finalSalesPrice)) {
                    $(errEleFsp).text('Enter valid amount.');
                    $(errEleFsp).removeClass('d-none');
                    return false;
                } else {
                    if (finalSalesPrice <= 0) {
                        $(errEleFsp).text('Amount must be greater than 0.');
                        $(errEleFsp).removeClass('d-none');
                        return false;
                    }
                }
                //Price input

                //file validation
                if (priceChangeProof.length > 0) {
                    var fileTypes = ['jpeg', 'png', 'jpg'];
                    for (var i = 0; i < priceChangeProof.length; i++) {
                        var extension = priceChangeProof[i].name.split('.').pop().toLowerCase();
                        if ($.inArray(extension, fileTypes) === -1) {
                            $(errElePcp).text('Only .png, .jpg, and .jpeg extensions supported.');
                            $(errElePcp).removeClass('d-none');
                            return false;
                        }
                    }

                    if ($(errElePcp).hasClass('d-none') && priceChangeProof.length > 10) {
                        $(errElePcp).text('Maximum 10 files can be uploaded.');
                        $(errElePcp).removeClass('d-none');
                        return false;
                    }

                    if ($(errElePcp).hasClass('d-none')) {

                        let totalSize = 0;
                        for (let i = 0; i < priceChangeProof.length; i++) {
                            totalSize += priceChangeProof[i].size;
                        }

                        if (totalSize > ((10 * 1024 * 1024) * 2)) {
                            $(errElePcp).text('Total file size must not exceed 20 MB');
                            $(errElePcp).removeClass('d-none');
                            return false;
                        }
                    }
                }
                //file validation

                formData.append("price", finalSalesPrice);

                if (priceChangeProof.length > 0) {
                    for (let i = 0; i < priceChangeProof.length; i++) {
                        formData.append(`proof[${i}]`, priceChangeProof[i])
                    }
                }
            }

            if (isNumeric(thisSid) && isNumeric(thisOrder) && comment !== '') {
                $.ajax({
                    url: "{{ route('sales-order-status-update-status') }}",
                    type: "POST",
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    data: formData,
                    beforeSend: function() {
                        $('button[type="submit"]').attr('disabled', true);
                        $('.status-save-btn').attr('disabled', true);
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.status) {
                            Swal.fire('', response.message, 'success');
                            ServerDataTable.ajax.reload();
                        } else {
                            Swal.fire('', response.message, 'error');
                        }

                        $(commentEle).val(null);
                        $(priceElement).val(null);
                        $(proofElement).val('');
                    },
                    complete: function() {
                        $('button[type="submit"]').attr('disabled', false);
                        $('.status-save-btn').attr('disabled', false);
                        $('body').find('.LoaderSec').addClass('d-none');
                    }

                });
            }
        })

        $('.change-driver-select2').select2({
            width: '100%',
            allowClear: true,
            dropdownParent: $('#change-driver').get(0)
        });

        $('#changeDriver').validate({
            rules: {
                driver_id: {
                    required: true
                }
            },
            messages: {
                driver_id: {
                    required: "Select a driver"
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent("div"));
            },
            submitHandler: function (form, event) {
                event.preventDefault();

                $.ajax({
                    url: "{{ url('change-driver') }}",
                    type: 'POST',
                    data: $(form).serializeArray(),
                    beforeSend: function () {
                        $('button[type="submit"]').attr('disabled', true);
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.status) {
                            Swal.fire('', response.message, 'success');
                        } else {
                            Swal.fire('', response.message, 'error');
                        }

                        $('#change-driver').modal('hide');
                        ServerDataTable.ajax.reload();
                    },
                    complete: function () {
                        $('button[type="submit"]').attr('disabled', false);
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            }
        });

        $(document).on('hidden.bs.modal', '#change-driver', function (e) {
            if (e.namespace == 'bs.modal') {
                $('#change-driver-picker-error').remove();
                $('#change-driver-picker').val(null).trigger('change');
            }
        });

        $(document).on('click', '.driver-change-modal-opener', function (event) {
            let title = $(this).attr('data-title');
            let deliveryBoy = $(this).attr('data-deliveryboy');
            let oId = $(this).attr('data-oid');

            $('#change-driver').modal('show');
            $('#modal-title-change-driver').text(title);
            $('#change-driver-picker').val(deliveryBoy).trigger('change');
            $('#change-driver-order-id').val(oId);

        });

    });
</script>
@endsection
