@extends('layouts.master')

@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2">
    <h2 class="f-24 f-700 c-36 mb-0">Manage {{ $moduleName }}</h2>
    @permission("users.create")
    <a href="{{ route('users.create') }}" class="btn-primary f-500 f-14">
        <svg class="me-1" width="16" height="16" viewBox="0 0 16 16" fill="#ffffff" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.00008 13.3332V7.99984M8.00008 7.99984V2.6665M8.00008 7.99984H13.3334M8.00008 7.99984H2.66675" stroke="#ffffff" stroke-width="2" stroke-linecap="round"></path>
            <defs>
                <linearGradient id="paint0_linear_1524_12120" x1="8.00008" y1="2.6665" x2="8.00008" y2="13.3332" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#ffffff"></stop>
                    <stop offset="1" stop-color="#ffffff"></stop>
                </linearGradient>
            </defs>
        </svg>
        Create New {{ $moduleName }}
    </a>
    @endpermission
</div>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="cards">
    <div class="row m-0 filterColumn">
        <div class="col-xl-3 col-md-4 col-sm-6 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Role</label>
                <select name="filterRole" id="filterRole" class="select2 select2-hidden-accessible" data-placeholder="--- Select Role ---">
                    @forelse($roles as $role)
                    @if($loop->first)
                    <option value="" selected> --- Select Role --- </option>
                    @endif
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @empty
                    <option value="" selected> --- No Roles Available --- </option>
                    @endforelse
                </select>
            </div>
        </div>
        <div class="col-xl-2 col-sm-4 position-relative">
            <div class="form-group mb-0 mb-10-500">
                <label class="c-gr f-500 f-14 w-100 mb-1">Select Status</label>
                <select name="filterStatus" id="filterStatus" class="select2 select2-hidden-accessible" data-placeholder="--- Select Status ---">
                    <option value="" selected> --- Select Status --- </option>
                    <option value="1">Active</option>
                    <option value="0">in-Active</option>
                </select>
            </div>
        </div>
        <div class="col-xl-3 col-sm-4 position-relative">
            <div class="form-group mb-0">
                <label class="c-gr f-500 f-14 w-100 mb-1 d-none-500">&nbsp;</label>
                <button class="btn-default f-500 f-14 clearData" style="display:none;"><i class="fa fa-remove" aria-hidden="true"></i> Clear filters</button>
            </div>
        </div>
    </div>

    <table class="datatable-users table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th>Sr No.</th>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Added By</th>
                <th>Updated By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
{{-- Add User Modal --}}
<div class="modal fade fieldsModal groupModal" id="userCreateModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add New {{ $moduleName }}</h4>
            </div>
            <form action="{{ route('users.store') }}" method="POST" id="userForm"> @csrf
                <div class="modal-body">
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Name: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <input type="text" name="name" class="form-control" placeholder="Enter user name">
                            <span class="text-danger d-block name"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Email Address: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <input type="text" name="email" class="form-control" placeholder="Enter email">
                            <span class="text-danger d-block email"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Phone Number: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <input type="text" name="phone" id="phone" maxlength="10" class="form-control" placeholder="Enter phone number">
                            <span class="text-danger d-block phone"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Select User Role: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <select name="role" id="insert-role" class="select2Model select2-hidden-accessible" data-placeholder="--- Select User Role ---">
                                @forelse($roles as $role)
                                @if($loop->first)
                                <option value="" selected> --- Select a Role --- </option>
                                @endif
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @empty
                                <option value="" selected> --- No Roles Available --- </option>
                                @endforelse
                            </select>
                            <span class="text-danger d-block role"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Password: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <input type="password" name="password" id="create-password" class="form-control" placeholder="Enter password">
                            <span class="text-danger d-block password"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Confirm Password: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Enter password again">
                            <span class="text-danger d-block confirm-password"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-default f-500 f-14" data-bs-dismiss="modal" type="button">Cancel</button>
                    <button type="submit" class="btn-primary" id="sbtmtBtn">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Add User Modal --}}

{{-- Edit User Modal --}}
<div class="modal fade fieldsModal groupModal" id="userUpdateModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Update {{ $moduleName }}</h4>
            </div>
            <form method="POST" id="userUpdateForm"> @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="u-id">
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Name: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <input type="text" name="name" id="u-name" class="form-control" placeholder="Enter user name">
                            <span class="text-danger d-block name"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Email Address: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <input type="text" name="email" id="u-email" class="form-control" placeholder="Enter email">
                            <span class="text-danger d-block email"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Phone Number: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <input type="text" name="phone" id="u-phone" maxlength="10" class="form-control" placeholder="Enter phone number">
                            <span class="text-danger d-block phone"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Select User Role: <span class="text-danger">*</span></label>
                        <div class="col-md-12">
                            <select name="role" id="u-role" class="select2Model select2-hidden-accessible" data-placeholder="--- Select User Role ---">
                                <option value="" selected> --- Select User Role --- </option>
                            </select>
                            <span class="text-danger d-block role"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Password:</label>
                        <div class="col-md-12">
                            <input type="password" name="password" id="u-password" class="form-control" placeholder="Enter password">
                            <span class="text-danger d-block password"></span>
                        </div>
                    </div>
                    <div class="row align-items-center mb-3">
                        <label class="c-gr f-16 f-500">Confirm Password:</label>
                        <div class="col-md-12">
                            <input type="password" name="password_confirmation" id="u-c-password" class="form-control" placeholder="Enter password again">
                            <span class="text-danger d-block confirm-password"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn-default f-500 f-14" data-bs-dismiss="modal" type="button">Cancel</button>
                    <button type="submit" class="btn-primary" id="sbtmtUpdtBtn">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Edit User Modal --}}

{{-- View User Modal --}}
<div class="modal fade fieldsModal groupModal" id="userViewModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">View {{ $moduleName }}</h4>
            </div>
            <div class="modal-body">
                <div class="row align-items-center mb-3">
                    <label class="c-gr f-16 f-500">Name:</label>
                    <div class="col-md-12">
                        <input type="text" id="v-name" class="form-control" placeholder="Enter user name" disabled>
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <label class="c-gr f-16 f-500">Email Address:</label>
                    <div class="col-md-12">
                        <input type="text" id="v-email" class="form-control" placeholder="Enter email" disabled>
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <label class="c-gr f-16 f-500">Phone Number:</label>
                    <div class="col-md-12">
                        <input type="text" id="v-phone" maxlength="10" class="form-control" placeholder="Enter phone number" disabled>
                    </div>
                </div>
                <div class="row align-items-center mb-3">
                    <label class="c-gr f-16 f-500">User Role:</label>
                    <div class="col-md-12">
                        <select id="v-role" class="select2Model select2-hidden-accessible" disabled data-placeholder=" --- Select a Role --- ">
                            <option value="" selected> --- Select a Role --- </option>
                        </select>
                        <span class="text-danger d-block role"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-default f-500 f-14" data-bs-dismiss="modal" type="button">Close</button>
            </div>
        </div>
    </div>
</div>
{{-- View User Modal --}}
@endsection

@section('script')

<script>
    $(document).ready(function() {
        var userCreateModal = $("#userCreateModal");
        var userUpdateModal = $('#userUpdateModal');
        var ServerDataTable = $('.datatable-users').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",

            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            ajax: {
                "url": "{{ route('users.getallusers') }}",
                "dataType": "json",
                "type": "POST",
                "data" : {
                    filterRole:function() {
                        return $("#filterRole").val();
                    },
                    filterStatus:function() {
                        return $("#filterStatus").val();
                    },
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'name',
                },
                {
                    data: 'role.name',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'email',
                },
                {
                    data: 'phone',
                },
                {
                    data: 'status',
                },
                {
                    data: 'addedby.name',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'updatedby.name',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                }
            ],
        });

        $('#filterInput').html($('#searchPannel').html());
        $('#filterInput > input').keyup(function() {
            ServerDataTable.search($(this).val()).draw();
        });

        $('.datepicker').datetimepicker({
            format: "DD/MM/YYYY", 
            timeZone: ''
        });

        /** Add User Data Starts */

        var userForm = $("#userForm", userCreateModal);

        userCreateModal.on('show.bs.modal', function(event) {
            $(userForm).validate().resetForm();
            $(userForm)[0].reset();
            $("#insert-role", userForm).val(null).trigger("change");
            $('#sbtmtBtn', userForm).attr('disabled', false);
            $('#sbtmtBtn', userForm).html('Add');
        });

        userForm.validate({
            rules: {
                name: {
                    required: true
                },
                email: {
                    required: true,
                    email: true,
                },
                phone: {
                    required: true,
                    number: true,
                    minlength: 10,
                    maxlength: 10,
                    min:1000000000,
                    remote: {
                        url: "{{ url('checkUserPhoneNumber') }}",
                        type: "POST",
                        data: {
                            phone: function() {
                                return $("#phone", userForm).val();
                            },
                        }
                    }
                },
                role: {
                    required: true,
                },
                password: {
                    required: true,
                    minlength: 8,
                    maxlength: 16
                },
                password_confirmation: {
                    required: true,
                    minlength: 8,
                    maxlength: 16,
                    equalTo: "#create-password"
                },
            },
            messages: {
                name: {
                    required: "Name is required.",
                },
                email: {
                    required: "Email is required.",
                    email: "Enter a vaild email address.",
                },
                phone: {
                    required: "Phone number is required.",
                    number: "Enter valid phone number.",
                    minlength: "Phone number must consist of at least 10 number.",
                    maxlength: "Phone number must consist of at least 10 number.",
                    remote: "Phone number already exist.",
                    min: "Please enter a valid phone number."
                },
                role: {
                    required: "Role is required.",
                },
                password: {
                    required: "Password is required.",
                    minlength: "Password must consist of at least 8 characters",
                    maxlength: "Password must consist of at most 16 characters"
                },
                password_confirmation: {
                    required: "Confirm password is required.",
                    minlength: "Confirm password must consist of at most 8 characters",
                    maxlength: "Password must consist of at most 16 characters",
                    equalTo: "Both password must be matched",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent("div"));
            }
        });

        $("button[type='submit']", userForm).on('click', function(e) {
            e.preventDefault();

            userForm.valid();
            if (!e.target.dataset.isSubmitted && userForm.valid() && userForm.validate().pendingRequest == 0) {
                e.target.dataset.isSubmitted = true;
                e.target.disable = true;

                $.ajax({
                    url: "{{ route('users.store') }}",
                    method: "POST",
                    data: $(userForm).serialize(),
                    dataType: "json",
                    async: false,
                    success: function(data) {
                        if (data.status == 200) {
                            ServerDataTable.ajax.reload();
                            fireSuccessMessage(data.success);
                        }
                        userCreateModal.modal('hide');
                        $(userForm)[0].reset();
                        $(userForm).validate().resetForm();
                        delete e.target.dataset.isSubmitted;
                    },
                    error: function(res) {
                        if (res.status == 422) {
                            delete e.target.dataset.isSubmitted;
                            const data = JSON.parse(res.responseText);

                            if (data.errors.name) {
                                $('#userForm').find('.name').text(data.errors.name[0]);
                            } else {
                                $('#userForm').find('.name').text('');
                            }

                            if (data.errors.email) {
                                $('#userForm').find('.email').text(data.errors.email[0]);
                            } else {
                                $('#userForm').find('.email').text('');
                            }

                            if (data.errors.phone) {
                                $('#userForm').find('.phone').text(data.errors.phone[0]);
                            } else {
                                $('#userForm').find('.phone').text('');
                            }

                            if (data.errors.role) {
                                $('#userForm').find('.role').text(data.errors.role[0]);
                            } else {
                                $('#userForm').find('.role').text('');
                            }

                            if (data.errors.password) {
                                $('#userForm').find('.password').text(data.errors.password[0]);
                            } else {
                                $('#userForm').find('.password').text('');
                            }

                            if (data.errors.password_confirmation) {
                                $('#userForm').find('.confirm-password').text(data.errors.password_confirmation[0]);
                            } else {
                                $('#userForm').find('.confirm-password').text('');
                            }
                        } else {
                            $(userForm)[0].reset();
                            $(userForm).validate().resetForm();
                            delete e.target.dataset.isSubmitted;
                            fireErrorMessage(res.error);
                        }
                    }
                });
            }

            return false;
        });

        /** Add User Data Ends */

        /** Update User Data Starts */

        var userUpdateForm = $("#userUpdateForm", userUpdateModal);

        userUpdateForm.validate({
            rules: {
                name: {
                    required: true,
                },
                email: {
                    required: true,
                    email: true,
                },
                phone: {
                    required: true,
                    number: true,
                    minlength: 10,
                    maxlength: 10,
                    min:1000000000,
                    remote: {
                        url: "{{ url('checkUserPhoneNumber') }}",
                        type: "POST",
                        async: false,
                        data: {
                            phone: function() {
                                return $("#u-phone", userUpdateForm).val();
                            },
                            uid: function() {
                                return $("#u-id", userUpdateForm).val();
                            }
                        }
                    }
                },
                role: {
                    required: true,
                },
                password: {
                    minlength: 8,
                    maxlength: 16
                },
                password_confirmation: {
                    minlength: 8,
                    maxlength: 16,
                    equalTo: "#u-password"
                },
            },
            messages: {
                name: {
                    required: "Name is required.",
                },
                email: {
                    required: "Email is required.",
                    email: "Enter a vaild email address.",
                },
                phone: {
                    required: "Phone number is required.",
                    number: "Enter valid phone number.",
                    minlength: "Phone number must consist of at least 10 number.",
                    maxlength: "Phone number must consist of at least 10 number.",
                    remote: "Phone number already exist.",
                    min: "Please enter a valid phone number."
                },
                role: {
                    required: "Role is required.",
                },
                password: {
                    minlength: "Password must consist of at least 8 characters",
                    maxlength: "Password must consist of at most 16 characters"
                },
                password_confirmation: {
                    minlength: "Confirm password must consist of at most 8 characters",
                    maxlength: "Password must consist of at most 16 characters",
                    equalTo: "Both password must be matched",
                },
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent("div"));
            }
        });

        $("button[type='submit']", userUpdateForm).on('click', function(e) {
            e.preventDefault();
            userUpdateForm.valid();
            if (!e.target.dataset.isSubmitted && userUpdateForm.valid() && userUpdateForm.validate().pendingRequest == 0) {
                e.target.dataset.isSubmitted = true;
                e.target.disable = true;
                var id = $("#u-id", userUpdateForm).val();
                $.ajax({
                    url: "{{ url('users/" + id + "/update/') }}",
                    method: "PUT",
                    data: $(userUpdateForm).serialize(),
                    dataType: "json",
                    success: function(data) {
                        if (data.status == 200) {
                            ServerDataTable.ajax.reload();
                            fireSuccessMessage(data.success);
                        }
                        userUpdateModal.modal('hide');
                        $(userUpdateForm)[0].reset();
                        $(userUpdateForm).validate().resetForm();
                        delete e.target.dataset.isSubmitted;
                    },
                    error: function(res) {
                        if (res.status == 422) {
                            delete e.target.dataset.isSubmitted;
                            const data = JSON.parse(res.responseText);

                            if (data.errors.name) {
                                $('#userUpdateForm').find('.name').text(data.errors.name[0]);
                            } else {
                                $('#userUpdateForm').find('.name').text('');
                            }

                            if (data.errors.email) {
                                $('#userUpdateForm').find('.email').text(data.errors.email[0]);
                            } else {
                                $('#userUpdateForm').find('.email').text('');
                            }

                            if (data.errors.phone) {
                                $('#userUpdateForm').find('.phone').text(data.errors.phone[0]);
                            } else {
                                $('#userUpdateForm').find('.phone').text('');
                            }

                            if (data.errors.role) {
                                $('#userUpdateForm').find('.role').text(data.errors.role[0]);
                            } else {
                                $('#userUpdateForm').find('.role').text('');
                            }

                            if (data.errors.password) {
                                $('#userUpdateForm').find('.password').text(data.errors.password[0]);
                            } else {
                                $('#userUpdateForm').find('.password').text('');
                            }

                            if (data.errors.password_confirmation) {
                                $('#userUpdateForm').find('.confirm-password').text(data.errors.password_confirmation[0]);
                            } else {
                                $('#userUpdateForm').find('.confirm-password').text('');
                            }

                        } else {
                            $(userUpdateForm)[0].reset();
                            $(userUpdateForm).validate().resetForm();
                            delete e.target.dataset.isSubmitted;
                        }
                    },
                    complete: function(jqXHR, textStatus) {
                        $('#sbtmtUpdtBtn', userUpdateForm).attr('disabled', false);
                        $('#sbtmtUpdtBtn', userUpdateForm).html('Update');
                    }
                });
            }
        });

        $(document).on('click', '.modal-edit-btn', function(event) {
            event.preventDefault();
            var id = event.currentTarget.dataset.uniqueid;
            var hrefLink = event.currentTarget.href;

            userUpdateModal.off('show.bs.modal').on('show.bs.modal', function(ev) {

                $(userUpdateForm)[0].reset();
                $(userUpdateForm).validate().resetForm();
                userUpdateForm.find(".error").removeClass("error");
                $("#u-role", userUpdateForm).val(null).trigger("change");
                $('#sbtmtUpdtBtn', userUpdateForm).attr('disabled', false);

                $.ajax({
                    url: hrefLink,
                    method: "GET",
                    async: false,
                    data: {
                        id: id
                    },
                    dataType: "json",
                    success: function(data) {
                        var allRoles = data.roles;
                        var data = data.user[0];
                        var role = data.roles[0];
                        $('#u-id', userUpdateForm).val(id);
                        $('#u-name', userUpdateForm).val(data.name);
                        $('#u-email', userUpdateForm).val(data.email);
                        $('#u-phone', userUpdateForm).val(data.phone);
                        $('#u-role', userUpdateForm).empty();
                        $("#u-role", userUpdateForm).empty().append("<option value=''> --- Select a Role --- </option>"); 
                        allRoles.forEach(element => {
                            if (element.id == role.id) {
                                $('#u-role', userUpdateForm).append('<option value="' + element.id + '" selected>' + element.name + '</option>');
                            } else {
                                $('#u-role', userUpdateForm).append('<option value="' + element.id + '">' + element.name + '</option>');
                            }
                        });
                    }
                });
            });

            userUpdateModal.modal('show');
        })
        /** Update User Data Ends */

        /** View User Data Starts */
        $(document).on('click', '.modal-view-btn', function(event) {
            event.preventDefault();
            $('#userViewModal').modal('show');

            var id = $(this).attr('data-uniqueid');
            var hrefLink = $(this).attr('href');

            $.ajax({
                url: hrefLink,
                method: "GET",
                data: {
                    id: id
                },
                dataType: "json",
                success: function(data) {
                    var data = data.user[0];
                    var role = data.roles[0];
                    $('#v-name').val(data.name);
                    $('#v-email').val(data.email);
                    $('#v-phone').val(data.phone);
                    $("#v-role").empty().append("<option value='" + role.id + "' selected> " + role.name + " </option>");
                }
            })
        });
        /** View User Data Ends */

        /* filter Datatable */
        $('body').on('change', '#filterRole, #filterStatus', function(e){
            var filterRole = $('body').find('#filterRole').val();
            var filterStatus = $('body').find('#filterStatus').val();
            
            if (filterRole != '' || filterStatus != '') {
                $('body').find('.clearData').show();
            } else {
                $('body').find('.clearData').hide();
            }

            ServerDataTable.ajax.reload();
        });

        $('body').on('click', '.clearData', function(e){
            $('body').find('#filterRole').val('').trigger('change');
            $('body').find('#filterStatus').val('').trigger('change');
            ServerDataTable.ajax.reload();
        });
    });
</script>
@endsection