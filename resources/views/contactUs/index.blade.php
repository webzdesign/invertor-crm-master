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
                <th width="10%">Mobile Number</th>
                <th width="20%">Date Time</th>
                <th width="5%">Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="contactDetailModal" tabindex="-1" role="dialog" aria-labelledby="contactDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fs-2" id="contactDetailModalLabel">Contact Details</h5>
        <button type="button" class="btn close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2" for="cname">Customer Name : </label>
                    <input type="text" name="cname" id="cname" value="" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2" for="cemail">Email : </label>
                    <input type="text" name="cemail" id="cemail" value="" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2" for="cphone">Phone Number : </label>
                    <input type="text" name="cphone" id="cphone" value="" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2" for="cmessage">Message : </label>
                    <textarea class="w-100 rounded-3" name="cmessage" id="cmessage" rows="6" style="background-color: #e9ecef; padding: 5px 10px;" readonly></textarea>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary close">Close</button>
        {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
      </div>
    </div>
  </div>
</div>

@endsection

@section('script')

<script>
    $(document).ready(function() {

        $(document).on('click','.modal-view-btn', function() {

            let cID = $(this).data('uniqueid');

            if(cID) {
                $.ajax({
                    url: "{{ route('contactus.detail') }}",
                    type: "POST",
                    data: { id: cID },
                    dataType: "json",
                    success: function (response) {
                        if(response.success == 1) {
                            if(response.data.name) {
                                $('#cname').val(response.data.name);
                            }
                            if(response.data.email) {
                                $('#cemail').val(response.data.email);
                            }
                            if(response.data.phone) {
                                if(response.data.country_dial_code == null){
                                    $('#cphone').val(`${response.data.phone}`);
                                } else {
                                    $('#cphone').val(`+${response.data.country_dial_code} ${response.data.phone}`);
                                }
                            }
                            if(response.data.message) {
                                $('#cmessage').text(response.data.message);
                            }
                            $('#contactDetailModal').modal('show');
                        } else {
                            console.log(response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("Request failed:", status, error);
                    },
                });
            }

        });

        $(document).on('click','.close', function() {
            $('#contactDetailModal').modal('hide');
        });

        $('#contactDetailModal').on('hidden.bs.modal', function () {
            $('#cname').val('');
            $('#cemail').val('');
            $('#cphone').val('');
            $('#cmessage').text('');
            $('#cmessage').css('height', '');
        });

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
                    data: 'phone',
                },               
                {
                    data: 'created_at',
                },
                {
                    data: 'action',
                },
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
