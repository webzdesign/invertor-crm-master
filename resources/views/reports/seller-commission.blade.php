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
                        <div class="row d-flex flex-column align-items-center">
                            <label class="c-gr f-500 f-14 w-100 mb-2"> PAYMENT RECEIPT : <span class="text-danger">*</span> </label>
                            <input type="file" class="form-control" name="receipt[]" id="receipt" style="width: 95%;" multiple>
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
            "dom": "<'filterHeader d-block-500 cardsHeader row'<'div.col-lg-3'l><'#seller-filter.col-lg-9'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('seller-withdrawal-reqs') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    seller: function () {
                        return $('#seller-filter > div.row > .col-sm-4 > select.seller-select2').val();
                    },
                    date: function () {
                        return $('#seller-filter > div.row > .col-sm-4 > div.position-relative > input#date-filter').val();
                    }
                }
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
                $('.seller-select2').select2({
                    allowClear: true,
                });

                $('#date-filter').datepicker({
                    format: 'dd-mm-yyyy',
                    autoclose: true,
                    todayHighlight: true,
                    orientation: "bottom"
                });
            }
        });
        
        $(document).on('change', '#seller-filter > div.row > .col-sm-4 > select.seller-select2', function() {
            sellerCommissionDt.ajax.reload();
        });
        $(document).on('change', '#seller-filter > div.row > .col-sm-4 > div.position-relative > input#date-filter', function() {
            $('.empty-date').css('display', 'block');
            sellerCommissionDt.ajax.reload();
        });
        $(document).on('keyup', '#seller-filter > div.row > .col-sm-4 > div.position-relative > input#tbl-2-search', function() {
            sellerCommissionDt.search($(this).val()).draw();
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
                    required: true,
                    fileType: 'jpeg|png|jpg',
                    maxFiles: 10,
                    fileSizeLimit: (10 * 1024 * 1024) * 2
                }
            },
            messages: {
                'receipt[]': {
                    required: "Upload payment receipt."
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

        $('#seller-filter').html(`
            <div class="row">
                <div class="col-sm-4 mt-lg-0 mt-2">
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
                </div>
                <div class="col-sm-4 mt-lg-0 mt-2">
                    <div class="position-relative">
                        <input type="text" class="form-control f-14" id="date-filter" readonly placeholder="Select a date">
                        <i class="fa fa-times cursor-pointer empty-date" style="position:absolute;top:7px;right:10px;display:none;"> </i>
                    </div>
                </div>
                <div class="col-sm-4 mt-lg-0 mt-2">
                    <div class="position-relative" id="filterInput2">
                        <input class="form-control f-14" placeholder="Search" id="tbl-2-search">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.6932 14.2957L10.7036 9.31023C11.386 8.35146 11.791 7.1584 11.791 5.90142C11.791 2.64178 9.14704 0 5.8847 0C2.62254 0.00017572 0 2.64196 0 5.90142C0 9.16105 2.64397 11.8028 5.90631 11.8028C7.18564 11.8028 8.35839 11.3981 9.31795 10.7163L14.3076 15.7018C14.4994 15.8935 14.7553 16 15.0113 16C15.2672 16 15.523 15.8935 15.7149 15.7018C16.0985 15.2971 16.0985 14.6792 15.6935 14.2956L15.6932 14.2957ZM1.96118 5.90155C1.96118 3.72845 3.73104 1.98133 5.88465 1.98133C8.03826 1.9815 9.82938 3.72845 9.82938 5.90155C9.82938 8.07466 8.05952 9.82178 5.90591 9.82178C3.7523 9.82178 1.96118 8.05338 1.96118 5.90155Z" fill="#7B809A" />
                        </svg>
                    </div>
                </div>
            </div>
        `);

        $('#filterInput3').html($('#searchPannel').html());
        $('#filterInput3 > input').keyup(function() {
            sellerCommissionDt2.search($(this).val()).draw();
        });

        $('#filterInput4').html($('#searchPannel').html());
        $('#filterInput4 > input').keyup(function() {
            sellerCommissionDt3.search($(this).val()).draw();
        });

        $(document).on('click', '.empty-date', function () {
            $('#date-filter').val('');
            $(this).css('display', 'none');
            sellerCommissionDt.ajax.reload();
        });

    });
</script>
@endsection
