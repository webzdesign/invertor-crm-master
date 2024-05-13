@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">View </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">View {{ $moduleName }}</h2>
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">


                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Role </label>
                        <input type="text" readonly class="form-control" value="{{ $cost->role->status == 1 ? $cost->role->name : '' }}">
                    </div>
                </div>

                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Category </label>
                        <input type="text" readonly class="form-control" value="{{ $cost->category->status == 1 ? $cost->category->name : '' }}">
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product </label>
                        <input type="text" readonly class="form-control" value="{{ $cost->category->status == 1 ? $cost->product->name : '' }}">
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Base Price </label>
                        <input type="text" readonly id="base_price" value="{{ Helper::currencyFormatter($cost->base_price) }}" class="form-control">
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Minimum Sales Price </label>
                        <input type="text" readonly id="min_sales_price" value="{{ Helper::currencyFormatter($cost->min_sales_price) }}" class="form-control">
                    </div>
                </div>

            </div>
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('procurement-cost.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
        </div>
    </div>
@endsection

