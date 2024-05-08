@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Edit  </li>
@endsection

@section('content')

{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Edit {{ $moduleName }}</h2>
<form action="{{ route('products.update', $id) }}" method="POST" id="addProduct" enctype="multipart/form-data"> @csrf @method('PUT')
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">

                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Category: <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Category ---">
                            @forelse($categories as $cid => $cname)
                                @if($loop->first)
                                <option value="" selected> --- Select a Category --- </option>
                                @endif
                                <option value="{{ $cid }}" @if($product->category_id == $cid) selected @endif > {{ $cname }} </option>
                            @empty                                
                                <option value=""> --- No Category Found --- </option>
                            @endforelse
                        </select>
                        @if ($errors->has('category'))
                            <span class="text-danger d-block">{{ $errors->first('category') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Name: <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" class="form-control" placeholder="Enter product name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Number: </label>
                        <input type="text" name="unique_number" id="unique_number" value="{{ old('unique_number', $product->unique_number) }}" class="form-control" placeholder="Enter product number">
                        @if ($errors->has('unique_number'))
                            <span class="text-danger d-block">{{ $errors->first('unique_number') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Purchase Price: <span class="text-danger">*</span></label>
                        <input type="text" name="pprice" id="pprice" value="{{ old('pprice', $product->purchase_price) }}" class="form-control" placeholder="Enter product purchase price">
                        @if ($errors->has('pprice'))
                            <span class="text-danger d-block">{{ $errors->first('pprice') }}</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>
        
        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('products.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
            <button type="submit" class="btn-primary f-500 f-14">Save changes</button>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
$(document).ready(function(){

    $.validator.addMethod("noSpace", function(value, element) { 
        return value.indexOf(" ") < 0; 
    }, "Space are not allowed");

    $("#addProduct").validate({
        rules: {
            category: {
                required: true,                
            },
            name: {
                required: true,                
            },
            unique_number: {
                noSpace: true,
                remote: {
                    url: "{{ url('checkProduct') }}",
                    type: "POST",
                    async: false,
                    data: {
                        name: function() {
                            return $("#unique_number").val();
                        },
                        id: "{{ $id }}"
                    }
                },
            },
            pprice: {
                required: true,
                number: true,
                min: 0
            }
        },
        messages: {
            category: {
                required: "Select a category."
            },
            name: {
                required: "Product name is required."
            },
            unique_number: {
                remote: "This product number is already exists."
            },
            pprice: {
                required: "Purchase price is required.",
                number: "Enter valid price format.",
                min: "Price can not be in negative amount."
            }
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent("div"));
        },
        submitHandler:function(form) {
            $('button[type="submit"]').attr('disabled', true);
            if(!this.beenSubmitted) {
                this.beenSubmitted = true;
                form.submit();
            }
        }
    });
});
</script>
@endsection
