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
                <th>Description</th>
                <th width="20%">Commission receivable</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td> <button class="btn btn-sm btn-success" id="withdraw-amount" style="width: 100px;float:right;"> Withdraw </button> </td>
                <td id="bl-total" style="background: #e583a47d;font-weight:600;">0</td>
            </tr>
        </tfoot>
    </table>
</div>


<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap mt-4">
    <h2 class="f-24 f-700 c-36 mb-0"> {{ $moduleName2 }} </h2>
</div>

<div class="cards mt-3">

    <div class="cards">
        <table class="sellerCommissionDt table datatableMain" style="width: 100%!important;">
            <thead>
                <tr>
                    <th width="20%">Amount Requested</th>
                    <th width="20%">Date</th>
                    <th width="20%">Status</th>
                    <th width="20%">Details</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>


{{-- Withdrawal request --}}
<div class="modal fade" id="withdrawal-modal" tabindex="-1" aria-labelledby="withdrawal-modal" aria-modal="true"
    role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 800px;">
        <div class="modal-content">
            <form action="{{ route('withdrawal-request') }}" id="withdrawal-request" method="POST" enctype="multipart/form-data"> @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="exampleModalLongTitle"> COMMISSION WITHDRAWAL REQUEST </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-3">

                        <div class="form-group">
                            <div class="row">
                                <label for="bank" class="c-gr f-500 f-12 w-100 mb-2"> BANK ACCOUNT : <span class="text-danger">*</span> </label>
                                <div class="col-md-12 mb-2 d-flex">
                                    <select name="bank" id="bank" class="form-control">
                                        <option value="" selected> --- Select a Bank Account ---- </option>
                                        <option value="" data-adder="true"> ADD NEW BANK DETAILS </option>
                                        @forelse ($accounts as $account)
                                            <option value="{{ $account->id }}"> {{ $account->name . ' ' . $account->surname . ' - ' . $account->iban_number }} </option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
        
                            </div>
                        </div>

                        <div id="withdrwal-content" class="stickyTable">

                        </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="should-disabled"> Save </button>
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal"> Cancel </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Withdrawal request --}}

{{-- Bank details adder --}}
<div class="modal fade" id="bank-adder" tabindex="-1" aria-labelledby="bank-adder" aria-modal="true"
    role="dialog">
    <div class="modal-dialog modal-lg" style="max-width: 800px;">
        <div class="modal-content">
            <form action="" method="POST" id="bankDetailAddForm">
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="exampleModalLongTitle"> ADD BANK DETAILS </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-3">
                    @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <label for="bank-name"> Name </label>
                                <input type="text" id="bank-name-add" name="bank_name_add" class="form-control">
                            </div>
        
                            <div class="col-md-4">
                                <label for="surname"> Surname </label>
                                <input type="text" id="surname-add" name="suername_add" class="form-control">
                            </div>
        
                            <div class="col-md-4">
                                <label for="iban"> IBAN Number </label>
                                <input type="text" id="iban-add" name="iban_add" class="form-control text-uppercase">
                            </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" > Save </button>
                    <button type="button" class="btn btn-default" data-bs-dismiss="modal"> Cancel </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Bank details adder --}}


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

        @if($errors->any())
            Swal.fire('', 'Something went wrong please try again later.' , 'error');
        @endif

        $('#withdrawal-request').validate({
            rules: {
                bank: {
                    required: true
                }
            },
            messages: {
                bank: {
                    required: "Select a bank account."
                }
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element.parent("div"));
            }
        });

        $('#bankDetailAddForm').validate({
            rules: {
                bank_name_add: {
                    required: true
                },
                suername_add: {
                    required: true
                },
                iban_add: {
                    required: true,
                    remote: {
                        url: "{{ route('iban-check') }}",
                        type: 'POST',
                        async: false
                    }
                }
            },
            messages: {
                bank_name_add: {
                    required: "Name is required."
                },
                suername_add: {
                    required: "Surname is required."
                },
                iban_add: {
                    required: "IBAN number is required.",
                    remote: "Invalid IBAN Number."
                }                
            },
            submitHandler: function (form, event) {
                event.preventDefault();

                let nameInp = $('#bank-name-add').val();
                let surnameInp = $('#surname-add').val();
                let ibanInp = $('#iban-add').val().toUpperCase();

                $.ajax({
                    url: "{{ route('bank-account-save') }}",
                    type: 'POST',
                    data: $('#bankDetailAddForm').serializeArray(),
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        if (response.status) {
                            Swal.fire('', response.message, 'success');
                            $('#bank-adder').modal('hide');
                            $('#bank').append(`<option value="${response.id}"> ${nameInp} ${surnameInp} - ${ibanInp} </option>`);
                            $('#bank').val(response.id).trigger('change');
                            $("#bankDetailAddForm")[0].reset();
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

        $('#bank').select2({
            dropdownParent: $('#withdrawal-modal'),
            width: '100%',
            allowClear: true,
        })

        $('#bank').on('change', function (e) {
            let value = $(this).val();
            $('.bank-delete-btn').remove();

            if ($('option:selected', this).hasAttr('data-adder') && $('option:selected', this).attr('data-adder') == 'true') {
                $('#bank-adder').modal('show');
            } else if (!$('option:selected', this).hasAttr('data-adder') && isNotEmpty(value)) {
                $(`<button data-id="${value}" type="button" class="btn btn-danger ms-2 bank-delete-btn" title="Delete selected bank account"> <i class="fa fa-trash"> </i> </button>`).insertAfter($(this).siblings('.select2'));
            }
        });

        $(document).on('click', '.bank-delete-btn', function (e) {
            let thisId = $(this).attr('data-id');
            
            Swal.fire({
                title: 'Are you sure want to delete this bank account?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: "{{ route('bank-account-delete') }}",
                        type: 'POST',
                        data: {
                            id: thisId
                        },
                        success: function(response) {
                            if (response.status) {
                                $('#bank').val(null).trigger('change');
                                $('.bank-delete-btn').remove();
                                $(`#bank option[value="${thisId}"]`).remove();
                                Swal.fire('', response.message, 'success');
                            } else {
                                Swal.fire('', response.message, 'error');
                            }
                        }
                    });
                }
            });
        });

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
                    data: 'voucher',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'crdr',
                    orderable: false,
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
                $('#bl-total').text(data.json.bl);
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
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput2'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('seller-withdrawal-reqs-2') }}",
                "dataType": "json",
                "type": "POST"
            },
            columns: [
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
            }
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
        $('#filterInput2 > input').keyup(function() {
            sellerCommissionDt.search($(this).val()).draw();
        });

        $('#withdraw-amount').on('click', function (e) {
            $.ajax({
                url: "{{ url('withdrawalable-amount') }}",
                type: 'POST',
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success: function (response) {
                    if (response.status) {
                        $('#withdrwal-content').html(response.html);
                        $('#withdrawal-modal').modal('show');

                        if (response.orders > 0) {
                            $('#should-disabled').attr('disabled', false);
                        } else {
                            $('#should-disabled').attr('disabled', true);
                        }

                    } else {
                        Swal.fire('', response.message, 'error');
                    }
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });
        });

        $(document).on('hidden.bs.modal', '#bank-adder', function (e) {
            if (e.namespace == 'bs.modal') {
                $("#bankDetailAddForm")[0].reset();
                $('#surname-add-error').remove();
                $('#iban-add-error').remove();
                $('#bank-name-add-error').remove();
            }
        });
    });
</script>
@endsection
