@extends('layouts.master')

@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">Manage {{ $moduleName }}</h2>
    @permission("categories.create")
    <a href="{{ route('categories.create') }}" class="btn-primary f-500 f-14">
        <svg class="me-1" width="16" height="16" viewBox="0 0 16 16" fill="#ffffff" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.00008 13.3332V7.99984M8.00008 7.99984V2.6665M8.00008 7.99984H13.3334M8.00008 7.99984H2.66675" stroke="#ffffff" stroke-width="2" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="paint0_linear_1524_12120" x1="8.00008" y1="2.6665" x2="8.00008" y2="13.3332" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#ffffff"></stop>
                    <stop offset="1" stop-color="#ffffff"></stop>
                </linearGradient>
            </defs>
        </svg>
        Add Category
    </a>
    @endpermission
</div>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="cards">
    <div class="row m-0 filterColumn">
        <div class="col-xl-2 col-sm-4 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Status</label>
                <select name="filterStatus" id="filterStatus" class="select2 select2-hidden-accessible" data-placeholder="--- Select Status ---">
                    <option value="" selected> --- Select Status --- </option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        <div class="col-xl-3 col-sm-4 position-relative">
            <div class="form-group mb-0">
                <label class="c-gr f-500 f-14 w-100 mb-1 d-none-500">&nbsp;</label>
                <button class="btn-default f-500 f-14 clearData" style="display:none;"><i class="fa fa-remove" aria-hidden="true"></i> Clear filters</button>
            </div>
        </div>
    </div>

    <table class="datatable-users table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th>Sr No.</th>
                <th>Name</th>
                <th>Status</th>
                <th>Added By</th>
                <th>Updated By</th>
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

        var ServerDataTable = $('.datatable-users').DataTable({
            pageLength : 50,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",

            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('categories.index') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    filterStatus:function() {
                        return $("#filterStatus").val();
                    },
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'name',
                },
                {
                    data: 'status',
                },
                {
                    data: 'addedby.name',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'updatedby.name',
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

        $('.datepicker').datetimepicker({
            format: "DD/MM/YYYY",
            timeZone: ''
        });

        /* filter Datatable */
        $('body').on('change', '#filterStatus', function(e){
            var filterStatus = $('body').find('#filterStatus').val();

            if (filterStatus != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }

            ServerDataTable.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterStatus').val('').trigger('change');
            ServerDataTable.ajax.reload();
        });
    });
</script>
@endsection
