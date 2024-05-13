@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/dropzone-basic.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/dropzone.min.css') }}" />
<style>
  .dz-error-message, .dz-error-mark {
    display: none!important;
  }
  .dtcheckbox {
    height: 20px;
    width: 20px;
	}
  .uploaded_image {
    display: flex;
    flex-direction: row;
  }
  .remove {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    right: -12px;
    top: -10px;
    border: none;
  }
  .dz-button {
    position: relative;
    top: 15px;
  }
</style>
@endsection

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

            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Category </label>
                    <input type="text" id="category" value="{{ $product->category->name ?? '' }}" class="form-control" readonly>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Product Name </label>
                    <input type="text" id="name" value="{{ $product->name }}" class="form-control" readonly>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Product Number </label>
                    <input type="text" id="unique_number" value="{{ $product->unique_number }}" class="form-control" readonly>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Purchase Price </label>
                    <input type="text" id="pprice" value="{{ Helper::currencyFormatter($product->purchase_price) }}" class="form-control" readonly>
                </div>
            </div>

        </div>
    </div>
    
    <div class="cardsBody pb-0">
        <div class="row">
        @forelse($product->images as $key => $image)
        @if($loop->first) <label class="c-gr f-500 f-16 w-100 mb-2"> Images </label> @endif
        <div class="col-md-2">
            <a href="{{ $image->image }}" target="_blank">
                <img src="{{ $image->image }}" class="w-100 shadow-1-strong rounded" style="object-fit:cover;height:100px;width:100%;margin:5px 0px;border:1px solid black;">
            </a>
        </div>
        @empty
            <center>
                <p><strong>No images uploaded for this product yet.</strong></p>
            </center>
        @endforelse
        </div>
    </div>

    <div class="cardsFooter d-flex justify-content-center">
        <a href="{{ route('products.index') }}">
            <button type="button" class="btn-default f-500 f-14">Cancel</button>
        </a>
    </div>
</div>
@endsection
