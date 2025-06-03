@extends('layouts.master')

@section('breadcumb')
    <li class="f-14 f-400 c-7b">
        /
    </li>
    <li class="f-14 f-400 c-36">View </li>
@endsection

@section('content')

    {{ Config::set('app.module', $moduleName) }}
    <h2 class="f-24 f-700 c-36 my-2">View {{ $moduleName }}</h2>
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="product_id">Products : <span
                                class="text-danger">*</span></label>
                        <select name="product_id" id="product_id" class="select2 select2-hidden-accessible"
                            data-placeholder="--- Select a Category ---">
                            <option value=""> -- Select Option -- </option>
                            @if (!empty($products) && count($products) > 0)
                                @foreach ($products as $key => $product)
                                    @if ($slider->product_id == $key)
                                        <option value="{{$key}}" selected>{{$product}}</option>
                                    @else
                                        <option value="{{$key}}">{{$product}}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @if ($errors->has('product_id'))
                            <span class="text-danger d-block">{{ $errors->first('product_id') }}</span>
                        @endif
                    </div>
                     <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Slider Banner : <span class="text-danger">*</span></label>
                        <input type="file" name="main_image" id="main_image" class="form-control"
                            value="{{ $slider->main_image }}">
                        <span class="banner-preview position-relative">
                            @if (!empty($slider->main_image) && file_exists(storage_path('app/public/sliders-images/' . $slider->main_image)))
                                <span
                                    class="remove-banner text-danger fw-bold align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0 fs-2"
                                    style="cursor:pointer; z-index: 2;display:;"><i class="fa fa-close fs-4"
                                        style="color: white;" aria-hidden="true"></i>
                                </span>
                                <img src="{{asset('storage/sliders-images/' . $slider->main_image)}}" alt="Preview"
                                    style="object-fit: cover;height:125px;" class="mt-2 w-100 shadow-1-strong rounded">
                            @else
                                <span
                                    class="remove-banner text-danger fw-bold align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0 fs-2"
                                    style="cursor:pointer; z-index: 2;display:none;"><i class="fa fa-close fs-4"
                                        style="color: white;" aria-hidden="true"></i>
                                </span>
                                <img src="" alt="Preview" style="object-fit: cover;height:125px;"
                                    class="d-none mt-2 w-100 shadow-1-strong rounded">
                            @endif
                        </span>
                        @if ($errors->has('main_image'))
                            <span class="text-danger d-block">{{ $errors->first('main_image') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-9 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Slider Title : </label>
                        <textarea name="title" class="form-control ckeditorField" id="title" cols="30"
                            rows="10" placeholder="Enter page description">{{ old('title',$slider->title) }}</textarea>
                        @if ($errors->has('title'))
                            <span class="text-danger d-block">{{ $errors->first('title') }}</span>
                        @endif
                    </div>
                </div>
                {{-- <div class="col-md-9 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Slider Description : </label>
                        <textarea name="page_description" class="form-control ckeditorField" id="page_description" cols="30"
                            rows="10" placeholder="Enter page description">{{ old('page_description') }}</textarea>
                        @if ($errors->has('page_description'))
                        <span class="text-danger d-block">{{ $errors->first('page_description') }}</span>
                        @endif
                    </div>
                </div> --}}
            </div>
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="gift-img-container">
                        <div class="form-group">
                            @if (!empty($slider->gift_images))
                                @foreach (explode(',', $slider->gift_images) as $key => $images)
                                    @if (file_exists(storage_path('app/public/sliders-images/' . $images)))
                                        <div class="d-flex flex-wrap mb-2 gift-img-input">
                                            <label class="c-gr f-500 f-16 w-100 mb-2">Slider Gift Images : </label>
                                            <input type="file" name="gift_images[{{$key}}]" id="gift_images" class="form-control w-75"
                                                value="{{$images}}">
                                            <div class="input-group-btns ms-2">
                                                <button type="button" class="btn btn-primary addNewRow" data-index="0">
                                                    +
                                                </button>
                                                <button type="button" class="btn btn-danger removeRow" data-index="0">
                                                    -
                                                </button>
                                            </div>
                                            <span class="gift-banner-preview position-relative">
                                                <span
                                                    class="gift-remove-banner text-danger fw-bold align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0 fs-2"
                                                    style="cursor:pointer; z-index: 2;display:;">
                                                    <i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i>
                                                </span>
                                                <img src="{{asset('storage/sliders-images/' . $images)}}" alt="Preview"
                                                    style="object-fit: cover;height:125px;" class="mt-2 w-100 shadow-1-strong rounded">
                                            </span>
                                            @if ($errors->has('gift_images'))
                                                <span class="text-danger d-block">{{ $errors->first('gift_images') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="d-flex flex-wrap mb-2 gift-img-input">
                                    <label class="c-gr f-500 f-16 w-100 mb-2">Slider Gift Images : </label>
                                    <input type="file" name="gift_images[0]" id="gift_images" class="form-control w-75">
                                    <div class="input-group-btns ms-2">
                                        <button type="button" class="btn btn-primary addNewRow" data-index="0">
                                            +
                                        </button>
                                        <button type="button" class="btn btn-danger removeRow" data-index="0">
                                            -
                                        </button>
                                    </div>
                                    <span class="gift-banner-preview position-relative">
                                        <span
                                            class="gift-remove-banner text-danger fw-bold align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0 fs-2"
                                            style="cursor:pointer; z-index: 2;display:none;">
                                            <i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i>
                                        </span>
                                        <img src="" alt="Preview" style="object-fit: cover;height:125px;"
                                            class="d-none mt-2 w-100 shadow-1-strong rounded">
                                    </span>
                                    @if ($errors->has('gift_images'))
                                        <span class="text-danger d-block">{{ $errors->first('gift_images') }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('sliders.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
        </div>
    </div>
@endsection

<script src="{{ asset('assets/ckeditor/ckeditor.js') }}"></script>
@section('script')
    <script>
        $(document).ready(function () {

            $(".ckeditorField").each(function () {
                CKEDITOR.config.autoParagraph = false;
                CKEDITOR.replace($(this).attr("id"), {
                    enterMode: CKEDITOR.ENTER_BR,
                    shiftEnterMode: CKEDITOR.ENTER_BR
                });
            });

            $('body').on('input', '#page_title', function (e) {
                var name = $(this).val();
                var slug = name.replace(/[^a-zA-Z0-9\s\+\-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-');

                $('body').find('#slug').val(slug);
            });

            $("#updateSlider").validate({
                ignore: [],
                rules: {
                    title: {
                        required: true,
                    },
                    product_id: {
                        required: true,
                    },
                    // short_description: {
                    //     required: function (textarea) {
                    //         return CKEDITOR.instances['page_description'].getData().trim() === '';
                    //     }
                    // },
                    main_image: {
                        required: function () {
                            // Check if file input is empty AND no existing image preview is present
                            const inputVal = $('#main_image').val();
                            const hasExistingImage = $('.banner-preview img').attr('src') !== '';
                            return !inputVal && !hasExistingImage;
                        }
                    }
                },
                messages: {
                    title: {
                        required: "Slider title is required."
                    },
                    product_id: {
                        required: "Product is required.",
                    },
                    // short_description: {
                    //     required: "Short Description is required.",
                    // },
                    main_image: {
                        required: "Main banner image is required."
                    },
                },
                errorPlacement: function (error, element) {
                    var inputName = element.attr("name");
                    error.appendTo(element.parent("div"));
                },
                submitHandler: function (form) {
                    // for (instance in CKEDITOR.instances) {
                    //     CKEDITOR.instances[instance].updateElement();
                    // }
                    $('button[type="submit"]').attr('disabled', true);
                    if (!this.beenSubmitted) {
                        this.beenSubmitted = true;
                        form.submit();
                    }
                }
            });

            let mainImage = '{{ $slider->main_image }}';
            if (mainImage && mainImage != '') {
                $('.banner-preview').show();
            } else {
                $('.banner-preview').hide();
            }

            $(document).on("change", "#main_image", function () {
                const file = this.files[0];
                const previewImg = $(".banner-preview img");

                if (file) {
                    $('.banner-preview').show();
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg
                            .attr("src", e.target.result)
                            .removeClass("d-none");
                    };
                    $('.remove-banner').show();
                    reader.readAsDataURL(file);
                } else {
                    previewImg
                        .attr("src", "")
                        .addClass("d-none");
                }
            });

            $(document).on("click", ".remove-banner", function () {
                const previewContainer = $(".banner-preview");

                const src = previewContainer.find("img").attr("src");
                const filename = src ? src.split('/').pop() : null;

                if (filename) {
                    const removeInput = $('#remove_images');
                    let oldImages = removeInput.val() ? removeInput.val().split(',') : [];

                    if (!oldImages.includes(filename)) {
                        oldImages.push(filename);
                        removeInput.val(oldImages.join(','));
                    }
                }
                previewContainer.find("img").attr("src", "");

                $('.banner-preview').hide();

                $("#page_banner").val("");
            });

            let giftImages = '{{ $slider->gift_images }}';
            if (giftImages && giftImages != '') {
                $('.gift-banner-preview').show();
            } else {
                $('.gift-banner-preview').hide();
            }

            $(document).on("change", "#gift_images", function () {
                const file = this.files[0];
                const container = $(this).closest(".gift-img-input");
                const previewImg = container.find(".gift-banner-preview img");

                if (file) {
                    $('.gift-banner-preview').show();
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg
                            .attr("src", e.target.result)
                            .removeClass("d-none");
                    };
                    container.find('.gift-remove-banner').show();
                    reader.readAsDataURL(file);
                } else {
                    previewImg
                        .attr("src", "")
                        .addClass("d-none");
                }
            });

            $(document).on("click", ".gift-remove-banner", function () {
                const container = $(this).closest(".gift-img-input");
                const previewContainer = container.find(".gift-banner-preview");
                container.find("#gift_images").val("");

                const src = previewImg.attr("src");
                const filename = src ? src.split('/').pop() : null;

                if (filename) {
                    const removeInput = $('#remove_images');
                    let oldImages = removeInput.val() ? removeInput.val().split(',') : [];

                    if (!oldImages.includes(filename)) {
                        oldImages.push(filename);
                        removeInput.val(oldImages.join(','));
                    }
                }

                previewContainer.find("img").attr("src", "");
                previewContainer.hide();

            });

            $(document).on('click', '.addNewRow', function () {
                let currentGroup = $(this).closest('.gift-img-input');
                let newGroup = currentGroup.clone();
                newGroup.find('.gift-banner-preview img').attr("src", "").addClass("d-none");
                let inputLength = $('.gift-img-input').length;

                newGroup.find('input').val('');
                // newGroup.find('label').html(`Slider Gift Images (${inputLength+1}) : <span class="text-danger">*</span>`);

                newGroup.find('input').attr('name', `gift_images[${inputLength}]`);

                currentGroup.after(newGroup);
            });

            $(document).on('click', '.removeRow', function () {
                let allGroups = $('.gift-img-input');

                const src = allGroups.find('.gift-banner-preview img').attr("src");
                const filename = src ? src.split('/').pop() : null;

                if (filename) {
                    const removeInput = $('#remove_images');
                    let oldImages = removeInput.val() ? removeInput.val().split(',') : [];

                    if (!oldImages.includes(filename)) {
                        oldImages.push(filename);
                        removeInput.val(oldImages.join(','));
                    }
                }

                if (allGroups.length > 1) {
                    $(this).closest('.gift-img-input').remove();
                }
            });

        });
    </script>
@endsection