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
                            <label class="c-gr f-500 f-16 w-100 mb-2">Page Title : <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="page_title" id="page_title"
                                value="{{ old('page_title', $info->page_title) }}" class="form-control"
                                placeholder="Product Title">
                            @if ($errors->has('page_title'))
                                <span class="text-danger d-block">{{ $errors->first('page_title') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Page Url : <span class="text-danger">*</span></label>
                            <input type="text" readonly name="slug" id="slug" value="{{ old('slug', $info->slug) }}"
                                class="form-control" placeholder="Page URL">
                            @if ($errors->has('slug'))
                                <span class="text-danger d-block">{{ $errors->first('slug') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-9 col-sm-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Page Description : </label>
                            <textarea name="page_description" class="form-control ckeditorField" id="page_description"
                                cols="30" rows="10"
                                placeholder="Enter page description">{{ old('page_description', $info->page_description) }}</textarea>
                            @if ($errors->has('page_description'))
                                <span class="text-danger d-block">{{ $errors->first('page_description') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    @php
                        $pageBanners = json_decode($info->page_banner ?? '{}');
                    @endphp

                    @if (!empty($langs))
                        @foreach ($langs as $lang)
                            @php
                                $image = $pageBanners->$lang->image ?? '';
                            @endphp
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="c-gr f-500 f-16 w-100 mb-2">
                                        Information Page Desktop Banner ({{ strtoupper($lang) }}) :
                                        {{-- <span class="text-danger">*</span> --}}
                                    </label>

                                    <input type="hidden" name="old_banner[{{ $lang }}]" class="old-banner" data-lang="{{ $lang }}"
                                        value="">

                                    <input type="file" name="page_banner[{{ $lang }}]" class="form-control page-banner-input"
                                        data-lang="{{ $lang }}">

                                    <span class="banner-preview position-relative" data-lang="{{ $lang }}">
                                        <span
                                            class="remove-banner text-danger fw-bold d-flex align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0 fs-2 {{ $image ? '' : 'd-none' }}"
                                            style="cursor:pointer; z-index: 2;">
                                            <i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i>
                                        </span>
                                        <img src="{{ $image ? url('/storage/app/public/information-images/' . $image) : '' }}"
                                            alt="Preview" style="object-fit: cover; height: 125px;"
                                            class="mt-2 w-100 shadow-1-strong rounded {{ $image ? '' : 'd-none' }}">
                                    </span>

                                    @if ($errors->has("page_banner.$lang"))
                                        <span class="text-danger d-block">{{ $errors->first("page_banner.$lang") }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="row">
                    @php
                        $pageBanners = json_decode($info->page_banner ?? '{}');
                    @endphp

                    @if (!empty($langs))
                        @foreach ($langs as $lang)
                            @php
                                $mobimage = $pageBanners->$lang->mob_image ?? '';
                            @endphp
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group">
                                    <label class="c-gr f-500 f-16 w-100 mb-2">
                                        Information Page Mobile Banner ({{ strtoupper($lang) }}) :
                                        {{-- <span class="text-danger">*</span> --}}
                                    </label>

                                    <input type="hidden" name="old_banner_mob[{{ $lang }}]" class="old-banner-mob" data-lang="{{ $lang }}"
                                        value="">

                                    <input type="file" name="page_banner_mob[{{ $lang }}]" class="form-control page-banner-input-mob"
                                        data-lang="{{ $lang }}">

                                        @if($mobimage && file_exists(storage_path('app/public/information-images/'.$mobimage))) 
                                            <span class="banner-preview-mob position-relative" data-lang="{{ $lang }}">
                                                <span
                                                    class="remove-banner-mob text-danger fw-bold d-flex align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0 fs-2 {{ $image ? '' : 'd-none' }}"
                                                    style="cursor:pointer; z-index: 2;">
                                                    <i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i>
                                                </span>
                                                <img src="{{ $mobimage ? asset('storage/information-images/' . $mobimage) : '' }}"
                                                    alt="Preview" style="object-fit: cover; height: 125px;"
                                                    class="mt-2 w-100 shadow-1-strong rounded {{ $mobimage ? '' : 'd-none' }}">
                                            </span>
                                        @else
                                            <span class="banner-preview-mob position-relative" data-lang="{{ $lang }}">
                                                <span
                                                    class="remove-banner-mob text-danger fw-bold d-flex align-items-center bg-danger rounded-circle ps-1 pe-1 position-absolute end-0 fs-2 d-none"
                                                    style="cursor:pointer; z-index: 2;">
                                                    <i class="fa fa-close fs-4" style="color: white;" aria-hidden="true"></i>
                                                </span>
                                                <img src=""
                                                    alt="Preview" style="object-fit: cover; height: 125px;"
                                                    class="mt-2 w-100 shadow-1-strong rounded d-none">
                                            </span>
                                        @endif

                                    @if ($errors->has("page_banner.$lang"))
                                        <span class="text-danger d-block">{{ $errors->first("page_banner.$lang") }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="cardsFooter d-flex justify-content-center">
                <a href="{{ route('information.index') }}">
                    <button type="button" class="btn-default f-500 f-14">Cancel</button>
                </a>
            </div>
        </div>
@endsection

{{-- <script src="{{ asset('assets/ckeditor/ckeditor.js') }}"></script> --}}
@section('script')
    <script>
        $(document).ready(function () {

            $(".ckeditorField").each(function () {
                // CKEDITOR.config.autoParagraph = false;
                // CKEDITOR.replace($(this).attr("id"), {
                //     enterMode: CKEDITOR.ENTER_BR,
                //     shiftEnterMode: CKEDITOR.ENTER_BR
                // });
                initEditor(`#${$(this).attr("id")}`);
            });

            $('body').on('input', '#page_title', function (e) {
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
                    // page_description: {
                    //     required: function (textarea) {
                    //         // return CKEDITOR.instances['page_description'].getData().trim() === '';
                    //     }
                    // },
                    // 'page_banner[]': {
                    //     required: true,
                    // }
                },
                messages: {
                    page_title: {
                        required: "Page title is required."
                    },
                    slug: {
                        required: "Page url is required.",
                    },
                    page_description: {
                        required: "Page Description is required.",
                    },
                    // 'page_banner[]': {
                    //     required: "Page banner is required.",
                    // }
                },
                errorPlacement: function (error, element) {
                    var inputName = element.attr("name");
                    error.appendTo(element.parent("div"));
                },
                submitHandler: function (form) {
                    $('button[type="submit"]').attr('disabled', true);
                    if (!this.beenSubmitted) {
                        this.beenSubmitted = true;
                        form.submit();
                    }
                }
            });

            $(document).on("change", ".page-banner-input", function () {
                const file = this.files[0];
                const lang = $(this).data('lang');
                const previewContainer = $(`.banner-preview[data-lang="${lang}"]`);
                const previewImg = previewContainer.find("img");
                const removeBtn = previewContainer.find(".remove-banner");

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.attr("src", e.target.result).removeClass("d-none");
                    };
                    reader.readAsDataURL(file);

                    previewContainer.show();
                    removeBtn.removeClass("d-none");
                } else {
                    previewImg.attr("src", "").addClass("d-none");
                    previewContainer.addClass("d-none");
                }
            });

            $(document).on("click", ".remove-banner", function () {
                const previewContainer = $(this).closest(".banner-preview");
                const lang = previewContainer.data("lang");
                const input = $(`.page-banner-input[data-lang="${lang}"]`);
                const oldBannerInput = $(`.old-banner[data-lang="${lang}"]`);
                let imgSrc = previewContainer.find("img").attr("src").split('/').pop();
                oldBannerInput.val(imgSrc);
                previewContainer.find("img").attr("src", "").addClass("d-none");
                previewContainer.hide();
                input.val("");
                $(this).hide();
            });

            $(document).on("change", ".page-banner-input-mob", function () {
                const file = this.files[0];
                const lang = $(this).data('lang');
                const previewContainer = $(`.banner-preview-mob[data-lang="${lang}"]`);
                const previewImg = previewContainer.find("img");
                const removeBtn = previewContainer.find(".remove-banner-mob");

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImg.attr("src", e.target.result).removeClass("d-none");
                    };
                    reader.readAsDataURL(file);

                    previewContainer.show();
                    removeBtn.removeClass("d-none");
                } else {
                    previewImg.attr("src", "").addClass("d-none");
                    previewContainer.addClass("d-none");
                }
            });

            $(document).on("click", ".remove-banner-mob", function () {
                const previewContainer = $(this).closest(".banner-preview-mob");
                const lang = previewContainer.data("lang");
                const input = $(`.page-banner-input-mob[data-lang="${lang}"]`);
                const oldBannerInput = $(`.old-banner-mob[data-lang="${lang}"]`);
                let imgSrc = previewContainer.find("img").attr("src").split('/').pop();
                oldBannerInput.val(imgSrc);
                previewContainer.find("img").attr("src", "").addClass("d-none");
                previewContainer.hide();
                input.val("");
                $(this).hide();
            });
        });
    </script>
@endsection