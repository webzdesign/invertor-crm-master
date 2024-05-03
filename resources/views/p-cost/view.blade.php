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


                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Category: </label>
                        <input type="text" readonly class="form-control" value="{{ $cost->category->status == 1 ? $cost->category->name : '' }}">
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product: </label>
                        <input type="text" readonly class="form-control" value="{{ $cost->category->status == 1 ? $cost->product->name : '' }}">
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Base Price: </label>
                        <input type="text" readonly id="base_price" value="{{ old('base_price', $cost->base_price) }}" class="form-control" placeholder="Enter Base Price">
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

