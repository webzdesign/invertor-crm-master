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
<li class="f-14 f-400 c-36">Edit </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Edit {{ $moduleName }}</h2>
<form action="{{ route('suppliers.update', $id) }}" method="POST" id="addUser"> @csrf @method('PUT')
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Name : <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control" placeholder="Enter name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Email : <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control" placeholder="Enter email">
                        @if ($errors->has('email'))
                            <span class="text-danger d-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Phone Number : </label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="form-control" >
                        <input type="hidden" name="country_dial_code" id="country_dial_code" value="{{ old('country_dial_code', $user->country_dial_code) }}">
                        <input type="hidden" name="country_iso_code" id="country_iso_code" value="{{ old('country_iso_code', $user->country_iso_code) }}">
                        @if ($errors->has('phone'))
                            <span class="text-danger d-block">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Country : <span class="text-danger">*</span></label>
                        <select name="country" id="country" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Country ---">
                            @forelse($countries as $cid => $cname)
                                @if($loop->first)
                                <option value="" selected> --- Select a Country --- </option>
                                @endif
                                <option value="{{ $cid }}" @if($cid == $user->country_id) selected @endif > {{ $cname }} </option>
                            @empty
                                <option value=""> --- No Country Found --- </option>
                            @endforelse
                        </select>
                        @if ($errors->has('country'))
                            <span class="text-danger d-block">{{ $errors->first('country') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Postal Code : <span class="text-danger">*</span></label>
                        <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', $user->postal_code) }}" class="form-control" placeholder="Enter postal code">
                        @if ($errors->has('postal_code'))
                            <span class="text-danger d-block">{{ $errors->first('postal_code') }}</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('suppliers.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
            <input type="hidden" name="role_id" id="role_id" value="4">
            <button type="submit" class="btn-primary f-500 f-14">Save Changes</button>
        </div>
    </div>
</form>
@endsection

@section('script')
<script src="{{ asset('assets/js/intel.min.js') }}"></script>
<script>
$(document).ready(function(){

    const input = document.querySelector('#phone');
    const errorMap = ["Phone number is invalid.", "Invalid country code", "Too short", "Too long"];

    const iti = window.intlTelInput(input, {
        initialCountry: "{{ $user->country_iso_code ?? 'gb' }}",
        preferredCountries: ['gb', 'pk'],
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

    $('#addUser').validate({
        rules : {
            'name' : {
                required: true
            },
            'phone': {
                inttel: true
            },
            'email' : {
                required: true,
                email: true,
                remote: {
                    url: "{{ url('checkUserEmail') }}",
                    type: "POST",
                    async: false,
                    data: {
                        email: function() {
                            return $("#email").val();
                        },
                        role_id: function() {
                            return $('#role_id').val();
                        },
                        id : "{{ $id }}"
                    }
                }
            },
            'country' : {
                required: true
            },
            'postal_code' : {
                required: true,
                maxlength: 8
            }
        },
        messages : {
            'name' : {
                required: 'Name is required.'
            },
            'phone': {
                inttel: 'Phone number is invalid.'
            },
            'email' : {
                required: 'Email is required.',
                email: 'Email format is invalid.',
                remote: 'This email is already exists.'
            },
            'country' : {
                required: 'Select a Country.'
            },
            'postal_code' : {
                required: 'Enter postal code.',
                maxlength: 'Maximum 8 characters allowed for postal code.'
            }
        },
        errorPlacement: function(error, element) {
            if ($(element).hasClass('pswd')) {
                error.insertAfter(element.parent("div"));
            } else {
                error.appendTo(element.parent("div"));
            }
        }
    });

});
</script>
@endsection
