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
    <li class="f-14 f-400 c-36">Add </li>
@endsection

@section('content')
    {{ Config::set('app.module', $moduleName) }}
    <h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
    <form action="{{ route('users.store') }}" method="POST" id="addUser"> @csrf
        <div class="cards">
            <div class="cardsBody pb-0">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Name: <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="form-control" placeholder="Enter name">
                            @if ($errors->has('name'))
                                <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Email: <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="form-control" placeholder="Enter email">
                            @if ($errors->has('email'))
                                <span class="text-danger d-block">{{ $errors->first('email') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Phone Number: </label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control" >
                            <input type="hidden" name="country_dial_code" id="country_dial_code">
                            <input type="hidden" name="country_iso_code" id="country_iso_code" value="{{ old('country_iso_code') }}">
                            @if ($errors->has('phone'))
                                <span class="text-danger d-block">{{ $errors->first('phone') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Roles: <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="select2 select2-hidden-accessible"
                                data-placeholder="--- Select Role ---">
                                @forelse($roles as $key => $role)
                                    @if ($loop->first)
                                        <option value="" selected> --- Select Role --- </option>
                                    @endif
                                    <option value="{{ $role->id }}"> {{ $role->name }} </option>
                                @empty
                                @endforelse
                            </select>
                            @if ($errors->has('name'))
                                <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Password: <span class="text-danger">*</span></label>

                            <div class="input-group">
                                <input type="password" name="password" id="password" value="{{ old('password') }}" class="form-control pswd hover-z-3" placeholder="Create password">
                                <button class="btn btn-outline-primary show-hide d-flex align-items-center justify-content-center text-black border-gray h-34 bg-transparent shadow-none hover-bg-gray" type="button" data-fieldid="password" title="See Password"><i class="fa fa-eye"></i></button>
                                <button class="btn btn-outline-primary d-flex align-items-center justify-content-center text-black border-gray h-34 bg-transparent shadow-none hover-bg-gray" type="button" title="Generate Random" id="generate"><i class="fa fa-random"></i></button>
                            </div>

                            @if ($errors->has('password'))
                                <span class="text-danger d-block">{{ $errors->first('password') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Confirm Password: <span class="text-danger">*</span></label>

                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm-password" value="{{ old('confirm_password') }}" class="form-control pswd hover-z-3" placeholder="Confirm new password">
                                <button class="btn btn-outline-primary show-hide  d-flex align-items-center justify-content-center text-black border-gray h-34 bg-transparent shadow-none hover-bg-gray" type="button" data-fieldid="confirm-password" title="See Password"><i class="fa fa-eye"></i></button>
                            </div>

                            @if ($errors->has('confirm_password'))
                                <span class="text-danger d-block">{{ $errors->first('confirm_password') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Country: <span class="text-danger">*</span></label>
                            <select name="country" id="country" class="select2 select2-hidden-accessible"
                                data-placeholder="--- Select a Country ---">
                                @forelse($countries as $cid => $cname)
                                    @if ($loop->first)
                                        <option value="" selected> --- Select a Country --- </option>
                                    @endif
                                    <option value="{{ $cid }}"> {{ $cname }} </option>
                                @empty
                                    <option value=""> --- No Country Found --- </option>
                                @endforelse
                            </select>
                            @if ($errors->has('country'))
                                <span class="text-danger d-block">{{ $errors->first('country') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">City: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="city" id="city" placeholder="Enter city">
                            @if ($errors->has('city'))
                                <span class="text-danger d-block">{{ $errors->first('city') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Postal Code: <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code') }}"
                                class="form-control" placeholder="Enter postal code">
                            @if ($errors->has('postal_code'))
                                <span class="text-danger d-block">{{ $errors->first('postal_code') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Address Line: <span
                                    class="text-danger">*</span></label>
                            <textarea name="address_line_1" id="address_line_1" class="form-control"></textarea>
                            @if ($errors->has('address_line_1'))
                                <span class="text-danger d-block">{{ $errors->first('address_line_1') }}</span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>


            <div class="cardsBody py-0 container-for-permissions">
                <label class="c-gr f-500 f-16 w-100 mb-2">Permissions</label>
                <div class="form-group">
                    <div class="row">
                        @foreach ($permission as $key => $value)
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-3 permission-listing">
                                <div class="PlBox">
                                    @foreach ($value as $k => $v)
                                        @if ($loop->first)
                                            <li class="list-group-item inline bg-transparent border-0 p-0 mb-2">
                                                <label class="c-gr w-100 mb-2 f-14">
                                                    <input type="checkbox" class="form-check-input selectDeselect">
                                                    <span class="c-primary f-700">{{ $v->model }}</span>
                                                </label>
                                            </li>
                                        @endif
                                        <li class="form-check">
                                            <input type="checkbox" class="form-check-input permission"
                                                name="permission[]" id="{{ $v->id }}"
                                                value="{{ $v->id }}" aria-label="..." checked>
                                            <label for="{{ $v->id }}"
                                                class="form-check-label mb-0 f-14 f-500 aside-input-checbox">{{ $v->name }}</label>
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
                <button type="submit" class="btn-primary f-500 f-14">Save</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
<script src="{{ asset('assets/js/intel.min.js') }}"></script>
    <script>
        $(document).ready(function() {

            const input = document.querySelector('#phone');
            const errorMap = ["Phone number is invalid.", "Invalid country code", "Too short", "Too long"];

            const iti = window.intlTelInput(input, {
            initialCountry: "gb",
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

            $('.permission-listing').each(function() {
                var permissionCheckboxes = $(this).find('.permission');
                var selectDeselect = $(this).find('.selectDeselect');

                selectDeselect.prop('checked', permissionCheckboxes.length === permissionCheckboxes.filter(
                    ':checked').length);
            });

            $(document).on('click', '.selectDeselect', function(e) {
                var selectVal = $(this).prop('checked');

                if (selectVal) {
                    $(this).closest('.permission-listing').find('.permission').prop('checked', true);
                } else {
                    $(this).closest('.permission-listing').find('.permission').prop('checked', false);
                }
            });

            $('#addUser').validate({
                rules: {
                    'name': {
                        required: true
                    },
                    'phone': {
                        inttel: true
                    },
                    'email': {
                        required: true,
                        email: true,
                        remote: {
                            url: "{{ url('checkUserEmail') }}",
                            type: "POST",
                            async: false,
                            data: {
                                email: function() {
                                    return $("#email").val();
                                }
                            }
                        }
                    },
                    'role': {
                        required: true
                    },
                    'password': {
                        required: true,
                        minlength: 8,
                        maxlength: 16
                    },
                    'confirm_password': {
                        equalTo: "#password"
                    },
                    'address_line_1': {
                        required: true
                    },
                    'country': {
                        required: true
                    },
                    'city': {
                        required: true
                    },
                    'postal_code': {
                        required: true,
                        maxlength: 8
                    }
                },
                messages: {
                    'name': {
                        required: 'Name is required.'
                    },
                    'phone': {
                        inttel: 'Phone number is invalid.'
                    },
                    'email': {
                        required: 'Email is required.',
                        email: 'Email format is invalid.',
                        remote: 'This email is already exists.'
                    },
                    'role': {
                        required: 'Select a role.'
                    },
                    'password': {
                        required: 'Create a password.',
                        minlength: 'Minimum length should be 8 characters.',
                        maxlength: 'Maximum length should be 16 characters.'
                    },
                    'confirm_password': {
                        equalTo: 'Both password field must be matched.'
                    },
                    'address_line_1': {
                        required: 'Address Line 1 is required.'
                    },
                    'country': {
                        required: 'Select a Country.'
                    },
                    'city': {
                        required: 'Select a City.'
                    },
                    'postal_code': {
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

            $('#generate').on('click', function (event) {
                var length = 16,
                charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$^&*(){}[]+-~`",
                password = "";
                for (var i = 0, n = charset.length; i < length; ++i) {
                    password += charset.charAt(Math.floor(Math.random() * n));
                }

                $('#password').val(password);
                $('#confirm-password').val(password);
            });

            $('.show-hide').on('click', function (event) {
                let input = $(this).data('fieldid');
                input = $(`#${input}`);

                if (input.attr('type') == 'password') {
                    input.attr('type', 'text');
                    $(this).html("<i class='fa fa-eye-slash'></i>");
                } else {
                    input.attr('type', 'password');
                    $(this).html("<i class='fa fa-eye'></i>");
                }
            });

        });
    </script>
@endsection
