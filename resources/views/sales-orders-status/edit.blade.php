@extends('layouts.master')

@section('css')
<style>
    .card-header{
        background: #f5f5f5;
        color: #000;
        padding: 6px 15px;
        text-align: center;
        display: grid;
        height: 40px;
    }

    .card-title {
        float: left;
        font-size: 1.1rem;
        font-weight: 400;
        margin: 0;
        font-size: 16px;
    }
    .card.card-row {
        width: 300px;
        margin: 0 !important;
        min-width: 270px;
        height: calc(100vh - 180px);
        border-radius: 0;
        border-right: 0;
    }
    .card.card-row:last-child{
        border-right: 1px solid rgba(0,0,0,.125);
    }
    .ui-sortable-helper{
        border: none;
    }
    .ui-sortable-helper .sticky-add-icon{
        display: none;
    }
    .fa-arrows {
        cursor: move;
    }
    .title-of-card {
        background: transparent;
        color: black;
        border: none;
        text-align: center;
        text-transform: uppercase;
    }
    .title-of-card:focus {
        outline: none;
    }
    .sticky-add-icon {
        height: 25px;
        width: 25px;
        border: 1px solid grey;
        border-radius: 50%;
        position: absolute;
        right: -12px;
        top: 25px;
        cursor: pointer;
        z-index: 1;
        background: white;
    }
    .fa-trash {
        color: #dd2d20;
        cursor: pointer;
    }

    input[type="color"] {
        -webkit-appearance: none;
        padding: 0;
        border: none;
        border-radius: 10px;
        width: 14px;
        min-width: 14px;
        height: 14px;
        position: relative;
    }
    input[type="color"]::-webkit-color-swatch {
        border: none;
        border-radius: 10px;
        padding: 0;
    }
    input[type="color"]::-webkit-color-swatch-wrapper {
        border: none;
        border-radius: 10px;
        padding: 0;
    }

    .card-options {
        display: none!important;
    }

    .df-fr-jse {
        display: flex;
        flex-direction: row;
        justify-content: space-evenly;
    }

</style>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="content pb-3">

{{-- Board --}}
<form action="{{ route('sales-order-status-update') }}" method="POST" id="cardForm" > @csrf

    <div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap" style="display: flex!important;justify-content: flex-end!important;">
        <button type="submit" class="btn-primary f-500 f-14" style="margin-right:10px;"> SAVE </button>
        <a href="{{ route('sales-order-status') }}" class="btn-default f-500 f-14"> BACK </a>
    </div>

<div class="d-flex overflow-auto pb-3" id="sortable">

    @php $iteration = 0;  @endphp
    @forelse($statuses as $key => $status)
    <div class="card card-row card-secondary parent-card @if($status->id == '1') disable-sorting @endif ">
        @php $tempColor = !empty($status->color) ? $status->color : (isset($colours[$key]) ? $colours[$key] : (isset($colours[$iteration]) ? $colours[$iteration] : ($iteration = 0 and $colours[0] ? $colours[$iteration] : '#99ccff' )));  @endphp
        <input type="hidden" name="sequence[]" value="{{ $status->id }}" @if($status->id == '1') disabled @endif>
        <div class="card-header px-2" style="border-bottom: 4px solid {{ $tempColor }};">
            @if(count($statuses) == 1 || !$loop->last)
            @permission("sales-order-status.create")
            <span class="sticky-add-icon" data-color="{{ $tempColor }}">
                <i class="fa fa-plus" style="color: #bfbfbf;"></i>
            </span>
            @endpermission
            @endif

            <div class="card-title d-flex align-items-center justify-content-between">

                @if($status->id != '1')
                <div style="line-height: 0;cursor: move" class="movable">
                    <svg fill="#656565" width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="M8.5,10a2,2,0,1,0,2,2A2,2,0,0,0,8.5,10Zm0,7a2,2,0,1,0,2,2A2,2,0,0,0,8.5,17Zm7-10a2,2,0,1,0-2-2A2,2,0,0,0,15.5,7Zm-7-4a2,2,0,1,0,2,2A2,2,0,0,0,8.5,3Zm7,14a2,2,0,1,0,2,2A2,2,0,0,0,15.5,17Zm0-7a2,2,0,1,0,2,2A2,2,0,0,0,15.5,10Z"/></svg>
                </div>
                @endif

                <input type="text" name="name[]" class="title-of-card f-14 m-auto" value="{{ $status->name }}" @if($status->id == '1') disabled @endif >

                <div class="d-flex align-items-center card-options">
                    <span class="me-2">
                        @if($status->id != '1')
                        @permission("sales-order-status.delete")
                        <i class="fa fa fa-trash"></i>
                        @endpermission
                        @endif
                    </span>

                    @if($status->id != '1')
                        <input type="color" name="color[]" class="color-picker" value="{{ $tempColor }}" />
                    @endif
                </div>

            </div>
        </div>
        <div class="card-body">
            <div class="card">
                <div class="card-body text-center cursor-pointer opener" data-title="{{ $status->name }}" 
                    data-sid="{{ $status->id }}"
                    >
                    <i class="fa fa-cog"></i>
                    Manage
                </div>
            </div>
        </div>
    </div>
    @empty
    @endforelse
    
</div>
</form>
{{-- Board --}}

</div>



<div class="modal fade" id="manager" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('sales-order-manage-role') }}" method="POST" id="manage-role-form"> @csrf
            <div class="modal-header no-border modal-padding">
                <h1 class="modal-title fs-5"> Manage "<span id="modal-title"></span>" status </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="manage-status-id" name="id" />
                <div class="row">
                    
                    <div class="col-12">
                        <div class="form-group row">
                            <label for="role-for-status" class="c-gr f-500 f-16 w-100 whiteSpace mt-2">Managed By : <span class="text-danger">*</span></label>
                            <select name="role" id="role-for-status" class="select2-hidden-accessible" data-placeholder="--- Select Role ---">
                                @forelse($roles as $id => $role)
                                @if ($loop->first)
                                    <option value="" selected> --- Select a Role --- </option>
                                @endif
                                    <option value="{{ $id }}"> {{ $role }} </option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group row">
                            <label class="c-gr f-500 f-16 w-100 whiteSpace">Change Responsible : <span class="text-danger">*</span></label>
                            <select name="responsible" id="responsible" class="select2-hidden-accessible" data-placeholder="--- Select Responsible ---">
                                @forelse($roles as $id => $role)
                                @if ($loop->first)
                                    <option value="" selected> --- Select Responsible Role --- </option>
                                @endif
                                    <option value="{{ $id }}"> {{ $role }} </option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Possible status : </label>
                            <button type="button" id="status-adder-into-modal" class="btn-primary f-500 f-14"> <i class="fa fa-plus"></i> Add </button>
                            <div id="multiple-row-container">
                                <table class="table table-bordered">
                                    <tbody class="upsertable">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="cas" class="c-gr f-500 f-16">Create status for admin : </label>
                            <input type="checkbox" class="form-check-input" name="create_admin_status" id="cas" value="1">
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="task" class="c-gr f-500 f-16">Create Task : </label>
                            <input type="checkbox" class="form-check-input" name="task" id="task" value="1">
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer no-border">
                <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                <button type="submit" class="btn-primary f-500 f-14"> Save </button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    let deletePermission = '';
    let addPermission = false;
    let isChanging = false;

    @if(auth()->user()->hasPermission('sales-order-status.delete'))
    deletePermission = '<span class="me-2"> <i class="fa fa fa-trash"></i></span>';
    @endif

    @if(auth()->user()->hasPermission('sales-order-status.create'))
    addPermission = true;
    @endif

    let lastElementIndex = 0;

    var content = `<tr><td class="block-a"><div style="min-width: 200px;width: 100%" class="removable-status"><select name="mstatus[0]" data-indexid="0" id="m-status-0" class="select2 select2-hidden-accessible m-status" style="width:100%" data-placeholder="Select a Status"><option value="" selected> --- Select a Status --- </option></select></div></td><td style="width:100px;"><div class="df-fr-jse" style="min-width: 100px;"><button type="button" class="btn btn-primary btn-sm addNewRow">+</button> <button type="button" class="btn btn-danger btn-sm removeRow" tabindex="-1">-</button></div></td></tr>`;
    var statusesHtml = `<option value="" selected> --- Select a Status --- </option>`;
    var allStatuses = {!! json_encode($s) !!};

    $(document).ready(function() {

        //

        $('#multiple-row-container').hide();

        for (key in allStatuses) {
            statusesHtml += `<option value="${key}"> ${allStatuses[key]} </option>`;
        }

        function toggleAddButton(bool) {

            if (Object.keys(allStatuses).length == $('.upsertable tr').length + bool) {
                $('.addNewRow').hide();
            } else {
                $('.addNewRow').show();
            }
        }

        function resetModal() {
            $('.upsertable tr').each(function (index, element) {
                $(element).remove();
            });

            $('#status-adder-into-modal').show();
            $('#multiple-row-container').hide();

            $('#cas').prop('checked', false);
            $('#task').prop('checked', false);
            $('#manage-status-id').val(null);
            $('#role-for-status').val(null).trigger('change');
            $('#responsible').val(null).trigger('change');
        }

        $(document).on('click', '.opener', function () {
            let sId = $(this).attr('data-sid');
            let Title = $(this).attr('data-title');

            $.ajax({
                url : "{{ route('sales-order-manage-role-get') }}",
                type : "POST",
                data : {
                    id : sId
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success : function (response) {

                    $('#manager').modal('show');
                    $('#manager').find('#modal-title').text(Title);
                    $('#manage-status-id').val(sId);

                    if (response.exists) {
                        let data = response.data;
                        let pStatus = data.possible_status.split(',');
                        pStatus = pStatus.filter(function (el) {return el !== null && el !== '';});

                        if (pStatus.length > 0) {
                            $('#status-adder-into-modal').hide();
                            $('#multiple-row-container').show();

                            pStatus.forEach((value, index) => {

                                let cloned = $(content);

                                cloned.find('.removable-status').empty().append(`<select data-indexid="${lastElementIndex}" name="mstatus[${lastElementIndex}]" id="m-status-${lastElementIndex}" class="select2 select2-hidden-accessible m-status" style="width:100%" data-placeholder="Select a Status"> ${statusesHtml} </select> `);
                                cloned.find('.removable-status .m-status').select2({
                                    dropdownParent: $('#manager'),
                                    width: '100%',
                                    allowClear: true
                                });

                                cloned.find('.removable-status .m-status').val(value).trigger('change');

                                $('.upsertable').append(cloned.get(0));

                                $(`m-status-${lastElementIndex}`).rules('add', {
                                    required: true,
                                    messages: {
                                        required: "Select a status."
                                    }
                                });

                                lastElementIndex++;
                            });
                        }

                        $('#cas').prop('checked', data.for_admin);
                        $('#task').prop('checked', data.task);
                        $('#manage-status-id').val(sId);
                        $('#role-for-status').val(data.role_id).trigger('change');
                        $('#responsible').val(data.responsible).trigger('change');
                    }
                },
                complete : function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });
        });

        $(document).on('hidden.bs.modal', '#manager', function (e) {
            if (e.namespace == 'bs.modal') {
                resetModal();
            }
        });

        $(document).on('change', '.m-status', function (event) {
            if (isChanging) return;

            let indexId = $(this).data('indexid');
            let thisId = $(this).val();

            let that = $(this);

            if (thisId == '' || thisId == null) {
                return true;                
            }

            $('.m-status').not(this).each(function (index, element) {
                if ($(element).val() !== null && thisId == $(element).val()) {
                    isChanging = true;
                    $(that).val(null).trigger('change');
                    Swal.fire('Warning', 'Status is already selected.', 'warning');
                    isChanging = false;
                    return false;
                }
            });
        });

        $(document).on('click', '#status-adder-into-modal', function () {
            toggleAddButton(1);
            if ($('.upsertable tr').length < 1) {
                $(this).hide();
                $('#multiple-row-container').show();
                $('.upsertable').html(content);

                $('.upsertable tr').find('.removable-status').empty().append(`<select data-indexid="${lastElementIndex}" name="mstatus[${lastElementIndex}]" id="m-status-${lastElementIndex}" class="select2 select2-hidden-accessible m-status" style="width:100%" data-placeholder="Select a Status"> ${statusesHtml} </select> `);
                $('.upsertable tr').find('.removable-status .m-status').select2({
                    dropdownParent: $('#manager'),
                    width: '100%',
                    allowClear: true
                });

                $('.upsertable tr').find('.removable-status .m-status').rules('add', {
                    required: true,
                    messages: {
                        required: "Select a status."
                    }
                }); 
            }
        });

        $(document).on('click', '.addNewRow', function (event) {
            toggleAddButton(1);
            if (Object.keys(allStatuses).length < $('.upsertable tr').length + 1) {
                return false;
            }

            cloned = $('.upsertable').find('tr').eq(0).clone();
            lastElementIndex++;

            cloned.find('.removable-status').empty().append(`<select data-indexid="${lastElementIndex}" name="mstatus[${lastElementIndex}]" id="m-status-${lastElementIndex}" class="select2 select2-hidden-accessible m-status" style="width:100%" data-placeholder="Select a Status"> ${statusesHtml} </select> `);
            cloned.find('.m-status').select2({
                dropdownParent: $('#manager'),
                width: '100%',
                allowClear: true
            });


            cloned.find('label.error').remove();            
            $('.upsertable').append(cloned.get(0));

            cloned.find('.m-status').rules('add', {
                required: true,
                messages: {
                    required: "Select a status."
                }
            }); 

        });

        $(document).on('click', '.removeRow', function(event) {
            let count = $('.upsertable tr').length;

            if (count > 0) {
                $(this).closest("tr").remove();
                if (count === 1) {
                    $('#status-adder-into-modal').show();
                    $('#multiple-row-container').hide();
                }
            }

            toggleAddButton(0);
        });

        $('#manage-role-form').validate({
            rules: {
                role: {
                    required: true
                },
                responsible: {
                    required: true
                }
            },
            messages: {
                role: {
                    required: "Select a role."
                },
                responsible: {
                    required: "Select a responsible role."
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent("div"));
            },
            submitHandler:function(form, event) {
                event.preventDefault();

                $('button[type="submit"]').attr('disabled', true);

                $.ajax({
                    url: "{{ route('sales-order-manage-role') }}",
                    type: "POST",
                    data: $(form).serializeArray(),
                    success: function (response) {
                        if (response.status) {
                            Swal.fire('', response.messages, 'success');
                            $('#manager').modal('hide');
                            resetModal();
                        } else if (response.status == false) {
                            x(Object.values(response.messages).flat().toString())
                        } else {
                            Swal.fire('', 'Something went wrong. Please try again.', 'error');
                        }
                    },
                    complete: function (response) {
                        $('button[type="submit"]').attr('disabled', false);
                    }
                });
            }
        });
        //

        $("#role-for-status").select2({
            dropdownParent: $('#manager'),
            width: '100%',
            allowClear: true,
        })

        $("#responsible").select2({
            dropdownParent: $('#manager'),
            width: '100%',
            allowClear: true,
        })

        $('#sortable').sortable({
            handle: ".movable",
        });

        let hasDuplicateValues = (className) => {
            var valuesCount = {};
            var hasDuplicates = false;
            var valueOfDuplicate = '';

            $(className).each(function() {
                var value = $(this).val().toUpperCase();
                if (valuesCount[value]) {
                    valuesCount[value]++;
                } else {
                    valuesCount[value] = 1;
                }
            });

            $.each(valuesCount, function(key, count) {
                if (count > 1) {
                    hasDuplicates = true;
                    valueOfDuplicate = key;
                    return false;
                }
            });

            return {
                exists: hasDuplicates,
                value: valueOfDuplicate
            };
        }

        $('#cardForm').validate({
            submitHandler: function (form, event) {
                event.preventDefault();
                let isThereAnyCardWithoutName = false;

                $('.title-of-card').each(function (index, element) {
                    if ($(element).val().length < 1) {
                        isThereAnyCardWithoutName = true;
                    }
                });

                if (isThereAnyCardWithoutName) {
                    Swal.fire('', 'Provide card a name before you save.', 'error');
                    return false;
                } else {
                    let validateTitles = hasDuplicateValues('.title-of-card');

                    if (validateTitles.exists) {
                        Swal.fire('', `You can't give same name as <strong>"${validateTitles.value}"</strong> already exists.`, 'error');
                    } else {
                        form.submit();
                    }
                }
            }
        });

        $(document).on('click', '.sticky-add-icon', function () {
            let thisIndex = $(".sticky-add-icon").index($(this));
            let totalCards = $("#sortable").children().length;
            let thisColor = $(this).data('color');
            let toBeAppened = '';

            if (thisColor.length < 1) {
                thisColor = '#9cf';
            }

            toBeAppened = `
            <div class="card card-row card-secondary parent-card">
                <input type="hidden" name="sequence[]" value="">
                <div class="card-header px-2" style="border-bottom: 4px solid ${thisColor};">
                    ${totalCards - thisIndex !== 0 && addPermission ? `<span class="sticky-add-icon" data-color="${thisColor}"><i class="fa fa-plus" style="color:#bfbfbf;"></i></span>` : ''}
                    <div class="card-title  d-flex align-items-center justify-content-between">

                        <div style="line-height: 0;cursor: move">
                            <svg fill="#656565" width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="M8.5,10a2,2,0,1,0,2,2A2,2,0,0,0,8.5,10Zm0,7a2,2,0,1,0,2,2A2,2,0,0,0,8.5,17Zm7-10a2,2,0,1,0-2-2A2,2,0,0,0,15.5,7Zm-7-4a2,2,0,1,0,2,2A2,2,0,0,0,8.5,3Zm7,14a2,2,0,1,0,2,2A2,2,0,0,0,15.5,17Zm0-7a2,2,0,1,0,2,2A2,2,0,0,0,15.5,10Z"/></svg>
                        </div>

                        <input type="text" name="name[]" class="title-of-card f-14 m-auto" value="">

                        <div class="d-flex align-items-center card-options">
                            ${deletePermission}
                            <input type="color" name="color[]" class="color-picker" value="${thisColor}">
                        </div>

                    </div>
                </div>
                <div class="card-body">
                </div>
            </div>
            `;

            $(toBeAppened).insertAfter($(this).parent().parent());
        });

        $(document).on('change', '.color-picker', function () {
            $(this).parent().parent().parent().css('border-bottom', `5px solid ${$(this).val()}`);
            $(this).parent().parent().parent().find('.sticky-add-icon').attr('data-color', $(this).val());
        });

        $(document).on('focus', '.title-of-card', function () {
            $(this).next().attr('style', 'display:block!important;');
        });

        $(document).on('blur', '.title-of-card', function () {
            if ($(this).next().find('.color-picker').hasClass('is-active')) {
                $(this).next().find('.color-picker').removeClass('is-active')
                $(this).next().attr('style', 'display:none!important;');
            }
            
        });

        $(document).on('click', '.color-picker', function () {
            if (!$(this).hasClass('is-active')) {
                $(this).addClass('is-active');
            }

            $(this).parent().prev().focus();
        });

        $(document).on('click', '.fa-trash', function () {
            $(this).parent().parent().attr('style', 'display:none!important;');
            Swal.fire('', 'This functionality is in development.', 'info');            
        })

    });
</script>
@endsection