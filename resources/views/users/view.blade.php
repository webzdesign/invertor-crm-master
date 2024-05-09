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
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-3 permission-listing">
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

});
</script>
@endsection