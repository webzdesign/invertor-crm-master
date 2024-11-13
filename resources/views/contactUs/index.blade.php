@extends('layouts.master')

@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">Manage {{ $moduleName }}</h2>
</div>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="cards">
    <table class="datatable-users table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th width="5%">Sr No.</th>
                <th width="15%">Name</th>
                <th width="20%">Email</th>
                <th width="20%">Date Time</th>
                <th width="40%">Message</th>
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
                "url": "{{ route('contactus.index') }}",
                "dataType": "json",
                "type": "POST",
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
                    data: 'email',
                },
                {
                    data: 'created_at',
                },
                {
                    data: 'message',
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
