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
<form action="{{ route('gifts.update',['id' => encrypt($gift->id)]) }}" method="POST" id="updateGifts" enctype="multipart/form-data"> @csrf @method('PUT')
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Gift Title : <span class="text-danger">*</span></label>
                        <input type="text" name="gift_title" id="gift_title" value="{{ old('name',$gift->gift_title) }}" class="form-control" placeholder="Enter brand name">
                        @if ($errors->has('gift_title'))
                            <span class="text-danger d-block">{{ $errors->first('gift_title') }}</span>
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
                                    @if ($category->id == $gift->category_id)
                                        <option value="{{ $category->id }}" selected >{{ $category->name }}</option>
                                    @else
                                        <option value="{{ $category->id }}" >{{ $category->name }}</option>
                                    @endif
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
                        <label class="c-gr f-500 f-16 w-100 mb-2">Gift Image : <span class="text-danger">*</span></label>
                        <input type="file" name="gift_images" id="gift_images" class="form-control" >
                        <input type="hidden" name="old_image" id="old_image">
                        <input type="hidden" name="existing_image" id="existing_image" value="{{ $gift->gift_images }}">
                        @if (!empty($gift->gift_images) && file_exists(storage_path('app/public/gifts-images/' . $gift->gift_images)))
                            <span class="logo-preview position-relative">
                                <span class="remove-logo text-danger fw-bold align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0" style="cursor:pointer; z-index: 2;display:;">
                                    <i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i>
                                </span>
                                <img src="{{ asset('storage/gifts-images/' . $gift->gift_images) }}" alt="Preview" style="object-fit: cover;height:125px;" class="mt-2 w-100 shadow-1-strong rounded">
                            </span>
                        @else
                            <span class="logo-preview position-relative">
                                <span class="remove-logo text-danger fw-bold align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0" style="cursor:pointer; z-index: 2;display:none;">
                                    <i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i>
                                </span>
                                <img src="" alt="Preview" style="object-fit: cover;height:125px;" class="d-none mt-2 w-100 shadow-1-strong rounded">
                            </span>
                        @endif
                        @if ($errors->has('gift_images'))
                            <span class="text-danger d-block">{{ $errors->first('gift_images') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('gifts.index') }}">
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

    $("#updateGifts").validate({
        rules: {
            gift_title: {
                required: true,
            },
            category_id: {
                required: true
            },
            gift_images: {
                required: {
                    depends: function(element) {
                        return $('#existing_image').val().trim() === '';
                    }
                }
            }
        },
        messages: {
            gift_title: {
                required: "Gift Title is required.",
            },
            category_id: {
                required: "Please select category!!"
            },
            gift_images: {
                required: "Gift Image is require."
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

    let oldImg = '{{ $gift->gift_images }}';
    if(oldImg && oldImg != '') {
        $('.logo-preview').show();
    } else {
        $('.logo-preview').hide();
    }
    
    $(document).on("change", "#gift_images", function () {
        const file = this.files[0];
        const previewImg = $(".logo-preview img");

        if (file) {
            let src = previewImg.attr("src").split('/').pop();
            
            if(src){
                let allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                let extension = src.split('.').pop().toLowerCase();
                if(allowedExtensions.includes(extension)) {
                    $('#old_image').val(src);
                }
            }
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
        let src = previewContainer.find("img").attr("src").split('/').pop();
        $('#existing_image').val('');
        if(src){
            let allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            let extension = src.split('.').pop().toLowerCase();
            if(allowedExtensions.includes(extension)) {
                $('#old_image').val(src);
            }
        }

        previewContainer.find("img").attr("src", "");
        $('.logo-preview').hide();

        $("#gift_images").val("");
    });
});
</script>
@endsection
