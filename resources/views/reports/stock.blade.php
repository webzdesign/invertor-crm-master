@extends('layouts.master')

@section('content')
{{ Config::set('app.module', $moduleName) }}
@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">
    <h2 class="f-24 f-700 c-36 mb-0">{{ $moduleName }}</h2>
</div>
@endsection

<div class="cards">
    <div class="row m-0 filterColumn">

        @if(!User::isDriver())
        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Type</label>
                <select name="filterType" id="filterType" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Driver ---">
                    <option value="" selected> --- Select a Type --- </option>
                    @forelse($types as $tid => $type)
                        <option value="{{ $tid }}"> {{ $type }} </option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Driver</label>
                <select name="filterDriver" id="filterDriver" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Driver ---">
                    <option value="" selected> --- Select a Driver --- </option>
                    @forelse($drivers as $did => $dname)
                        <option value="{{ $did }}"> {{ $dname }} </option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>
        @endif

        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Product</label>
                <select name="filterProduct" id="filterProduct" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Product ---">
                    <option value="" selected> --- Select a Product --- </option>
                    @forelse($products as $product)
                        <option value="{{ $product->product->id ?? 0 }}"> {{ $product->product->name ?? '-' }} </option>
                    @empty
                    @endforelse
                </select>
            </div>
        </div>

        <div class="col-md-4 col-sm-12 position-relative">
            <div class="form-group mb-0">
                <label class="c-gr f-500 f-14 w-100 mb-1 d-none-500">&nbsp;</label>
                <button class="btn-default f-500 f-14 clearData" style="display:none;"><i class="fa fa-remove" aria-hidden="true"></i> Clear filters</button>
            </div>
        </div>
    </div>
    <table class="datatables-po table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                @if(!User::isDriver())
                <th width="15%">Type</th>
                @endif
                <th>Product</th>
                <th width="10%">Quantity</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td @if(!User::isDriver()) colspan="2" @endif>  </td>
                <td id="q-total" style="background: #e583a47d;font-weight:600;">0</td>
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
            processing: true,
            serverSide: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here"
            },
            "dom":"<'filterHeader d-block-500 cardsHeader'l<'#filterInput'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('stock-report') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    filterType:function() {
                        return $("#filterType").val();
                    },
                    filterDriver:function() {
                        return $("#filterDriver").val();
                    },
                    filterProduct:function() {
                        return $("#filterProduct").val();
                    }
                }
            },
            columns: [
            @if(!User::isDriver())
                {
                    data: 'type',
                },
            @endif
                {
                    data: 'product_id',
                },
                {
                    data: 'qty'
                }
            ],
            drawCallback: function (data) {
                $('#q-total').text(data.json.total);
            }
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        /* filter Datatable */
        $('body').on('change', '#filterDriver, #filterType, #filterProduct', function(e) {
            var filterType = $('#filterType').val();
            var filterDriver = $('#filterDriver').val();
            var filterProduct = $('#filterProduct').val();

            if (filterDriver != '') {
                if (filterType == '1') {
                    $('body').find('#filterType').val('').trigger('change');
                }
            }

            if (filterType != '' || filterDriver != '' || filterProduct != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }
            ServerDataTable.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterType').val('').trigger('change');
            $('body').find('#filterDriver').val('').trigger('change');
            $('body').find('#filterProduct').val('').trigger('change');
            ServerDataTable.ajax.reload();
        });

    });
</script>
@endsection
