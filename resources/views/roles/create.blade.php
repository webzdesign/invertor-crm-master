@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Add </li>
@endsection
@section('css')
<style>
    #assign-role{
        cursor: pointer;
    }

</style>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
<form action="{{ route('roles.store') }}" method="POST" id="addRole"> @csrf
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Role Name : <span class="text-danger">*</span> </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" placeholder="Enter role name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Description : </label>
                        <textarea name="description" id="description" class="form-control" placeholder="Enter role description">{{ old('description') }}</textarea>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Activation : </label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_user_activation" id="is_user_activation_0" value="1">
                            <label class="form-check-label" for="is_user_activation_0">
                                Need activation
                            </label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="is_user_activation" id="is_user_activation_1" checked value="0">
                            <label class="form-check-label" for="is_user_activation_1">
                                Don't need activation
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cardsBody py-0">
            <label class="c-gr f-500 f-16 w-100 mb-2">Permissions : </label>
            <div class="form-group">
                <div class="row">
                    @foreach($permission as $key => $value)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-3 permission-listing">
                            <div class="PlBox">
                                @foreach($value as $k => $v)
                                    @if($loop->first)
                                    <li class="list-group-item inline bg-transparent border-0 p-0 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <label class="c-gr mb-2 f-14">
                                                <input type="checkbox" class="form-check-input selectDeselect">
                                                <span class="c-primary f-700">{{ Helper::spaceBeforeCap($v->model) }}</span>
                                            </label>
                                            @if($v->model == 'User') <div id="assign-role" ><i class="fa fa-lock"></i> Assign Role</div> @endif
                                        </div>
                                    </li>
                                    @endif
                                    <li class="form-check">
                                        <input type="checkbox" class="form-check-input permission @if($v->model == 'User') user-checked-box @endif" name="permission[]" id="{{ $v->id }}" value="{{ $v->id }}" aria-label="...">
                                        <label for="{{ $v->id }}" class="form-check-label mb-0 f-14 f-500 aside-input-checbox">{{ $v->name }}</label>
                                    </li>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="modal fade" id="role-permission-modal" tabindex="-1" aria-labelledby="role-permission-moda" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered" style="max-width: 700px;">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h6 class="modal-title" id="role-permissionTitle"> Assign Role To User</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pb-3">
                        <div class="row" id="role-permission-content">
                            <div class="col-md-12">
                            @if(!empty($roleDetails))
                                @foreach($roleDetails as $dataId=>$roledata)
                                <li class="ml-2 form-check">
                                    <input type="checkbox" class="form-check-input role-premission" name="assign_role_id[]" id="assign_role_id{{ $dataId }}" value="{{ $dataId }}" aria-label="..." @if(in_array($dataId, $userassignrole)) checked @endif >
                                    <label for="assign_role_id{{ $dataId }}" class="form-check-label mb-0 f-14 f-500 aside-input-checbox">{{ $roledata }}</label>
                                </li>
                                @endforeach
                            @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('roles.index') }}">
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
            if($('.user-checked-box:checkbox:checked').length > 0 && $('.role-premission:checkbox:checked').length < 1){
                Swal.fire({
                    title: 'Please select at least one assign role.',
                    text: "",
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ok'
                }).then((result) => {
                    $('#assign-role').trigger('click');
                });

                return false;
            }

            $('button[type="submit"]').attr('disabled', true);
            if(!this.beenSubmitted) {
                this.beenSubmitted = true;
                form.submit();
            }
        }
    });
});
$(document).on('click', '#assign-role', function(e) {
    if($('.user-checked-box:checkbox:checked').length > 0) {
        $('#role-permission-modal').modal('show');
    } else {
        Swal.fire({
            title: 'Please select at least one role for user.',
            text: "",
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ok'
        });
    }

});
</script>
@endsection
