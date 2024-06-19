
<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.name', 'Module') }} </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/main.css?time=') . time() }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/intel.css') }}">
    <style>
        label.error {
            color: #ff0a22;
            font-size: 15px;
            font-family: 'Roboto Regular' !important;
        }
        .btn-primary{
            height: 40px !important;
        }
        .loginCard .loginCardBody {
            width: 600px;
        }
        .iti__selected-flag {
        height: 32px!important;
        }
        .iti--show-flags {
            width: 100%!important;
        }
    </style>
</head>
<body id="loginAnimation">
    <div class="main">
        <section class="loginCard d-flex align-items-center justify-content-center">
            <div class="loginCardBody w-100-500">
                <div class="loginCardHead d-flex align-items-center justify-content-center position-relative">
                    <a href="{{ route('login') }}">
                        <h3 class="text-white f-700 f-22 m-0"> {{ Helper::getAppTitle() }} </h3>
                    </a>
                </div>

                    <form method="POST" action="{{ $url }}" id="register">
                        @csrf

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="f-500 f-12 mb-2 d-flex align-items-center text-white"> Name </label>
                                    <input id="name" type="text" class="form-control f-400 f-14 text-dark @error('name') is-invalid @enderror" placeholder="Enter name" name="name" value="{{ old('name') }}"  autocomplete="name" autofocus>
                                    <span class="text-danger f-400 f-14">
                                        @error('name')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="f-500 f-12 mb-2 d-flex align-items-center text-white"> Email </label>
                                    <input id="email" type="email" class="form-control f-400 f-14 text-dark @error('email') is-invalid @enderror" placeholder="Enter email" name="email" value="{{ old('email') }}" >
                                    <span class="text-danger f-400 f-14">
                                        @error('email')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="f-500 f-12 mb-2 d-flex align-items-center text-white"> Phone Number </label>
                                    <input id="phone" type="phone" class="form-control f-400 f-14 text-dark @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" >
                                    <input type="hidden" name="country_dial_code" id="country_dial_code">
                                    <input type="hidden" name="country_iso_code" id="country_iso_code" value="{{ old('country_iso_code') }}">
                                    <span class="text-danger f-400 f-14">
                                        @error('phone')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="f-500 f-12 mb-2 d-flex align-items-center text-white"> City </label>
                                    <input id="city" type="text" class="form-control f-400 f-14 text-dark @error('city') is-invalid @enderror" placeholder="Enter city" name="city" value="{{ old('city') }}">
                                    <span class="text-danger f-400 f-14">
                                        @error('postal_code')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="f-500 f-12 mb-2 d-flex align-items-center text-white"> Postal Code </label>
                                    <input id="postal-code" type="text" class="form-control f-400 f-14 text-dark @error('postal_code') is-invalid @enderror" placeholder="Enter postal code" value="{{ old('postal_code') }}" name="postal_code" >
                                    <span class="text-danger f-400 f-14">
                                        @error('postal_code')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="f-500 f-12 mb-2 d-flex align-items-center text-white"> Country </label>
                                    <select name="country" id="country" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Country ---">
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
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="f-500 f-12 mb-2 d-flex align-items-center text-white"> Password </label>
                                    <input id="password" type="password" class="form-control f-400 f-14 text-dark @error('password') is-invalid @enderror" placeholder="Create a password" name="password" >
                                    <span class="text-danger f-400 f-14">
                                        @error('password')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
        
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label class="f-500 f-12 mb-2 d-flex align-items-center text-white"> Confirm Password </label>
                                    <input name="confirm_password" id="confirm-password" type="password" class="form-control f-400 f-14 text-dark @error('confirm-password') is-invalid @enderror" placeholder="Re-type password" >
                                    <span class="text-danger f-400 f-14">
                                        @error('confirm-password')
                                            {{ $message }}
                                        @enderror
                                    </span>
                                </div>
                            </div>
                        </div>


                        <button type="submit" class="btn-primary text-uppercase w-100 mt-4">Register</button>

                    </form>

                </div>
            </section>
        </div>
    
    </body>
    </html>
    
    <script src="{{ asset('assets/js/jquery3-6-0.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
    <script src="{{ asset('assets/js/jqueryAdditional.js') }}"></script>
    <script src="{{ asset('assets/js/three.r134.min.js') }}"></script>
    <script src="{{ asset('assets/js/vanta.net.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2_4_0_13.min.js') }}"></script>
    <script src="{{ asset('assets/js/intel.min.js') }}"></script>
    
<script>    
$(document).ready(function(){

    VANTA.NET({
        el: "#loginAnimation",
        mouseControls: true,
        touchControls: true,
        gyroControls: false,
        minHeight: 200.00,
        minWidth: 200.00,
        scale: 1.00,
        scaleMobile: 1.00
    })

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

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#register").validate({
        rules: {
            'name': {
                required: true
            },
            'phone': {
                inttel: true,
                required: true
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
            'password': {
                required: true,
                minlength: 8,
                maxlength: 16
            },
            'confirm_password': {
                equalTo: "#password"
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
                required: "Phone number is required.",
            },
            'email': {
                required: 'Email is required.',
                email: 'Email format is invalid.',
                remote: 'This email is already exists.'
            },
            'password': {
                required: 'Create a password.',
                minlength: 'Minimum length should be 8 characters.',
                maxlength: 'Maximum length should be 16 characters.'
            },
            'confirm_password': {
                equalTo: 'Both password field must be matched.'
            },
            'country': {
                required: "Select a country."
            },
            'city': {
                required: "Enter city."
            },
            'postal_code': {
                required: "Enter postal code.",
                maxlength: 'Maximum 8 characters allowed for postal code.'
            }
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent("div"));
        }
    });

    $('.select2').select2({
        width: '100%',
        allowClear: true,
    });

    $(document).ready(function() {
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () {
            window.history.go(1);
        };
    });

});
</script>