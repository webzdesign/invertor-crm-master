@extends('layouts.master')

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
                <td colspan="2"> <button data-bs-toggle="modal" data-bs-target="#payamount" class="btn btn-sm btn-success" id="pay-amount" style="width: 60px;float:right;"> Pay </button> </td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="modal fade" id="payamount" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ url('pay-amount-to-seller') }}" method="POST" id="payamount-form" enctype="multipart/form-data"> @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="exampleModalLongTitle"> Pay commission to seller </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-12 mb-2 amount-field">
                            <label class="c-gr f-500 f-12 w-100 mb-2"> SELLER : <span class="text-danger">*</span> </label>
                            <select class="select2-hidden-accessible" name="seller" id="seller-picker" data-placeholder="--- Select a Seller ---">
                                <option value="" selected> --- Select a seller --- </option>
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller['id'] }}"> {{ $seller['name'] }} - ({{ $seller['email'] }}) </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mb-2 amount-field">
                            <label class="c-gr f-500 f-12 w-100 mb-2"> ENTER AMOUNT : <span class="text-danger">*</span> </label>
                            <input type="text" id="order-closing-amount" name="amount" class="form-control" placeholder="Enter amount" />
                        </div>

                    </div>
                </div>
                <div class="modal-footer no-border">
                    <button type="submit" class="btn-success f-500 f-14 btn"> Pay </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
            }
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        $('#seller-picker').select2({
            dropdownParent: $('#payamount'),
            width: '100%',
            allowClear: true
        });

        $('#payamount-form').validate({
            rules: {
                seller: {
                    required: true
                },
                amount: {
                    required: true,
                    number: true,
                    min: 1
                }
            },
            messages: {
                seller: {
                    required: "Select a seller."
                },
                amount: {
                    required: "Enter the amount",
                    number: "Enter valid amount",
                    min: "Enter valid amount."
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent("div"));
            },
            submitHandler: function(form, event) {
                if ($('#payamount-form').valid()) {
                    $('body').find('.LoaderSec').addClass('d-none');
                    $('button[type="submit"]').attr('disabled', false);
                    return true;
                } else {
                    return false;
                }
            }
        });

    });
</script>
@endsection
