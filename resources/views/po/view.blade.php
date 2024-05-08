@extends('layouts.master')

@section('breadcumb')
    <li class="f-14 f-400 c-7b">
        /
    </li>
    <li class="f-14 f-400 c-36">View </li>
@endsection

@section('css')
<style>
    .customLayout th {
        white-space: nowrap;
        font-size: 13px;
    }

    .srNumberClass {
        font-size: 10px !important;
    }
</style>
@endsection

@section('content')
    {{ Config::set('app.module', $moduleName) }}
    <h2 class="f-24 f-700 c-36 my-2">View {{ $moduleName }}</h2>
        <div class="cards">
            <div class="cardsBody pb-0">

                <div class="row">

                    <div class="col-sm-6 col-md-4">
                        <div class="form-group">
                            <label for="order_number" class="c-gr f-500 f-16 w-100 mb-2">Order Number</label>

                            <input class="form-control" id="order_number" placeholder="" type="text" value="{{ $po->order_no }}" readonly style="background:#efefef">
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <div class="form-group">
                            <label for="order_date" class="c-gr f-500 f-16 w-100 mb-2">Order Date</label>
                            <input type="text" readonly name="order_date" placeholder="Order Date" id="order_date" value="{{ date('d-m-Y', strtotime($po->date)) }}" class="form-control datepicker" style="background:#efefef">

                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <!-- Datasource -->
                        <div class="form-group">
                            <label for="supplier" class="c-gr f-500 f-16 w-100 mb-2">Supplier</label>
                            <input type="text" readonly class="form-control" value="{{ $po->supplier->name }}">
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="col-md-12">
                            <div
                                class="cardsHeader f-20 f-600 c-36 f-700 border-0 ps-0 tableHeading position-relative my-4">
                                <span>Products</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="row">

                                <div class="table-responsive">
                                    <input type="hidden">
                                    <table class="table table-bordered customLayout">
                                        <thead>
                                            <tr>

                                                <th >Category  </th>

                                                <th >Product  </th>

                                                <th >Quantity  </th>

                                                <th >Price  </th>

                                                <th >Amount </th>

                                                <th >Remarks </th>
                                            </tr>
                                        </thead>

                                        <tbody class="upsertable">
                                            @forelse($items as $key => $item)
                                            <tr>

                                                <td>
                                                    <div style="min-width: 200px;width: 100%" class="removable-category">
                                                        <input type="text" readonly class="form-control" value="{{ $item->category->status == 1 ? $item->category->name : '' }}">
                                                    </div>
                                                </td>


                                                <td>
                                                    <div style="min-width: 200px;width: 100%" class="removable-product">
                                                        <input type="text" readonly class="form-control" value="{{ $item->category->status == 1 ? $item->product->name : '' }}">
                                                    </div>
                                                </td>


                                                <td >
                                                    <div style="min-width: 200px;">
                                                        <input type="number" readonly value="{{ $item->qty }}" id="quantity-{{ $key }}" class="form-control m-quantity" style="background:#efefef">
                                                    </div>
                                                </td>


                                                <td >
                                                    <div style="min-width: 200px;">
                                                        <input type="number" readonly value="{{ $item->price }}" id="price-{{ $key }}" class="form-control m-price" style="background:#efefef">
                                                    </div>
                                                </td>


                                                <td >
                                                    <div style="min-width: 200px;">
                                                        <input type="number" readonly value="{{ $item->amount }}" id="amount-{{ $key }}"  class="form-control m-amount" style="background:#efefef" readonly>
                                                    </div>
                                                </td>


                                                <td >
                                                    <div style="min-width: 200px;">
                                                        <input type="text" readonly value="{{ $item->remarks }}" id="remarks-{{ $key }}" class="form-control m-remarks" style="background:#efefef">
                                                    </div>
                                                </td>

                                            </tr>
                                            @empty
                                           <tr>
                                            <td colspan="6">
                                                <h4>This purchase order has no products.</h4>
                                            </td>
                                           </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td> 
                                                    <div style="min-width: 200px;">
                                                        <input type="number" class="form-control mt-quantity" style="background:#efefef" value="{{ $items->sum('qty') }}" readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="min-width: 200px;">
                                                        <input type="number" class="form-control mt-price" style="background:#efefef" value="{{ ($items->sum('price')) }}" readonly>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="min-width: 200px;">
                                                        <input type="number" class="form-control mt-amount" style="background:#efefef" value="{{ ($items->sum('amount')) }}" readonly>
                                                    </div>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



            </div>

            <div class="cardsFooter d-flex justify-content-center">
                <a href="{{ route('purchase-orders.index') }}">
                    <button type="button" class="btn-default f-500 f-14">Cancel</button>
                </a>
            </div>
        </div>
@endsection
