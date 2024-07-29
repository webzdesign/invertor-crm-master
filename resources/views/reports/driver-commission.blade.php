@extends('layouts.master')

@section('css')
<style>
    #filterInput2 input{
        padding-left: 40px;
    }

    #filterInput2 svg {
        position: absolute;
        transform: translate(-50%, -50%);
        top: 50%;
        left: 22px;
        pointer-events: none;
    }
</style>
@endsection

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
                <th>Driver Name</th>
                <th width="20%">Amount Receivable</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td id="total-receivable" style="background: #e583a47d;font-weight:600;">0</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap mt-4">
    <h2 class="f-24 f-700 c-36 mb-0"> {{ $moduleName2 }} </h2>
</div>

<div class="cards mt-3">

    <div class="cards">
        <table class="driverCommissionDt table datatableMain" style="width: 100%!important;">
            <thead>
                <tr>
                    <th>Driver Name</th>
                    <th width="20%">Amount Paid</th>
                    <th width="20%">Date</th>
                    <th width="20%">Status</th>
                    <th width="10%">Payment Receipt</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>


<div class="modal fade" id="proof-modal" tabindex="-1" aria-labelledby="proof-modal" aria-modal="true"
    role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 700px;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="exampleModalLongTitle"> PAYMENT PROOF </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-3">
                <div class="row" id="proof-modal-content">

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {

        $.fn.dataTable.ext.errMode = 'none';

        var ServerDataTable = $('.datatables-po').DataTable({
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
                "url": "{{ route('driver-commission') }}",
                "dataType": "json",
                "type": "POST"
            },
            columns: [
                {
                    data: 'driver_info',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'driver_amount',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
                $('#total-receivable').text(data.json.total);
            }
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });


        var driverCommissionDt = $('.driverCommissionDt').DataTable({
            pageLength : 50,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",
            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput2'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('driver-payment-log') }}",
                "dataType": "json",
                "type": "POST"
            },
            columns: [
                {
                    data: 'driver',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'amount',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'date',
                    searchable: false,
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'proof',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
            }
        });

        $('#filterInput2').html($('#searchPannel').html());
        $('#filterInput2 > input').keyup(function() {
            driverCommissionDt.search($(this).val()).draw();
        });

        $(document).on('click', '.accept-payment', function (e) {
            e.preventDefault();

            let thisId = $(this).attr('data-id');

            Swal.fire({
                title: 'Are you sure want to accept this payment?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value) {

                    $.ajax({
                        url: "{{ route('driver-payment', ['accept']) }}",
                        type: 'POST',
                        data: {
                            id: thisId
                        },
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function(response) {
                            if (response.status) {
                                ServerDataTable.ajax.reload()
                                driverCommissionDt.ajax.reload()
                                Swal.fire('', response.message, 'success');
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
        });

        $(document).on('click', '.reject-payment', function (e) {
            e.preventDefault();

            let thisId = $(this).attr('data-id');

            Swal.fire({
                title: 'Are you sure want to reject this payment?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value) {

                    $.ajax({
                        url: "{{ route('driver-payment', ['reject']) }}",
                        type: 'POST',
                        data: {
                            id: thisId
                        },
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function(response) {
                            if (response.status) {
                                ServerDataTable.ajax.reload()
                                driverCommissionDt.ajax.reload()
                                Swal.fire('', response.message, 'success');
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
        });


        $(document).on('click', '.show-proofs', function (e) {
            let tId = $(this).attr('data-id');

            if (isNumeric(tId)) {
                $.ajax({
                    url: "{{ route('show-driver-payment-proofs') }}",
                    type: 'POST',
                    data: {
                        id: tId
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.status) {
                            $('#proof-modal-content').html(response.html);
                            $('#proof-modal').modal('show');
                        } else {
                            Swal.fire('', response.message, 'info');
                        }
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            }
        });

    });
</script>
@endsection
