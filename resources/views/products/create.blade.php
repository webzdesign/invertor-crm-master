@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Add </li>
@endsection

@section('content')

{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
<form action="{{ route('products.store') }}" method="POST" id="addProduct" enctype="multipart/form-data"> @csrf
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">

                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Number : </label>
                        <input type="text" class="form-control" value="{{ $prodNo }}" readonly>
                    </div>
                </div>

                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Category : <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Category ---">
                            @forelse($categories as $cid => $cname)
                                @if($loop->first)
                                <option value="" selected> --- Select a Category --- </option>
                                @endif
                                <option value="{{ $cid }}"> {{ $cname }} </option>
                            @empty                                
                                <option value=""> --- No Category Found --- </option>
                            @endforelse
                        </select>
                        @if ($errors->has('category'))
                            <span class="text-danger d-block">{{ $errors->first('category') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Name : <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" placeholder="Enter product name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Website Sales Price :</label>
                        <input type="text" name="web_sales_price" id="web_sales_price" value="{{ old('web_sales_price') }}" class="form-control" placeholder="Enter Website Sales Price">
                        @if ($errors->has('web_sales_price'))
                            <span class="text-danger d-block">{{ $errors->first('web_sales_price') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Detail URL : <span class="text-danger">*</span></label>
                        <input type="text" readonly name="slug" id="slug" value="{{ old('slug') }}" class="form-control" placeholder="Product URL">
                        @if ($errors->has('slug'))
                            <span class="text-danger d-block">{{ $errors->first('slug') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-9 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Description : </label>
                        <textarea name="description" class="form-control" id="description" cols="30" rows="10" placeholder="Enter product description">{{ old('description') }}</textarea>
                        @if ($errors->has('description'))
                            <span class="text-danger d-block">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('products.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
            <button type="submit" class="btn-primary f-500 f-14">Save</button>
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

    $('body').on('keyup', '#name', function(e) {
        var name = $(this).val();
        var slug = name.replace(/[^a-zA-Z0-9\s\+\-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-');

        $('body').find('#slug').val(slug);
    });

    $("#addProduct").validate({
        rules: {
            category: {
                required: true,                
            },
            name: {
                required: true,                
            },
            slug: {
                required: true,
                remote: {
                    url: "{{ url('checkProductSlug') }}",
                    type: "POST",
                    async: false,
                    data: {
                        slug: function() {
                            return $("#slug").val();
                        }
                    }
                }
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
                    }
                },
            },
            pprice: {
                required: true,
                number: true,
                min: 0
            },
            web_sales_price: {
                number: true,
                min: 0
            },
        },
        messages: {
            category: {
                required: "Select a category."
            },
            name: {
                required: "Product name is required."
            },
            slug: {
                required: "Slug is required.",
                remote: "Slug already exists.",
            },
            unique_number: {
                remote: "This product number is already exists.",
            },
            pprice: {
                required: "Purchase price is required.",
                number: "Enter valid price format.",
                min: "Price can not be in negative amount."
            },
            web_sales_price: {
                number: "Enter valid price format.",
                min: "Price can not be in negative amount."
            },
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
