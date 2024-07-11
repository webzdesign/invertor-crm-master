@extends('layouts.master')

@section('css')
<style>
    .stickyTable:not('#withdrwal-details'){
        max-height: 295px;
        overflow: auto;
        border: 1px solid #dee2e6;
    }
    .stickyTable table{
        margin: 0 !important;
    }
    .stickyTable table{
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .stickyTable table thead,
    .stickyTable table tfoot{
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 1;
    }
    .stickyTable table thead{
        border-bottom: 1px solid #000;
    }
    .stickyTable table tfoot{
        bottom: 0;
    }
    .stickyTable table thead tr,
    .stickyTable table tfoot tr{
        border-width:0; 
    }
    .stickyTable table tbody tr td:first-child,
    .stickyTable table tfoot tr td:first-child,
    .stickyTable table thead tr th{
        border-left: 0; 
    }
    .stickyTable table tbody,
    .stickyTable table tfoot{
        border-top: 0 !important;
    }
    .stickyTable table > tfoot > tr:first-child > td{
        border-top: 0;
    }
    .stickyTable table tfoot tr td{
        border: 0;
    }
    .stickyTable table thead tr th,
    .stickyTable table tbody tr td,
    .stickyTable table tfoot tr td{
        border-right-color: #dee2e6 !important;
    }
    .stickyTable table tbody tr:last-child td{
        border-bottom: 0;
    }
    .stickyTable table tfoot tr td{
        border-top-color: #000 !important;
    }
    .stickyTable table thead tr th,
    .stickyTable table tbody tr td,
    .stickyTable table tfoot tr td{
        font-size: 12px;
        padding: 4px 6px;
    }
    .stickyTable table tfoot tr td{
        font-family: "Roboto Bold" !important;
    }
    .inline-image-preview {
        width: 100%;
        border: 1px solid black;
        border-radius: 10px;
    }
    #filterInput2 input, #filterInput3 input, #filterInput4 input{
        padding-left: 40px;
    }

    #filterInput2 svg, #filterInput3 svg, #filterInput4 svg {
        position: absolute;
        transform: translate(-50%, -50%);
        top: 50%;
        left: 22px;
        pointer-events: none;
    }
</style>
@endsection

{{ Config::set('app.module', $moduleName) }}

@section('content')

<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">{{ $moduleName }}</h2>
</div>

<div class="cards">
    <table class="seller-commission table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th>Seller Name</th>
                <th width="20%">Commission Payable</th>
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

<div class="mt-3">
    <div class="tabCards">

    <ul class="nav nav-tabs border-0 accountWrpr px-2 mb-1" id="myTab" role="tablist">
        <li class="nav-item mb-3" role="presentation">
            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true"> Pending </button>
        </li>
        <li class="nav-item mb-3" role="presentation">
            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Accepted</button>
        </li>
        <li class="nav-item mb-3" role="presentation">
            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Rejected</button>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
            {{-- Pending --}}
            <div class="cards">
                <table class="sellerCommissionDt table datatableMain" style="width: 100%!important;">
                    <thead>
                        <tr>
                            <th>Seller Name</th>
                            <th width="20%">Commission Amount</th>
                            <th width="20%">Date</th>
                            <th width="20%">Request</th>
                            <th width="20%">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            {{-- Pending --}}
        </div>
        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
            {{-- Accepted --}}
            <div class="cards">
                <table class="sellerCommissionDt2 table datatableMain" style="width: 100%!important;">
                    <thead>
                        <tr>
                            <th>Seller Name</th>
                            <th width="20%">Commission Amount</th>
                            <th width="20%">Date</th>
                            <th width="20%">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            {{-- Accepted --}}
        </div>
        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
            {{-- Rejected --}}
            <div class="cards">
                <table class="sellerCommissionDt3 table datatableMain" style="width: 100%!important;">
                    <thead>
                        <tr>
                            <th>Seller Name</th>
                            <th width="20%">Commission Amount</th>
                            <th width="20%">Date</th>
                            <th width="20%">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            {{-- Rejected --}}
        </div>
    </div>

    </div>
</div>



{{-- Accept Withdraw Request Modal --}}
<div class="modal fade" id="accept-request" tabindex="-1" aria-labelledby="accept-request" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ url('') }}" method="POST" id="accept-request-form" enctype="multipart/form-data"> @csrf
                <div class="modal-header no-border modal-padding">
                    <h1 class="modal-title fs-5"> WITHDRAWAL REQUEST CONFIRMATION </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">                    

                    <div class="form-group">
                        <div class="row">
                            <label class="c-gr f-500 f-14 w-100 mb-2"> UPLOAD PAYMENT RECEIPT : </label>
                            <input type="file" class="form-control" name="receipt[]" id="receipt" multiple>
                            <input type="hidden" name="id" id="withdrawal-request-id">
                        </div>
                    </div>

                    <div id="withdrwal-content" class="stickyTable mt-2">

                    </div>

                </div>
                <div class="modal-footer no-border">
                    <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                    <button type="submit" class="btn-primary f-500 f-14"> Accept </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Accept Withdraw Request Modal --}}

{{-- Details --}}
<div class="modal fade" id="details" tabindex="-1" aria-labelledby="accept-request" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
                <div class="modal-header no-border modal-padding">
                    <h1 class="modal-title fs-5"> WITHDRAWAL DETAILS </h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="withdrwal-details" class="mt-2 stickyTable">

                    </div>
                </div>
                <div class="modal-footer no-border">
                    <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Close </button>
                </div>
        </div>
    </div>
</div>
{{-- Details --}}

@endsection

@section('script')
<script>
    $(document).ready(function() {

        $.fn.dataTable.ext.errMode = 'none';

        var ServerDataTable = $('.seller-commission').DataTable({
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
                $('#total-receivable').text(data.json.total);
            }
        });

        var sellerCommissionDt = $('.sellerCommissionDt').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",

            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#seller-filter'><'#filterInput2'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('seller-withdrawal-reqs') }}",
                "dataType": "json",
                "type": "POST"
            },
            columns: [
                {
                    data: 'seller_name',
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
                    data: 'details',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
                $('#seller-select2').select2();
            }
        });

        var sellerCommissionDt2 = $('.sellerCommissionDt2').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",

            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput3'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('seller-withdrawal-reqs-accepted') }}",
                "dataType": "json",
                "type": "POST"
            },
            columns: [
                {
                    data: 'seller_name',
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
                    data: 'details',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
            }
        });

        var sellerCommissionDt3 = $('.sellerCommissionDt3').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",

            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput4'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('seller-withdrawal-reqs-rejected') }}",
                "dataType": "json",
                "type": "POST"
            },
            columns: [
                {
                    data: 'seller_name',
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
                    data: 'details',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
            }
        });

        $(document).on('click', '.reject-wreq', function () {
            let thisId = $(this).attr('data-id');

            if (isNumeric(thisId)) {
                Swal.fire({
                    title: 'Are you sure want to reject this withdrawal request?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('reject-withdrawal-request') }}",
                            type: 'POST',
                            data: {
                                id: thisId
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire('', response.message, 'success');
                                    ServerDataTable.ajax.reload();
                                    sellerCommissionDt.ajax.reload();
                                    sellerCommissionDt2.ajax.reload();
                                    sellerCommissionDt3.ajax.reload();
                                } else {
                                    Swal.fire('', response.message, 'error');
                                }
                            }
                        });
                    }
                });
            }
        });

        $(document).on('click', '.accept-wreq', function () {
            let thisId = $(this).attr('data-id');

            if (isNumeric(thisId)) {
                $.ajax({
                    url: "{{ route('withdrawal-req-info') }}",
                    type: 'POST',
                    data: {
                        id : thisId
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.status) {
                            $('#accept-request').modal('show');
                            $('#withdrawal-request-id').val(thisId);
                            $('#withdrwal-content').html(response.html);
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

        $(document).on('hidden.bs.modal', '#accept-request', function (e) {
            if (e.namespace == 'bs.modal') {
                $('withdrawal-request-id').val(null);
                $('#withdrwal-content').html('');
            }
        });

        $('#accept-request-form').validate({
            rules: {
                'receipt[]': {
                    fileType: 'jpeg|png|jpg',
                    maxFiles: 10,
                    fileSizeLimit: (10 * 1024 * 1024) * 2
                }
            }
        });

        $(document).on('submit', '#accept-request-form', function(e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: "{{ route('accept-withdrawal-request') }}",
                type: 'POST',
                contentType: false,
                processData: false,
                dataType: 'json',
                data: formData,
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                    $('button[type="submit"]').attr('disabled', true);
                },
                success: function (response) {
                    if (response.status) {
                        $('#accept-request').modal('hide');
                        Swal.fire('', response.message, 'success');
                        ServerDataTable.ajax.reload();
                        sellerCommissionDt.ajax.reload();
                        sellerCommissionDt2.ajax.reload();
                        sellerCommissionDt3.ajax.reload();
                    } else {
                        Swal.fire('', response.message, 'error');
                    }
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                    $('button[type="submit"]').attr('disabled', false);
                }
            });

        });

        $(document).on('click', '.show-orders', function (e) {
            let thisId = $(this).attr('data-id');

            if (isNumeric(thisId)) {
                $.ajax({
                    url: "{{ route('withdrwal-details') }}",
                    type: 'POST',
                    data: {
                        id: thisId
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.status) {
                            $('#details').modal('show');
                            $('#withdrwal-details').html(response.html);
                        }
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });                
            }
        });

        $(document).on('hidden.bs.modal', '#details', function (e) {
            if (e.namespace == 'bs.modal') {
                $('#withdrwal-details').html('');
            }
        });


        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        $('#filterInput2').html($('#searchPannel').html());
        $('#seller-filter').html(`
            <select class="seller-select2 select2-hidden-accessible" style="width:100%" data-placeholder="Select a Seller">
                @forelse($sellers as $seller)
                    @if($loop->first)
                        <option value="" selected> --- Select a Seller --- </option>
                    @endif
                        <option value="{{ $seller->user_id }}">{{ $seller->user->name ?? '-' }}</option>
                @empty
                        <option value="" selected> --- No Seller Found --- </option>
                @endforelse
            </select>
        `);
        $('#filterInput2 > input').keyup(function() {
            sellerCommissionDt.search($(this).val()).draw();
        });
        $('#seller-filter > select').on('change', function () {
            sellerCommissionDt.search($(this).val()).draw();
        });

        $('#filterInput3').html($('#searchPannel').html());
        $('#filterInput3 > input').keyup(function() {
            sellerCommissionDt2.search($(this).val()).draw();
        });

        $('#filterInput4').html($('#searchPannel').html());
        $('#filterInput4 > input').keyup(function() {
            sellerCommissionDt3.search($(this).val()).draw();
        });

    });
</script>
@endsection
