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
<li class="f-14 f-400 c-36">View {{ $moduleName }} </li>
@endsection

@section('content')

{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">View {{ $moduleName }}</h2>
<div class="cards">
    <div class="cardsBody pb-0">
        <div class="row">

            <div class="col-md-4 col-sm-4">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Category: </label>
                    <input type="text" id="category" value="{{ $product->category->name ?? '' }}" class="form-control" readonly>
                </div>
            </div>

            <div class="col-md-4 col-sm-4">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Product Number: </label>
                    <input type="text" id="unique_number" value="{{ $product->unique_number }}" class="form-control" readonly>
                </div>
            </div>

            <div class="col-md-4 col-sm-4">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Product Name: </label>
                    <input type="text" id="name" value="{{ $product->name }}" class="form-control" readonly>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Purchase Price: </label>
                    <input type="text" id="pprice" value="{{ $product->purchase_price }}" class="form-control" readonly>
                </div>
            </div>

            <div class="col-md-6 col-sm-6">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Sales Price: </label>
                    <input type="text" id="sprice" value="{{ $product->sales_price }}" class="form-control" readonly>
                </div>
            </div>

            <div class="col-md-12 col-sm-12">
                <div class="form-group">
                    <label class="c-gr f-500 f-16 w-100 mb-2">Images:</label>
                    <div>            
                    <div id="uploaded_image">
                        <div class="row mt-3 sort_images">
                        @forelse($images as $image)
                            <div class="col-md-2 mb-3 imageListitem">
                            <div style=" border: 1px solid #c7c7c7">
                                <img src="{{ $image->image }}" class="getdata w-100 shadow-1-strong rounded" style="object-fit: cover;height:100px;" data-switchid="{{$image->id}}" />
                            </div>
                            </div>
                        @empty
                        @endforelse
                        </div>
                        </div>  

                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <div class="cardsFooter d-flex justify-content-center">
        <a href="{{ route('products.index') }}">
            <button type="button" class="btn-default f-500 f-14">Cancel</button>
        </a>
    </div>
</div>
@endsection
