@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Add  </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
<form action="{{ route('brands.store') }}" method="POST" id="addCategory" enctype="multipart/form-data"> @csrf
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Brand Name : <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" placeholder="Enter brand name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="category_id">Category Name : <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-control select2 select2-hidden-accessible" data-placeholder="--- Select a Category ---">
                            <option value="" >--- Select a Category ---</option>
                            @if (!empty($categorys) && count($categorys) > 0)
                                @foreach ($categorys as $category)
                                    <option value="{{ $category->id }}" >{{ $category->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Brand Logo : <span class="text-danger">*</span></label>
                        <input type="file" name="brand_logo" id="brand_logo" class="form-control">
                        <span class="logo-preview position-relative">
                                <span class="remove-logo text-danger fw-bold align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0" style="cursor:pointer; z-index: 2;display:none;">
                                    <i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i>
                                </span>
                                <img src="" alt="Preview" style="object-fit: cover;height:125px;" class="d-none mt-2 w-100 shadow-1-strong rounded">
                            </span>
                        @if ($errors->has('brand_logo'))
                            <span class="text-danger d-block">{{ $errors->first('brand_logo') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('brands.index') }}">
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

    $("#addCategory").validate({
        rules: {
            name: {
                required: true,
                remote: {
                    url: "{{ url('checkBrands') }}",
                    type: "POST",
                    async: false,
                    data: {
                        name: function() {
                            return $("#name").val();
                        },
                    }
                },
            },
            category_id: {
                required: true
            },
            brand_logo: {
                required: true
            }
        },
        messages: {
            name: {
                required: "Brand Name is required.",
                remote: "This Brand is already exists.",
            },
            category_id: {
                required: "Please select category!!"
            },
            brand_logo: {
                required: "Brand logo is require."
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

    $('.logo-preview').hide();
    $(document).on("change", "#brand_logo", function () {
        const file = this.files[0];
        const previewImg = $(".logo-preview img");

        if (file) {
            $('.logo-preview').show();
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg
                    .attr("src", e.target.result)
                    .removeClass("d-none");
            };
            $('.remove-logo').show();
            reader.readAsDataURL(file);
        } else {
            previewImg
                .attr("src", "")
                .addClass("d-none");
        }
    });

    $(document).on("click", ".remove-logo", function () {
        const previewContainer = $(".logo-preview");
        previewContainer.find("img").attr("src", "");
        $('.logo-preview').hide();

        $("#brand_logo").val("");
    });
});
</script>
@endsection
