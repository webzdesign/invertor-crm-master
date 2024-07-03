@extends('layouts.master')

{{ Config::set('app.module', $moduleName) }}

@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">{{ $moduleName }}</h2>
</div>
@endsection

@section('content')

<div class="cards">
    <table class="datatables-po table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                @if(User::isAdmin())
                    <th>Seller Name</th>
                    <th width="20%">Amount Payable</th>
                @else
                    <th>Order Number</th>
                    <th width="20%">Amount Receivable</th>
                @endif
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td >  </td>
                <td id="a-total" style="background: #e583a47d;font-weight:600;">0</td>
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
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('seller-commission') }}",
                "dataType": "json",
                "type": "POST"
            },
            columns: [
                {
                    data: 'seller_info',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'seller_amount',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
                $('#a-total').text(data.json.total);
            }
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

    });
</script>
@endsection
