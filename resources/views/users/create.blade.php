@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Add </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
<form action="{{ route('users.store') }}" method="POST" id="addUser"> @csrf
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Name: <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" placeholder="Enter name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Email: <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" placeholder="Enter email">
                        @if ($errors->has('email'))
                            <span class="text-danger d-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Roles: <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="select2 select2-hidden-accessible" data-placeholder="--- Select Role ---">
                            @forelse($roles as $key => $role)
                                @if($loop->first)
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
                        <input type="password" name="password" id="password" value="{{ old('password') }}" class="form-control" placeholder="Create password">
                        @if ($errors->has('password'))
                            <span class="text-danger d-block">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Confirm Password: <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" id="confirm-password" value="{{ old('confirm_password') }}" class="form-control" placeholder="Confirm new password">
                        @if ($errors->has('confirm_password'))
                            <span class="text-danger d-block">{{ $errors->first('confirm_password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Country: <span class="text-danger">*</span></label>
                        <select name="country" id="country" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Country ---">
                            @forelse($countries as $cid => $cname)
                                @if($loop->first)
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
                        <label class="c-gr f-500 f-16 w-100 mb-2">State: <span class="text-danger">*</span></label>
                        <select name="state" id="state" class="select2 select2-hidden-accessible" data-placeholder="--- Select a State ---">
                            <option value="" selected> --- Select State --- </option>
                        </select>
                        @if ($errors->has('state'))
                            <span class="text-danger d-block">{{ $errors->first('state') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">City: <span class="text-danger">*</span></label>
                        <select name="city" id="city" class="select2 select2-hidden-accessible" data-placeholder="--- Select a City ---">
                            <option value="" selected> --- Select City --- </option>
                        </select>
                        @if ($errors->has('city'))
                            <span class="text-danger d-block">{{ $errors->first('city') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Address Line 1: <span class="text-danger">*</span></label>
                        <textarea name="address_line_1" id="address_line_1" class="form-control"></textarea>
                        @if ($errors->has('address_line_1'))
                            <span class="text-danger d-block">{{ $errors->first('address_line_1') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Address Line 2: <span class="text-danger">*</span></label>
                        <textarea name="address_line_2" id="address_line_2" class="form-control"></textarea>
                        @if ($errors->has('address_line_2'))
                            <span class="text-danger d-block">{{ $errors->first('address_line_2') }}</span>
                        @endif
                    </div>
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
<script>
$(document).ready(function(){

    $('#country').on('change', function (event) {
        let country = event.target.value;

        if (country !== '') {
            $.ajax({
                url: "{{ route('getStates') }}",
                type: 'POST',
                data: {
                    id: country
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');                    
                },
                success: function (response) {
                    if (response.status) {
                        $('#state').empty().append(response.states);
                        $("#state").select2({
                            width: '100%',
                            allowClear: true,
                            placeholder: "--- Select a State ---"
                        });

                        $('#city').empty();
                        $("#city").select2({
                            width: '100%',
                            allowClear: true,
                            placeholder: "--- Select a City ---"
                        });
                    }
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });
        }
    });

    $('#state').on('change', function (event) {
        let state = event.target.value;
        
        if (state !== '') {
            $.ajax({
                url: "{{ route('getCities') }}",
                type: 'POST',
                data: {
                    id: state
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');                    
                },
                success: function (response) {
                    if (response.status) {
                        $('#city').empty().append(response.cities);
                        $("#city").select2({
                            width: '100%',
                            allowClear: true,
                            placeholder: "--- Select a City ---"
                        });
                    }
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });
        }
    });

    $('#addUser').validate({
        rules : {
            'name' : {
                required: true
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
                        }
                    }
                }
            },
            'role' : {
                required: true
            },
            'password' : {
                required: true,
                minlength: 8,
                maxlength: 16
            },
            'confirm_password' : {
                equalTo: "#password"
            },
            'address_line_1' : {
                required: true
            },
            'address_line_2' : {
                required: true
            },
            'country' : {
                required: true
            },
            'state' : {
                required: true
            },
            'city' : {
                required: true
            }
        },
        messages : {
            'name' : {
                required: 'Name is required.'
            },
            'email' : {
                required: 'Email is required.',
                email: 'Email format is invalid.',
                remote: 'This email is already exists.'
            },
            'role' : {
                required: 'Select a role.'
            },
            'password' : {
                required: 'Create a password.',
                minlength: 'Minimum length should be 8 characters.',
                maxlength: 'Maximum length should be 16 characters.'
            },
            'confirm_password' : {
                equalTo: 'Both Password field must be matched.'
            },
            'address_line_1' : {
                required: 'Address Line 1 is required.'
            },
            'address_line_2' : {
                required: 'Address Line 2 is required.'
            },
            'country' : {
                required: 'Select a Country.'
            },
            'state' : {
                required: 'Select a State.'
            },
            'city' : {
                required: 'Select a City.'
            }
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent("div"));
        }
    });

});
</script>
@endsection
