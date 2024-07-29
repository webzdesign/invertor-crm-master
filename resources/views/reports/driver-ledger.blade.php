@extends('layouts.master')

@section('content')
{{ Config::set('app.module', $moduleName) }}
@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">{{ $moduleName }}</h2>
</div>
@endsection

<div class="cards">
    <table class="datatables-po table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th>Description</th>
                <th>Date</th>
                <th width="20%">Amount Payable</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">  <button data-bs-toggle="modal" data-bs-target="#payamount" class="btn btn-sm btn-success" id="pay-amount" style="width: 60px;float:right;"> Pay </button>  </td>
                <td id="bl-total" style="background: #e583a47d;font-weight:600;">0</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="modal fade" id="payamount" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ url('pay-amount-to-admin') }}" method="POST" id="payamount-form" enctype="multipart/form-data"> @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title" id="exampleModalLongTitle"> PAY AMOUNT TO ADMIN </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-12 mb-2 amount-field">
                            <label class="c-gr f-500 f-12 w-100 mb-2"> ENTER AMOUNT : <span class="text-danger">*</span> </label>
                            <input type="text" id="order-closing-amount" name="amount" class="form-control" placeholder="Enter amount" />
                        </div>

                        <div class="col-12 mb-2 document-field">
                            <label class="c-gr f-500 f-12 w-100 mb-2"> PAYMENT RECEIPT : <span class="text-danger">*</span> </label>
                            <input type="file" id="order-closing-document" name="file[]" class="form-control" multiple />
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
                    data: 'voucher',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'date',
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

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        $.validator.addMethod("fileType", function(value, element, param) {
            var fileTypes = param.split('|');
            var files = element.files;
            for (var i = 0; i < files.length; i++) {
                var extension = files[i].name.split('.').pop().toLowerCase();
                if ($.inArray(extension, fileTypes) === -1) {
                    return false;
                }
            }
            return true;
        }, "Only .png, .jpg, and .jpeg extensions supported");

        $.validator.addMethod("maxFiles", function(value, element, param) {
            return element.files.length <= param;
        }, "Maximum 10 files can be uploaded.");

        $.validator.addMethod("fileSizeLimit", function(value, element, param) {
            var totalSize = 0;
            var files = element.files;
            for (var i = 0; i < files.length; i++) {
                totalSize += files[i].size;
            }
            return totalSize <= param;
        }, "Total file size must not exceed 20 MB");

        $('#payamount-form').validate({
            rules: {
                amount: {
                    required: true,
                    number: true,
                    min: 1
                },
                'file[]': {
                    required: true,
                    fileType: 'jpeg|png|jpg',
                    maxFiles: 10,
                    fileSizeLimit: (10 * 1024 * 1024) * 2
                }
            },
            messages: {
                amount: {
                    required: "Enter the amount",
                    number: "Enter valid amount",
                    min: "Enter valid amount."
                },
                'file[]': {
                    required: "Upload payment receipt."
                }
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
