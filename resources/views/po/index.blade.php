@extends('layouts.master')

@section('content')
{{ Config::set('app.module', $moduleName) }}
@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">Manage {{ $moduleName }}</h2>
    @permission("purchase-orders.create")
    <a href="{{ route('purchase-orders.create') }}" class="btn-primary f-500 f-14">
        <svg class="me-1" width="16" height="16" viewBox="0 0 16 16" fill="#ffffff" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.00008 13.3332V7.99984M8.00008 7.99984V2.6665M8.00008 7.99984H13.3334M8.00008 7.99984H2.66675" stroke="#ffffff" stroke-width="2" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="paint0_linear_1524_12120" x1="8.00008" y1="2.6665" x2="8.00008" y2="13.3332" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#ffffff"></stop>
                    <stop offset="1" stop-color="#ffffff"></stop>
                </linearGradient>
            </defs>
        </svg>
        Add Storage
    </a>
    @endpermission
</div>
@endsection

<div class="importWrpr">

    <div class="cards mt-3">
        <table class="datatables-po1 table datatableMain" style="width: 100%!important;">
            <thead>
                <tr>
                    <th>Sr No.</th>
                    {{-- <th>Order No.</th> --}}
                    <th>Supplier</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    {{-- <th>Added By</th> --}}
                    {{-- <th>Updated By</th> --}}
                    {{-- <th>Action</th> --}}
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    
    <div class="cards mt-3">
        <div class="row m-0 filterColumn">
            <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
                <div class="form-group mb-0 mb-10-500">
                    <label class="c-gr f-500 f-14 w-100 mb-1">Select Supplier</label>
                    <select name="filterSupplier" id="filterSupplier" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Supplier ---">
                        <option value="" selected> --- Select a Supplier --- </option>
                        @forelse($suppliers as $id => $supplier)
                            <option value="{{ $id }}"> {{ $supplier }} </option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>
    
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
                    <th>Order Amount</th>
                    <th>Added By</th>
                    <th>Updated By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

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
            "dom":"<'filterHeader d-block-500 cardsHeader'l>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('purchase-orders.index') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    filterSupplier:function() {
                        return $("#filterSupplier").val();
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
                    data: 'total',
                },
                {
                    data: 'addedby.name',
                },
                {
                    data: 'updatedby.name',
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                }
            ],
        });

        var ServerDataTable1 = $('.datatables-po1').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here"
            },
            processing: true,
            serverSide: true,
            searching: false,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here"
            },
            "dom":"<'filterHeader d-block-500 cardsHeader'l>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('purchase-orders.data') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                // {
                //     data: 'order_no',
                //     orderable: false,
                //     searchable: false,
                // },
                {
                    data: 'supplier',
                },
                {
                    data: 'product',
                },
                {
                    data: 'quantity',
                },
                // {
                //     data: 'addedby',
                //     orderable: false,
                //     searchable: false,
                // },
                // {
                //     data: 'updatedby',
                //     orderable: false,
                //     searchable: false,
                // },
                // {
                //     data: 'action',
                //     orderable: false,
                //     searchable: false,
                // }
            ],
        });

        /* filter Datatable */
        $('body').on('change', '#filterFrom, #filterTo, #filterSupplier', function(e){
            var filterSupplier = $('#filterSupplier').val();
            var filterFrom = $('#filterFrom').val();
            var filterTo = $('#filterTo').val();

            if (filterSupplier != '' || filterFrom != '' || filterTo != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }

            ServerDataTable.ajax.reload();
            ServerDataTable1.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterSupplier').val('').trigger('change');
            $('body').find('#filterFrom').val('').trigger('change');
            $('body').find('#filterTo').val('').trigger('change');

            ServerDataTable.ajax.reload();
            ServerDataTable1.ajax.reload();
        });

    });
</script>
@endsection