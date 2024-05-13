@extends('layouts.master')

@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">Manage {{ $moduleName }}</h2>
    @permission("procurement-cost.create")
    <a href="{{ route('procurement-cost.create') }}" class="btn-primary f-500 f-14">
        <svg class="me-1" width="16" height="16" viewBox="0 0 16 16" fill="#ffffff" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.00008 13.3332V7.99984M8.00008 7.99984V2.6665M8.00008 7.99984H13.3334M8.00008 7.99984H2.66675" stroke="#ffffff" stroke-width="2" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="paint0_linear_1524_12120" x1="8.00008" y1="2.6665" x2="8.00008" y2="13.3332" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#ffffff"></stop>
                    <stop offset="1" stop-color="#ffffff"></stop>
                </linearGradient>
            </defs>
        </svg>
        Add Procurement Cost
    </a>
    @endpermission
</div>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="cards">
    <div class="row m-0 filterColumn">
        <div class="col-sm-3 col-md-3 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Status</label>
                <select name="filterStatus" id="filterStatus" class="select2 select2-hidden-accessible" data-placeholder="--- Select Status ---">
                    <option value="" selected> --- Select Status --- </option>
                    <option value="1">Active</option>
                    <option value="0">in-Active</option>
                </select>
            </div>
        </div>

        <div class="col-sm-3 col-md-3 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Category</label>
                <select name="filterCategory" id="filterCategory" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Category ---">
                    <option value="" selected> --- Select a Category --- </option>
                    @forelse($categories as $id => $name)
                        @if($loop->first)
                        <option value="" selected> --- Select a Category --- </option>
                        @endif
                        <option value="{{ $id }}"> {{ $name }} </option>
                    @empty
                        <option value="" selected> --- No Category Found </option>
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-sm-3 col-md-3 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Product</label>
                <select name="filterProduct" id="filterProduct" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Product ---">
                    <option value="" selected> --- Select a Product --- </option>
                </select>
            </div>
        </div>

        <div class="col-sm-3 col-md-3 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Role</label>
                <select name="filterRole" id="filterRole" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Role ---">
                    <option value="" selected> --- Select a Role --- </option>
                    @forelse($roles as $id => $name)
                        @if($loop->first)
                        <option value="" selected> --- Select a Role --- </option>
                        @endif
                        <option value="{{ $id }}"> {{ $name }} </option>
                    @empty
                        <option value="" selected> --- No Role Found </option>
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-xl-3 col-sm-3 position-relative">
            <div class="form-group mb-0">
                <label class="c-gr f-500 f-14 w-100 mb-1 d-none-500">&nbsp;</label>
                <button class="btn-default f-500 f-14 clearData" style="display:none;"><i class="fa fa-remove" aria-hidden="true"></i> Clear filters</button>
            </div>
        </div>
    </div>

    <table class="datatable-users table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th>Sr No.</th>
                <th>Product</th>
                <th>Base Price</th>
                <th>Min. Sales Price</th>
                <th>Status</th>
                <th>Added By</th>
                <th>Updated By</th>
                <th>Action</th>
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
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",

            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('procurement-cost.index') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    filterProduct:function() {
                        return $("#filterProduct").val();
                    },
                    filterStatus:function() {
                        return $("#filterStatus").val();
                    },
                    filterCategory:function() {
                        return $("#filterCategory").val();
                    },
                    filterRole:function() {
                        return $("#filterRole").val();
                    }
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'product_id',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'base_price',
                },
                {
                    data: 'min_sales_price',
                },
                {
                    data: 'status',
                },
                {
                    data: 'addedby.name',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'updatedby.name',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                }
            ],
        });

        $(document).on('change', '#filterCategory', function (event) {
        let thisId = $(this).val();

            if (thisId !== '') {
                $.ajax({
                    url: "{{ route('get-products-on-category') }}",
                    type: 'POST',
                    data: {
                        id: thisId
                    },
                    success: function (response) {
                        if (response !== '') {
                            $(`#filterProduct`).empty().append(response);
                            $(`#filterProduct`).select2({
                                width: '100%',
                                allowClear: true,
                                placeholder: "Select a Product"
                            });
                        }
                    },
                });
            } else {
                $(`#filterProduct`).empty().append(`<option value="" selected> Select a Product </option>`);
                $(`#filterProduct`).select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: "Select a Product"
                });
            }
        })

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        $('.datepicker').datetimepicker({
            format: "DD/MM/YYYY", 
            timeZone: ''
        });

        /* filter Datatable */
        $('body').on('change', '#filterStatus, #filterCategory, #filterRole, #filterProduct', function(e){
            var filterStatus = $('body').find('#filterStatus').val();
            var filterCategory = $('body').find('#filterCategory').val();
            var filterRole = $('body').find('#filterRole').val();
            var filterProduct = $('body').find('#filterProduct').val();
            
            if (filterStatus != '' || filterCategory != '' || filterRole != '' || filterProduct != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }

            ServerDataTable.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterStatus').val('').trigger('change');
            $('body').find('#filterCategory').val('').trigger('change');
            $('body').find('#filterRole').val('').trigger('change');
            $('body').find('#filterProduct').val('').trigger('change');
            ServerDataTable.ajax.reload();
        });
    });
</script>
@endsection