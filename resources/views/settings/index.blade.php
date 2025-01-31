@extends('layouts.master')

{{ Config::set('app.module',$moduleName) }}

@section('css')
<style>
</style>
@endsection

@section('content')

<h2 class="f-24 f-700 c-36 my-2"> {{ $moduleName }}</h2>
<form action="{{ route('settings.update') }}" method="POST" id="settingsUpdate" enctype="multipart/form-data"> @csrf @method('PUT')
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">

                <blockquote class="blockquote text-center">
                    <p class="mb-0 f-700">App Information</p>
                </blockquote>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Name : <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $settings->title) }}" class="form-control" placeholder="Enter name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Logo : <span class="text-danger">*</span></label>
                        <input type="file" name="logo" id="logo" class="form-control">
                        @if ($errors->has('logo'))
                            <span class="text-danger d-block">{{ $errors->first('logo') }}</span>
                        @endif
                        @if(empty($settings->logo))
                        <a href="{{ asset(Helper::$appLogo) }}" target="_blank">
                            <img src="{{ asset(Helper::$appLogo) }}" style="height:50px;" class="mt-2">
                        </a>
                        @elseif(!empty($settings->logo) && file_exists(public_path("assets/images/$settings->logo")))
                        <a href="{{ asset("assets/images/{$settings->logo}") }}" target="_blank">
                            <img src="{{ asset("assets/images/{$settings->logo}") }}" style="height:50px;" class="mt-2">
                        </a>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Favicon : <span class="text-danger">*</span></label>
                        <input type="file" name="favicon" id="favicon" class="form-control">
                        @if ($errors->has('favicon'))
                            <span class="text-danger d-block">{{ $errors->first('favicon') }}</span>
                        @endif
                        @if(empty($settings->favicon))
                        <a href="{{ asset(Helper::$favIcon) }}" target="_blank">
                            <img src="{{ asset(Helper::$favIcon) }}" style="height:50px;" class="mt-2">
                        </a>
                        @elseif(!empty($settings->favicon) && file_exists(public_path("assets/images/$settings->favicon")))
                        <a href="{{ asset("assets/images/{$settings->favicon}") }}" target="_blank">
                            <img src="{{ asset("assets/images/{$settings->favicon}") }}" style="height:50px;" class="mt-2">
                        </a>
                        @endif
                    </div>
                </div>

                <blockquote class="blockquote text-center">
                    <p class="mt-2 f-700">App Credentials</p>
                </blockquote>

                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Google Geocode API Key : <span class="text-danger">*</span></label>
                        <input type="text" name="geocode" id="geocode" value="{{ old('geocode', $settings->geocode_key) }}" class="form-control" placeholder="Enter API Key">
                        @if ($errors->has('geocode'))
                            <span class="text-danger d-block">{{ $errors->first('geocode') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Google Sheet ID : <span class="text-danger">*</span></label>
                        <input type="text" name="gsheetid" id="gsheetid" value="{{ old('gsheetid', $settings->google_sheet_id) }}" class="form-control" placeholder="Enter Sheet ID">
                        @if ($errors->has('gsheetid'))
                            <span class="text-danger d-block">{{ $errors->first('gsheetid') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Twilio Account Id : <span class="text-danger">*</span></label>
                        <input type="text" name="twilioAccountSid" id="twilioAccountSid" value="{{ old('twilioAccountSid', $settings->twilioAccountSid) }}" class="form-control" placeholder="Enter Twillo Account ID">
                        @if ($errors->has('twilioAccountSid'))
                            <span class="text-danger d-block">{{ $errors->first('twilioAccountSid') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Twilio Auth Token : <span class="text-danger">*</span></label>
                        <input type="text" name="twilioAuthToken" id="twilioAuthToken" value="{{ old('twilioAuthToken', $settings->twilioAuthToken) }}" class="form-control" placeholder="Enter Twilio Auth Token">
                        @if ($errors->has('twilioAuthToken'))
                            <span class="text-danger d-block">{{ $errors->first('twilioAuthToken') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Twilio Url : <span class="text-danger">*</span></label>
                        <input type="url" name="twilioUrl" id="twilioUrl" value="{{ old('twilioUrl', $settings->twilioUrl) }}" class="form-control" placeholder="Enter Twilio Url">
                        @if ($errors->has('twilioUrl'))
                            <span class="text-danger d-block">{{ $errors->first('twilioUrl') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Twilio From Number : <span class="text-danger">*</span></label>
                        <input type="text" name="twilioFromNumber" id="twilioFromNumber" value="{{ old('twilioFromNumber', $settings->twilioFromNumber) }}" class="form-control" placeholder="Enter Twilio From Number">
                        @if ($errors->has('twilioFromNumber'))
                            <span class="text-danger d-block">{{ $errors->first('twilioFromNumber') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Twilio Template Url : <span class="text-danger">*</span></label>
                        <input type="url" name="twilloTemplateUrl" id="twilloTemplateUrl" value="{{ old('twilloTemplateUrl', $settings->twilloTemplateUrl) }}" class="form-control" placeholder="Enter Twilio template Url">
                        @if ($errors->has('twilloTemplateUrl'))
                            <span class="text-danger d-block">{{ $errors->first('twilloTemplateUrl') }}</span>
                        @endif
                    </div>
                </div>

                <!-- social icon links -->
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Facebook Url : </label>
                        <input type="text" name="facebookUrl" id="facebookUrl" value="{{ old('facebookUrl', $settings->facebookUrl) }}" class="form-control" placeholder="Enter Facebook Url">
                        @if ($errors->has('facebookUrl'))
                            <span class="text-danger d-block">{{ $errors->first('facebookUrl') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Linkdin Url : </label>
                        <input type="text" name="linkdinUrl" id="linkdinUrl" value="{{ old('linkdinUrl', $settings->linkdinUrl) }}" class="form-control" placeholder="Enter Linkdin Url">
                        @if ($errors->has('linkdinUrl'))
                            <span class="text-danger d-block">{{ $errors->first('linkdinUrl') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Instagram Url : </label>
                        <input type="text" name="instgramUrl" id="instgramUrl" value="{{ old('instgramUrl', $settings->instgramUrl) }}" class="form-control" placeholder="Enter Instagram  Url">
                        @if ($errors->has('instgramUrl'))
                            <span class="text-danger d-block">{{ $errors->first('instgramUrl') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> TikTok Url : </label>
                        <input type="text" name="tiktokUrl" id="tiktokUrl" value="{{ old('tiktokUrl', $settings->tiktokUrl) }}" class="form-control" placeholder="Enter Tiktok Url">
                        @if ($errors->has('tiktokUrl'))
                            <span class="text-danger d-block">{{ $errors->first('tiktokUrl') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2"> Youtube Url : </label>
                        <input type="text" name="youtubeUrl" id="youtubeUrl" value="{{ old('youtubeUrl', $settings->youtubeUrl) }}" class="form-control" placeholder="Enter Youtube Url">
                        @if ($errors->has('youtubeUrl'))
                            <span class="text-danger d-block">{{ $errors->first('youtubeUrl') }}</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <button type="submit" class="btn-primary f-500 f-14">Save</button>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
$(document).ready(function(){

    $.validator.addMethod("logoext", function(value, element, param) {
        var fileTypes = param.split('|');
        var files = element.files;
        for (var i = 0; i < files.length; i++) {
            var extension = files[i].name.split('.').pop().toLowerCase();
            if ($.inArray(extension, fileTypes) === -1) {
                return false;
            }
        }
        return true;
    }, "Only .png, .jpg, .jpeg and .webp extensions supported");

    $.validator.addMethod("logolim", function(value, element, param) {
        var totalSize = 0;
        var files = element.files;
        for (var i = 0; i < files.length; i++) {
            totalSize += files[i].size;
        }
        return totalSize <= param;
    }, "Total file size must not exceed 2 MB");

    $.validator.addMethod("favext", function(value, element, param) {
        var fileTypes = param.split('|');
        var files = element.files;
        for (var i = 0; i < files.length; i++) {
            var extension = files[i].name.split('.').pop().toLowerCase();
            if ($.inArray(extension, fileTypes) === -1) {
                return false;
            }
        }
        return true;
    }, "Only .ico extensions supported");

    $.validator.addMethod("favlim", function(value, element, param) {
        var totalSize = 0;
        var files = element.files;
        for (var i = 0; i < files.length; i++) {
            totalSize += files[i].size;
        }
        return totalSize <= param;
    }, "Total file size must not exceed 1 MB");

    $('#settingsUpdate').validate({
        rules: {
            name: {
                required: true
            },
            geocode: {
                required: true
            },
            gsheetid: {
                required: true
            },
            logo: {
                logoext: 'png|jpg|jpeg|webp',
                logolim: (1024 * 1024) * 2
            },
            favicon: {
                favext: 'ico',
                favlim: 1024 * 1024
            },
            twilioAccountSid: {
                required: true
            },
            twilioAuthToken: {
                required: true
            },
            twilioUrl: {
                required: true
            },
            twilioFromNumber: {
                required: true
            },
            twilloTemplateUrl:{
                required: true
            }
        },
        messages: {
            name: {
                required: "Name is required"
            },
            geocode: {
                required: "Geocode API key is required"
            },
            gsheetid: {
                required: "Google sheet id is required"
            },
            twilioAccountSid: {
                required: "Twilio account id is required"
            },
            twilioAuthToken: {
                required: "Twilio auth token is required"
            },
            twilioUrl: {
                required: "Twilio url is required"
            },
            twilioFromNumber: {
                required: "Twilio from number is required"
            },
            twilloTemplateUrl: {
                required: "Twilio template url is required"
            }
        }
    });

});
</script>
@endsection
