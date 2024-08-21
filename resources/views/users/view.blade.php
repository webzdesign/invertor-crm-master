@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/intel.css') }}">
<style>
    .iti__selected-flag {
        height: 32px!important;
    }
    .iti--show-flags {
        width: 100%!important;
    }
    .border-box-element {
        border: 1px solid black;
        border-radius: 10px;
        word-break: break-all;
        height: 100px;
        width: 100px;
        font-size: smaller;
        margin-right: 5px;
    }

    .border-box-element-inner {
        height: 100%;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .border-box-element-inner i{
        font-size: 40px;
    }

    .remove {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        right: -12px;
        top: -10px;
        border: none;
    }
</style>
@endsection

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">View </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">View {{ $moduleName }}</h2>

    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Name </label>
                        <input type="text" id="name" value="{{ $user->name }}" class="form-control" readonly>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Email </label>
                        <input type="email" id="email" value="{{ $user->email }}" class="form-control" readonly>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Phone Number </label>
                        <input type="text" id="phone" value="{{ $user->phone }}" class="form-control" readonly>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Role </label>
                        <input type="text" class="form-control" readonly value="{{ implode(', ', $user->roles->pluck('name')->toArray() ?? []) ?? '' }}" >
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Country </label>
                        <input type="text" class="form-control" readonly value="{{ $user->country->name ?? '' }}">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">City </label>
                        <input type="text" class="form-control" readonly value="{{ $user->city_id }}">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Postal Code </label>
                        <input type="text" id="postal_code" value="{{ $user->postal_code }}" class="form-control" readonly>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Address Line </label>
                        <textarea readonly id="address_line_1" class="form-control">{{ old('address_line_1', $user->address_line_1) }}</textarea>
                    </div>
                </div>

            </div>
        </div>

        <div class="cardsBody py-0">
            <label class="c-gr f-500 f-16 w-100 mb-2">Permissions</label>
            <div class="form-group">
                <div class="row">
                    @foreach($permission as $key => $value)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-3 permission-listing" @if($key == 'SalesOrderStatus' && $user->roles->first()->id == '3') style="display:none;" @endif >
                            <div class="PlBox">
                                @foreach($value as $k => $v)
                                    @if($loop->first)
                                    <li class="list-group-item inline bg-transparent border-0 p-0 mb-2">
                                        <label class="c-gr w-100 mb-2 f-14">
                                            <span class="c-primary f-700">{{ Helper::spaceBeforeCap($v->model) }}</span>
                                        </label>
                                    </li>
                                    @endif
                                    <li class="form-check">
                                        <input type="checkbox" class="form-check-input permission" name="permission[]" id="{{ $v->id }}" value="{{ $v->id }}" aria-label="..." @if(in_array($v->id,$userPermissions)) checked @endif disabled>
                                        <label for="{{ $v->id }}" class="form-check-label mb-0 f-14 f-500 aside-input-checbox">{{ $v->name }}</label>
                                    </li>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>


        {{-- Documents --}}
        <div class="cardsBody py-0">
            <label class="c-gr f-500 f-16 w-100 mb-2">Uploaded Documents</label>
            <div class="form-group">
                <div class="row">
                    @forelse($documents as $key => $doc)
                    @if(isset($docNames[$key]))
                        <div class="col-12">
                            <p> {!! $docNames[$key]['name'] !!} </p>
                            <div class="form-group">
                                <div class="row">
                                    @forelse($doc as $file)
                                    <div class="border-box-element">
                                        <a href="{{ asset("storage/documents/{$file->name}") }}" target="_blank" title="{{ $file->name }}">
                                            <div class="border-box-element-inner">
                                                @php
                                                    $ext = explode('.', $file->name);
                                                    $ext = $ext[1] ?? 'FILE';
                                                    $ext = strtolower($ext);
                                                @endphp

                                                @if(in_array($ext, ['docm','dotm','odt','docx','dotx','text','txt','dot','doc']))
                                                    <i class="fa fa-file-word-o"></i>
                                                @elseif(in_array($ext, ['ppt','pptm','ppsm','potm','odp','pptx','ppsx','potx']))
                                                    <i class="fa fa-file-powerpoint-o"></i>
                                                @elseif(in_array($ext, ['xls','xltm','ods','xlsx','xltx','csv']))
                                                    <i class="fa fa-file-excel-o"></i>
                                                @elseif(in_array($ext, ['dst','dwf','dwfx','dwg','dws','dwt','dxb','dxf']))
                                                    <i class="fa fa-file-photo-o"></i>
                                                @elseif(in_array($ext, ['pdf']))
                                                    <i class="fa fa-file-pdf-o"></i>
                                                @elseif(in_array($ext, ['bmp','gif','heic','heif','pjp','jpg','pjpeg','jpeg','jfif','png','tif','ico','webp']))
                                                    <i class="fa fa-file-picture-o"></i>
                                                @elseif(in_array($ext, ['3gpp','3gp2','avi','m4v','mp4','mpg','mpeg','ogm','ogv','mov','webm','m4v','mkv','asx','wm','wmv','wvx','avi']))
                                                    <i class="fa fa-file-video-o"></i>
                                                @elseif(in_array($ext, ['flac','mid','mp3','m4a','mp3','opus','oga','ogg','wav','m4a','mid','wav']))
                                                    <i class="fa fa-file-audio-o"></i>
                                                @else
                                                    <i class="fa fa-file"></i>
                                                @endif
                                            </div>
                                        </a>
                                        <button type="button" class="center remove remove-file bg-danger d-flex align-items-center justify-content-center position-absolute" data-id="{{$file->id}}">
                                            <i class="fa fa-close" style="color: white;" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                    @empty
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                    @empty
                    <p> No document found </p>
                    @endforelse
                </div>
            </div>
        </div>
        {{-- Documents --}}

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('users.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('assets/js/intel.min.js') }}"></script>
<script>
$(document).ready(function(){

    const input = document.querySelector('#phone');
    const errorMap = ["Phone number is invalid.", "Invalid country code", "Too short", "Too long"];

    const iti = window.intlTelInput(input, {
        initialCountry: "{{ $user->country_iso_code ?? 'gb' }}",
        separateDialCode:true,
        nationalMode:false,
        utilsScript: "{{ asset('assets/js/intel2.js') }}"
    });

    $.validator.addMethod('inttel', function (value, element) {
            if (value.trim() == '' || iti.isValidNumber()) {
                return true;
            }
        return false;
    }, function (result, element) {
            return errorMap[iti.getValidationError()] || errorMap[0];
    });

    input.addEventListener('keyup', () => {
        if (iti.isValidNumber()) {
            $('#country_dial_code').val(iti.s.dialCode);
            $('#country_iso_code').val(iti.j);
        }
    });

    $(document).on('click', '.remove-file', function () {
      let self = this;
      let id = this.dataset.id;

        Swal.fire({
            title: 'Are you sure?',
            text: 'This document will be deleted!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            var url = "{{ route('remove-user-document') }}"
            if (result.value) {
                $.ajax({
                    type: "POST",
                    url: url,
                    dataType:'json',
                    data: {
                        '_method' : 'DELETE',
                        'id' : id
                    },
                    success: function(response) {
                        if (response) {
                            $(self).parent().remove();
                        } else {
                            Swal.fire('Error', 'something went wrong.', 'error');
                        }
                    }
                });
            }
        });

    });

});
</script>
@endsection
