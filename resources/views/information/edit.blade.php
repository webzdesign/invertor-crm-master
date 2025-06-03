@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Edit </li>
@endsection

@section('content')

{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Edit {{ $moduleName }}</h2>
<form action="{{ route('information.update', ['id' => encrypt($info->id)]) }}" method="POST" id="updateInformationPage" enctype="multipart/form-data"> @csrf
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Page Title : <span class="text-danger">*</span></label>
                        <input type="text" name="page_title" id="page_title" value="{{ old('page_title', $info->page_title) }}" class="form-control" placeholder="Product Title">
                        @if ($errors->has('page_title'))
                            <span class="text-danger d-block">{{ $errors->first('page_title') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Page Url : <span class="text-danger">*</span></label>
                        <input type="text" readonly name="slug" id="slug" value="{{ old('slug',$info->slug) }}" class="form-control" placeholder="Page URL">
                        @if ($errors->has('slug'))
                            <span class="text-danger d-block">{{ $errors->first('slug') }}</span>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Information Page Banner : <span class="text-danger">*</span></label>
                        <input type="hidden" name="old_banner" id="old_banner" value="{{$info->page_banner}}">
                        <input type="file" name="page_banner" id="page_banner" class="form-control">
                        <span class="banner-preview position-relative">
                            <span class="remove-banner text-danger fw-bold d-flex align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0 fs-2" style="cursor:pointer; z-index: 2;"><i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i></span>
                            <img src="{{ url('/storage/app/public/information-images/'.$info->page_banner) }}" alt="Preview" style="object-fit: cover;height:125px;" class="mt-2 w-100 shadow-1-strong rounded">
                        </span>
                        @if ($errors->has('page_banner'))
                            <span class="text-danger d-block">{{ $errors->first('page_banner') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-9 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Page Description : </label>
                        <textarea name="page_description" class="form-control ckeditorField" id="page_description" cols="30" rows="10" placeholder="Enter page description">{{ old('page_description', $info->page_description) }}</textarea>
                        @if ($errors->has('page_description'))
                            <span class="text-danger d-block">{{ $errors->first('page_description') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('information.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
            <button type="submit" class="btn-primary f-500 f-14">Save</button>
        </div>
    </div>
</form>
@endsection

<script src="{{ asset('assets/ckeditor/ckeditor.js') }}"></script>
@section('script')
<script>
$(document).ready(function(){

    $(".ckeditorField").each(function() {
		CKEDITOR.config.autoParagraph = false;
		CKEDITOR.replace($(this).attr("id"), {
			enterMode: CKEDITOR.ENTER_BR,
			shiftEnterMode: CKEDITOR.ENTER_BR
		});
	});
    
    $('body').on('input', '#page_title', function(e) {
        var name = $(this).val();
        var slug = name.replace(/[^a-zA-Z0-9\s\+\-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-');

        $('body').find('#slug').val(slug);
    });

    $("#updateInformationPage").validate({
        ignore: [],
        rules: {
            page_title: {
                required: true,                
            },
            slug: {
                required: true,
            },
            page_description: {
                required: function(textarea) {
                    return CKEDITOR.instances['page_description'].getData().trim() === '';
                }
            },
        },
        messages: {
            page_title: {
                required: "Page title is required."
            },
            slug: {
                required: "Slug is required.",
                remote: "Slug already exists.",
            },
            page_description: {
                required: "Page Description is required.",
            }
        },
        errorPlacement: function(error, element) {
            var inputName = element.attr("name");
            console.log("Input name:", inputName);
            error.appendTo(element.parent("div"));
        },
        submitHandler:function(form) {
            for (instance in CKEDITOR.instances) {
                CKEDITOR.instances[instance].updateElement();
            }
            $('button[type="submit"]').attr('disabled', true);
            if(!this.beenSubmitted) {
                this.beenSubmitted = true;
                form.submit();
            }
        }
    });

    $(document).on("change","#page_banner", function() {
        const file = this.files[0];
        const previewImg = $(".banner-preview img");

        if (file) {
            $('.banner-preview').show();
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg
                    .attr("src", e.target.result)
                    .removeClass("d-none");
            };
            reader.readAsDataURL(file);
        } else {
            previewImg
                .attr("src", "")
                .addClass("d-none");
        }
    });

    $(document).on("click", ".remove-banner", function() {
        const previewContainer = $(".banner-preview");
        previewContainer.find("img").attr("src", "");
        $('#old_banner').val("");
        $('.banner-preview').hide();
        $("#page_banner").val("");
    });

});
</script>
@endsection