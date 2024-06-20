@extends('layouts.master')


@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="cards">
    @if(in_array(1, User::getUserRoles()))
    <div class="row m-0 filterColumn">

        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Driver</label>
                <select name="filterDriver" id="filterDriver" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Driver ---">
                    @forelse($drivers as  $driver)
                    @if($loop->first)
                    <option value="" selected> --- Select a Driver --- </option>
                    @endif
                    <option value="{{ $driver->user_id }}">{{ $driver->user->name ?? 'Driver' }}</option>
                    @empty
                    <option value="" selected> --- No Driver Available --- </option>
                    @endforelse
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
    @endif

    <table class="datatable-users table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th>Sr No.</th>
                <th>Order No.</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Distance</th>
                <th>Added By</th>
                <th>Location</th>
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
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",

            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('orders-to-deliver') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    'driver' : function () {
                        return $('#filterDriver').val();
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
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'item',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'quantity',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'distance',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'added_by',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'location',
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
        $('body').on('change', '#filterStatus, #filterDriver', function(e){
            var filterStatus = $('body').find('#filterStatus').val();
            var filterDriver = $('body').find('#filterDriver').val();
            
            if (filterStatus != '' || filterDriver != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }

            ServerDataTable.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterStatus').val('').trigger('change');
            $('body').find('#filterDriver').val('').trigger('change');
            ServerDataTable.ajax.reload();
        });
    });
</script>
@endsection