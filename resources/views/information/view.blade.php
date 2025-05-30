@extends('layouts.master')

@section('breadcumb')
    <li class="f-14 f-400 c-7b">
        /
    </li>
    <li class="f-14 f-400 c-36">Add </li>
@endsection

@section('content')

    {{ Config::set('app.module', $moduleName) }}
    <h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md-3 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Page Title : </label>
                        <input type="text" name="page_title" id="page_title"
                            value="{{ old('page_title', $info->page_title) }}" class="form-control"
                            placeholder="Product Title">
                    </div>
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Page Url : </label>
                        <input type="text" readonly name="slug" id="slug" value="{{ old('slug', $info->slug) }}"
                            class="form-control" placeholder="Page URL">
                    </div>
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Page Banner : </label>
                        @if (!empty($info->page_banner))
                            <span class="banner-preview">
                                <img src="{{ url('/storage/app/public/information-images/'.$info->page_banner) }}" alt="Preview" style="object-fit: cover;height:125px;" class="mt-2 w-100 shadow-1-strong rounded">
                            </span>
                        @endif
                    </div>
                </div>

                <div class="col-md-9 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Page Description : </label>
                        <textarea name="page_description" class="form-control ckeditorField" id="page_description" cols="30"
                            rows="10"
                            placeholder="Enter page description">{{ old('page_description', $info->page_description) }}</textarea>
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
        });
    </script>
@endsection