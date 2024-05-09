@extends('layouts.master')

@section('content')

    <div class="row">

        @permission('categories.view')
        <div class="col-xl-3 col-md-6">
            <div class="small-box" style="background:#15283c;">
                <div class="inner">
                    <h3 class="text-white"> {{ Category::count() }} </h3>
                    <p class="text-white"> Categories </p>
                </div>
                <div class="icon" style="color:rgba(0,0,0,.15);position:absolute;right:12px;top:2px;font-size:62px;">
                    <i class="fa fa-list-alt text-white"></i>
                </div>
                <a href="{{ route('categories.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>
            </div>
        </div>
        @endpermission

        @permission('products.view')
        <div class="col-xl-3 col-md-6">
            <div class="small-box" style="background:#be3685;">
                <div class="inner">
                    <h3 class="text-white"> {{ Product::count() }} </h3>
                    <p class="text-white"> Products </p>
                </div>
                <div class="icon" style="color:rgba(0,0,0,.15);position:absolute;right:12px;top:2px;font-size:62px;">
                    <i class="fa fa-product-hunt text-white"></i>
                </div>
                <a href="{{ route('products.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>
            </div>
        </div>
        @endpermission

        @permission('purchase-orders.view')
        <div class="col-xl-3 col-md-6">
            <div class="small-box" style="background:#e95657;">
                <div class="inner">
                    <h3 class="text-white"> {{ PO::count() }} </h3>
                    <p class="text-white"> Purchase Orders </p>
                </div>
                <div class="icon" style="color:rgba(0,0,0,.15);position:absolute;right:12px;top:2px;font-size:62px;">
                    <i class="fa fa-shopping-bag text-white"></i>
                </div>
                <a href="{{ route('purchase-orders.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>
            </div>
        </div>
        @endpermission

        @permission('sales-orders.view')
        <div class="col-xl-3 col-md-6">
            <div class="small-box" style="background:#15283c;">
                <div class="inner">
                    @if(in_array(2, auth()->user()->roles->pluck('id')->toArray()))
                        <h3 class="text-white"> {{ SO::where('seller_id', auth()->user()->id)->count() }} </h3>
                    @elseif (in_array(1, auth()->user()->roles->pluck('id')->toArray()))
                        <h3 class="text-white"> {{ SO::count() }} </h3>
                    @else
                        <h3 class="text-white"> 0 </h3>
                    @endif
                    <p class="text-white"> Sales Orders </p>
                </div>
                <div class="icon" style="color:rgba(0,0,0,.15);position:absolute;right:12px;top:2px;font-size:62px;">
                    <i class="fa fa-tag text-white"></i>
                </div>
                <a href="{{ route('sales-orders.index') }}" class="small-box-footer">More info <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></a>
            </div>
        </div>
        @endpermission

    </div>

@endsection
