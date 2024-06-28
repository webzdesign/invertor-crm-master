@extends('layouts.master')

@section('content')
{{ Config::set('app.module', $moduleName) }}
@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">{{ $moduleName }}</h2>
</div>
@endsection

<div class="cards">
    <div class="row m-0 filterColumn">

        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select User</label>
                <select name="filterUser" id="filterUser" class="select2 select2-hidden-accessible" data-placeholder="--- Select a User ---">
                    <option value="" selected> --- Select a User --- </option>
                    @forelse($users as $id => $name)
                        <option value="{{ $id }}"> {{ $name }} </option>
                    @empty
                    @endforelse
                </select>
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
                <th width="20%">Date</th>
                <th>Order</th>
                <th>User</th>
                <th width="20%">Amount</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" >  </td>
                <td id="q-total" style="background: #e583a47d;font-weight:600;">0</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {

        $.fn.dataTable.ext.errMode = 'none';

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
                "url": "{{ route('ledger-report') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    user : function () {
                        return $("#filterUser").val();
                    }
                }
            },
            columns: [
                {
                    data: 'date',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'order',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'user',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'credit',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
                $('#q-total').text(data.json.total);
            }
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        /* filter Datatable */
        $('body').on('change', '#filterUser', function(e) {
            var filterUser = $('#filterUser').val();

            if (filterUser != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }
            ServerDataTable.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterUser').val('').trigger('change');
            ServerDataTable.ajax.reload();
        });

    });
</script>
@endsection
