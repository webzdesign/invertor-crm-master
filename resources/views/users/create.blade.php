@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Add {{ $moduleName }} </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
<form action="{{ route('roles.store') }}" method="POST" id="addRole"> @csrf
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Name: <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" placeholder="Enter name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Roles: <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="select2 form-control">
                            @forelse($roles as $key => $role)
                                <option value="{{ $role->id }}"> {{ $role->name }} </option>
                            @empty
                            @endforelse
                        </select>
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Email: <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" placeholder="Enter email">
                        @if ($errors->has('email'))
                            <span class="text-danger d-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Phone Number: <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="form-control" placeholder="Enter phone number">
                        @if ($errors->has('phone'))
                            <span class="text-danger d-block">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>
                </div>


            </div>
        </div>
        <div class="cardsBody py-0">
            <label class="c-gr f-500 f-16 w-100 mb-2">Permissions</label>
            <div class="form-group">
                <div class="row">


                </div>
            </div>
        </div>
        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('roles.index') }}">
                <button type="button" class="btn-default f-500 f-14">cancel</button>
            </a>
            <button type="submit" class="btn-primary f-500 f-14">Save changes</button>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
$(document).ready(function(){

    $('body').on('click','.selectDeselect',function(e){
        var selectVal = $(this).prop('checked');

        if (selectVal) {
            $(this).closest('.permission-listing').find('.permission').prop('checked', true);
        } else {
            $(this).closest('.permission-listing').find('.permission').prop('checked', false);
        }
    });

    $("#addRole").validate({
        rules: {
            name: {
                required: true,
                remote: {
                    url: "{{ url('roles/checkRoleExist') }}",
                    type: "POST",
                    async: false,
                    data: {
                        name: function() {
                            return $( "#name" ).val();
                        },
                    }
                },
            }
        },
        messages: {
            name: {
                required: "Role name is required.",
                remote: "Role name already exist.",
            }
        },
        errorPlacement: function(error, element) {
            if (element.attr("name") == "permission[]") {
                error.insertAfter(".permission-listing");
            } else {
                error.insertAfter(element);
            }
        },
        submitHandler:function(form) {
            $('button[type="submit"]').attr('disabled', true);
            if(!this.beenSubmitted) {
                this.beenSubmitted = true;
                form.submit();
            }
        }
    });
});
</script>
@endsection
