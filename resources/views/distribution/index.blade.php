@extends('layouts.master')

@section('css')
<style>
    .inner-table-of-datatable > tbody > tr  > td {
        padding: 0!important;
    }

    .inner-table-of-datatable > tbody > tr  > td:nth-child(1) {
        width: 173px!important;
    }
    .inner-table-of-datatable > tbody > tr  > td:nth-child(2) {
        width: 140px!important;
    }
    .inner-table-of-datatable > tbody > tr  > td:nth-child(3) {
        width: 150px!important;
    }
    .inner-table-of-datatable > tbody > tr  > td:nth-child(4) {
        width: 146px!important;
    }

    .inner-table-of-datatable > tbody > tr:first-child {
        border: none!important;
    }
    .inner-table-of-datatable > tbody > tr:last-child {
        border: none!important;
    }

</style>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">Manage {{ $moduleName }}</h2>
    @permission("distribution.create")
    <a href="{{ route('distribution.create') }}" class="btn-primary f-500 f-14">
        <svg class="me-1" width="16" height="16" viewBox="0 0 16 16" fill="#ffffff" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.00008 13.3332V7.99984M8.00008 7.99984V2.6665M8.00008 7.99984H13.3334M8.00008 7.99984H2.66675" stroke="#ffffff" stroke-width="2" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="paint0_linear_1524_12120" x1="8.00008" y1="2.6665" x2="8.00008" y2="13.3332" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#ffffff"></stop>
                    <stop offset="1" stop-color="#ffffff"></stop>
                </linearGradient>
            </defs>
        </svg>
        Assign Stock
    </a>
    @endpermission
</div>
@endsection
<div class="cards">
    <div class="row m-0 filterColumn">

        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Type</label>
                <select name="filterType" id="filterType" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Driver ---">
                    <option value="" selected> --- Select a Type --- </option>
                    @forelse($types as $tid => $type)
                        <option value="{{ $tid }}"> {{ $type }} </option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Driver</label>
                <select name="filterDriver" id="filterDriver" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Driver ---">
                    <option value="" selected> --- Select a Driver --- </option>
                    @forelse($drivers as $did => $dname)
                        <option value="{{ $did }}"> {{ $dname }} </option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">From Date</label>
                <input readonly type="text" id="filterFrom" name="filterFrom"
                     class="form-control"
                    placeholder="From date">
            </div>
        </div>

        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">To Date</label>
                <input readonly type="text" id="filterTo" name="filterTo"
                     class="form-control"
                    placeholder="To date">
            </div>
        </div>

        <div class="col-md-4 col-sm-12 position-relative">
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
                <th>Type</th>
                <th>Date</th>
                <th>

                    <table style="width: 100%;">
                        <thead>
                            <tr>
                                <td> From Driver </td> <td> Product </td> <td> To Driver </td> <td> Quantity </td>
                            </tr>
                        </thead>
                    </table>

                </th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {

        $.fn.dataTable.ext.errMode = 'none';
        
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
                "url": "{{ route('distribution.index') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    filterType:function() {
                        return $("#filterType").val();
                    },
                    filterDriver:function() {
                        return $("#filterDriver").val();
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
                    data: 'type',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'created_at',
                },
                {
                    data: 'product',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                }
            ],
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        /* filter Datatable */
        $('body').on('change', '#filterFrom, #filterTo, #filterDriver, #filterType', function(e){
            var filterType = $('#filterType').val();
            var filterDriver = $('#filterDriver').val();
            var filterFrom = $('#filterFrom').val();
            var filterTo = $('#filterTo').val();

            if (filterType != '' || filterDriver != '' || filterFrom != '' || filterTo != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }
            ServerDataTable.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterType').val('').trigger('change');
            $('body').find('#filterDriver').val('').trigger('change');
            $('body').find('#filterFrom').val('').trigger('change');
            $('body').find('#filterTo').val('').trigger('change');
            ServerDataTable.ajax.reload();
        });

    });
</script>
@endsection