@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/automate.css') . '?v=' . time() }}">
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
    <div class="card border-0 card-row card-secondary parent-card @if($status->id == '1') disable-sorting @endif " data-mainstatus="{{ $status->id }}">
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
        <div class="card-body" style="padding: 0;" data-thisstatus="{{ $status->id }}">
            @php
                $trigger = Trigger::with(['nextstatus', 'currentstatus'])->where('status_id', $status->id)->orderBy('sequence', 'ASC')->get()->keyBy('sequence')->toArray();
            @endphp
            @for($i = 0; $i < $maxTriggers; $i++)
                <div class="cardDrag drag-area">
                    @if(isset($trigger[$i]))
                    <div class="card-body text-center custom-p portlet cursor-pointer min-max-height @if($trigger[$i]['type'] == 1) bg-light-green trigger-add-task @elseif($trigger[$i]['type'] == 2) bg-light-grey trigger-change-order-status @elseif($trigger[$i]['type'] == 3) bg-light-green trigger-change-order-user @endif   "  data-title="{{ $status->name }}"  data-sid="{{ $status->id }}" data-triggerid="{{ $trigger[$i]['id'] }}" 
                        @if($trigger[$i]['type'] == 1)
                            data-at-statusid="{{ $trigger[$i]['status_id'] }}"
                            data-at-taskdescription="{{ $trigger[$i]['task_description'] }}"
                            data-at-timetype="{{ $trigger[$i]['time_type'] }}"
                            data-at-hour="{{ $trigger[$i]['hour'] }}"
                            data-at-minute="{{ $trigger[$i]['minute'] }}"
                            data-at-actiontype="{{ $trigger[$i]['action_type'] }}"
                            data-at-type="{{ $trigger[$i]['type'] }}"
                        @else
                            data-cs-statusid="{{ $trigger[$i]['status_id'] }}"
                            data-cs-nextstatusid="{{ $trigger[$i]['next_status_id'] }}"
                            data-cs-timetype="{{ $trigger[$i]['time_type'] }}"
                            data-cs-hour="{{ $trigger[$i]['hour'] }}"
                            data-cs-minute="{{ $trigger[$i]['minute'] }}"
                            data-cs-actiontype="{{ $trigger[$i]['action_type'] }}"
                            data-cs-type="{{ $trigger[$i]['type'] }}"
                        @endif
                        >
                        <div class="d-flex flex-row portlet-header">
                            @if($trigger[$i]['type'] == 1)
                            <img src="{{ asset('assets/images/completed.png') }}" class="width-35" />
                            <div class="w-100">
                                <div class="f-12 text-start trigger-box-label-timetype">
                                @if($trigger[$i]['action_type'] == 1)
                                After moved to this status
                                @elseif($trigger[$i]['action_type'] == 2)
                                After created in this status
                                @elseif($trigger[$i]['action_type'] == 3)
                                After moved or created in this status
                                @endif
                                @if($trigger[$i]['time_type'] == 2)
                                after 5 minutes
                                @elseif($trigger[$i]['time_type'] == 3)
                                after 10 minutes
                                @elseif($trigger[$i]['time_type'] == 4)
                                after one day
                                @elseif($trigger[$i]['time_type'] == 5)
                                    @if($trigger[$i]['hour'] < 100)
                                    after {{ sprintf('%02d', $trigger[$i]['hour']) }} hours {{ sprintf('%02d', $trigger[$i]['minute']) }} minutes
                                    @else
                                    after {{ sprintf('%03d', $trigger[$i]['hour']) }} hours {{ sprintf('%02d', $trigger[$i]['minute']) }} minutes
                                    @endif
                                @endif
                                </div>
                                <div class="text-start">
                                    <span class="f-12"> <strong>Task:</strong> <span class="trigger-box-label-task-description"> {{ Str::of(strip_tags($trigger[$i]['task_description']))->limit(18)  }} </span> </span>
                                    {{-- <i class="fa fa-bars drag-task float-end"></i> <i class="fa fa-copy copy-task float-end" ></i> --}}
                                </div>
                                <div class="inp-groups">
                                    <input type="hidden" data-type="1" class="trigger-saver-input" name="task[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][status]" value="{{ $status->id }}" />
                                    <input type="hidden" data-type="1" class="trigger-saver-input-maintype" name="task[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][maintype]" value="{{ $trigger[$i]['action_type'] }}" />
                                    <input type="hidden" data-type="1" class="trigger-saver-input-timetype" name="task[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][timetype]" value="{{ $trigger[$i]['time_type'] }}" />
                                    <input type="hidden" data-type="1" class="trigger-saver-input-hour" name="task[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][hour]" value="{{ $trigger[$i]['hour'] }}" />
                                    <input type="hidden" data-type="1" class="trigger-saver-input-minute" name="task[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][minute]" value="{{ $trigger[$i]['minute'] }}" />
                                    <input type="hidden" data-type="1" class="trigger-saver-input-desc" name="task[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][desc]" value="{{ $trigger[$i]['task_description'] }}" />
                                    <input type="hidden" data-type="1" class="trigger-saver-input-sequence" name="task[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][sequence]" value="{{ $trigger[$i]['sequence'] }}" />
                                    <input type="hidden" data-type="1" class="trigger-saver-input-edit_id" name="task[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][edit_id]" value="{{ $trigger[$i]['id'] }}" />
                                </div>
                            </div>
                            @elseif($trigger[$i]['type'] == 2)
                            <img src="{{ asset('assets/images/edit.png') }}" class="width-35" />
                            <div class="w-100">
                                <div class="f-12 text-start trigger-box-label-timetype">
                                @if($trigger[$i]['action_type'] == 1)
                                After moved to this status
                                @elseif($trigger[$i]['action_type'] == 2)
                                After created in this status
                                @elseif($trigger[$i]['action_type'] == 3)
                                After moved or created in this status
                                @endif
                                @if($trigger[$i]['time_type'] == 2)
                                after 5 minutes
                                @elseif($trigger[$i]['time_type'] == 3)
                                after 10 minutes
                                @elseif($trigger[$i]['time_type'] == 4)
                                after one day
                                @elseif($trigger[$i]['time_type'] == 5)
                                    @if($trigger[$i]['hour'] < 100)
                                    after {{ sprintf('%02d', $trigger[$i]['hour']) }} hours {{ sprintf('%02d', $trigger[$i]['minute']) }} minutes
                                    @else
                                    after {{ sprintf('%03d', $trigger[$i]['hour']) }} hours {{ sprintf('%02d', $trigger[$i]['minute']) }} minutes
                                    @endif
                                @endif
                                </div>
                                <div class="text-start">
                                    <strong class="f-12">Change status:</strong>
                                    <span class="status-lbl f-10 trigger-box-label-task-ns" style="background: {{ $trigger[$i]['nextstatus']['color'] }};color:{{ Helper::generateTextColor($trigger[$i]['nextstatus']['color']) }};text-transform:uppercase;"> {{ $trigger[$i]['nextstatus']['name'] }} </span>
                                    {{-- <i class="fa fa-bars drag-task float-end"></i> <i class="fa fa-copy copy-task float-end" ></i> --}}
                                </div>
                                <div class="inp-groups">
                                    <input type="hidden" data-type="2" class="trigger-saver-input" name="statuschange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][status]" value="{{ $status->id }}" />
                                    <input type="hidden" data-type="2" class="trigger-saver-input-maintype" name="statuschange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][maintype]" value="{{ $trigger[$i]['action_type'] }}" />
                                    <input type="hidden" data-type="2" class="trigger-saver-input-timetype" name="statuschange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][timetype]" value="{{ $trigger[$i]['time_type'] }}" />
                                    <input type="hidden" data-type="2" class="trigger-saver-input-hour" name="statuschange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][hour]" value="{{ $trigger[$i]['hour'] }}" />
                                    <input type="hidden" data-type="2" class="trigger-saver-input-minute" name="statuschange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][minute]" value="{{ $trigger[$i]['minute'] }}" />
                                    <input type="hidden" data-type="2" class="trigger-saver-input-next-status" name="statuschange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][nextstatus]" value="{{ $trigger[$i]['next_status_id'] }}" />
                                    <input type="hidden" data-type="2" class="trigger-saver-input-sequence" name="statuschange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][sequence]" value="{{ $trigger[$i]['sequence'] }}" />
                                    <input type="hidden" data-type="2" class="trigger-saver-input-edit_id" name="statuschange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][edit_id]" value="{{ $trigger[$i]['id'] }}" />
                                </div>
                            </div>
                            @elseif($trigger[$i]['type'] == 3)
                            <div class="w-100">
                                <img src="{{ asset('assets/images/edit.png') }}" class="width-35" />
                                <div class="f-12 text-start trigger-box-label-timetype">
                                @if($trigger[$i]['action_type'] == 1)
                                After moved to this status
                                @elseif($trigger[$i]['action_type'] == 2)
                                After created in this status
                                @elseif($trigger[$i]['action_type'] == 3)
                                After moved or created in this status
                                @endif
                                @if($trigger[$i]['time_type'] == 2)
                                after 5 minutes
                                @elseif($trigger[$i]['time_type'] == 3)
                                after 10 minutes
                                @elseif($trigger[$i]['time_type'] == 4)
                                after one day
                                @elseif($trigger[$i]['time_type'] == 5)
                                    @if($trigger[$i]['hour'] < 100)
                                    after {{ sprintf('%02d', $trigger[$i]['hour']) }} hours {{ sprintf('%02d', $trigger[$i]['minute']) }} minutes
                                    @else
                                    after {{ sprintf('%03d', $trigger[$i]['hour']) }} hours {{ sprintf('%02d', $trigger[$i]['minute']) }} minutes
                                    @endif
                                @endif
                                </div>
                                <div class="text-start">
                                    <span class="f-12"> <strong>Change order's user:</strong> {{ $trigger[$i]['user']['name'] }} </span>
                                    {{-- <i class="fa fa-bars drag-task float-end"></i> <i class="fa fa-copy copy-task float-end" ></i> --}}
                                </div>
                                <div class="inp-groups">
                                    <input type="hidden" class="trigger-saver-input" data-type="3" name="userchange[{{ $status->id }}][{{ $trigger[$i]['sequence'] }}][status][]" value="{{ $status->id }}" />
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="card-body text-center d-flex align-items-center justify-content-center custom-p cursor-pointer opener min-max-height" data-title="{{ $status->name }}"  data-sid="{{ $status->id }}">
                        <i class="fa fa-plus-circle"></i> Add trigger
                    </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>
    @empty
    @endforelse
    
</div>
</form>
{{-- Board --}}

</div>

{{-- Trigger Modal --}}
@include('sales-orders-status.modal.triggers')
{{-- Trigger Modal --}}

{{-- Manage Next Possible Status Modal --}}
@include('sales-orders-status.modal.manage-next-possible-status')
{{-- Manage Next Possible Status Modal --}}

{{-- Add Task Modal --}}
@include('sales-orders-status.modal.add-task')
{{-- Add Task Modal --}}

{{-- Change Status Modal --}}
@include('sales-orders-status.modal.change-lead-stage')
{{-- Change Status Modal --}}

@endsection

@section('script')
<script>
    let deletePermission = '';
    let addPermission = false;
    let isChanging = false;
    let performingStatusTitle = '';
    let selectedOpt = null;
    let thisWindowId = uuid();
    let selectedColorBg = '#e8e8e8';
    let triggerBlock = null;
    let appUrl = "{{ asset('') }}";

    @if(auth()->user()->hasPermission('sales-order-status.delete'))
    deletePermission = '<span class="me-2"> <i class="fa fa fa-trash"></i></span>';
    @endif

    @if(auth()->user()->hasPermission('sales-order-status.create'))
    addPermission = true;
    @endif

    let lastElementIndex = 0;
    let modalTitle = '';

    var content = `<tr><td class="block-a"><div style="min-width: 200px;width: 100%" class="removable-status"><select name="mstatus[0]" data-indexid="0" id="m-status-0" class="select2 select2-hidden-accessible m-status" style="width:100%" data-placeholder="Select a Status"><option value="" selected> --- Select a Status --- </option></select></div></td><td style="width:100px;"><div class="df-fr-jse" style="min-width: 100px;"><button type="button" class="btn btn-primary btn-sm addNewRow">+</button> <button type="button" class="btn btn-danger btn-sm removeRow" tabindex="-1">-</button></div></td></tr>`;
    var statusesHtml = `<option value="" selected> --- Select a Status --- </option>`;
    var allStatuses = {!! json_encode($s) !!};
    var allStatusesObj = {!! json_encode($statuses) !!};
    var editingBlock = null;

    $(document).ready(function() {

        $.validator.setDefaults({
            ignore: []
        });

        $(document).on('click', '.opener', function () {
            let thisStatus = $(this).attr('data-sid');
            modalTitle = $(this).attr('data-title');
            triggerBlock = this;

            if (isNumeric(thisStatus)) {
                $('#trigger-options-modal').modal('show');
                $('#performing-status').val(thisStatus);
                performingStatusTitle = modalTitle.toUpperCase();
            }
        });

        $(document).on('hidden.bs.modal', '#manage-next-possible-status', function (event) {
            if (event.namespace == 'bs.modal') {
                resetModal();
            }
        });

        $(document).on('click', '#manage-status-btn', function () {
            let thisStatus = $('#performing-status').val();

            if (isNumeric(thisStatus)) {
                $.ajax({
                    url : "{{ route('sales-order-manage-role-get') }}",
                    type : "POST",
                    data : {
                        id : thisStatus
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success : function (response) {

                        $('#trigger-options-modal').modal('hide');
                        $('#manage-next-possible-status').modal('show');
                        $('#manage-next-possible-status').find('#modal-title').text(modalTitle.toUpperCase());
                        $('#manage-status-id').val(thisStatus);

                        if ('updatedStatuses' in response) {
                            allStatuses = response.updatedStatuses;

                            statusesHtml = `<option value="" selected> --- Select a Status --- </option>`;
                            for (key in allStatuses) {
                                statusesHtml += `<option value="${key}"> ${allStatuses[key]} </option>`;
                            }
                        }

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
                                        dropdownParent: $('#manage-next-possible-status'),
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
                            $('#manage-status-id').val(thisStatus);
                            $('#role-for-status').val(data.role_id).trigger('change');
                            $('#responsible').val(data.responsible).trigger('change');
                            toggleAddButton(0);
                        }
                    },
                    complete : function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            }
        });


        /** Add Task JS  **/
        $(document).on('click', '#add-task-btn', function() {
            let thisStatus = $('#performing-status').val();

            if (isNumeric(thisStatus)) {
                $('#trigger-options-modal').modal('hide');
                $('#editing-add-task').val('0');
                $('#add-task').modal('show');
                $('#add-task').find('#modal-title-add-task').text(performingStatusTitle);
                $('#manage-status-id-for-add-task').val(thisStatus);
            }

        });

        $(document).on('click', '.trigger-add-task', function () {

            let thisTrigger = $(this).attr('data-triggerid');
            let thisTitle = $(this).attr('data-title');
            let thisstatus = $(this).parent().parent().attr('data-thisstatus');
            editingBlock = this;

            let dt = {
                status_id: $(this).attr('data-at-statusid'),
                task_description: $(this).attr('data-at-taskdescription'),
                time_type: $(this).attr('data-at-timetype'),
                hour: $(this).attr('data-at-hour'),
                minute: $(this).attr('data-at-minute'),
                action_type: $(this).attr('data-at-actiontype'),
                type: $(this).attr('data-at-type')
            };
            
            $('#editing-add-task').val('1');
            $('#add-task').modal('show');
            $('#add-task').find('#modal-title-add-task').text(thisTitle);
            $('#manage-status-id-for-add-task').val(dt.status_id);
            $('#task-desc').val(dt.task_description);

            let dropdownText = 'Execute: ';
            let timeString = `Immediately`;

            if (dt.time_type == 1) {
                timeString = `Immediately`;
            } else if (dt.time_type == 2) {
                timeString = `5 minutes`;
            } else if (dt.time_type == 3) {
                timeString = `10 minutes`;
            } else if (dt.time_type == 4) {
                timeString = `one day`;
            } else if (dt.time_type == 5) {
                timeString = `Before delay ${dt.hour} hour ${dt.minute} minute`;
            }

            dropdownText += timeString;

            if (dt.action_type == 1) {
                dropdownText += ` After moved to this status`;
            } else if (dt.action_type == 2) {
                dropdownText += ` After created in this status`;
            } else {
                dropdownText += ` After moved or created in this status`;
            }
            
            $('.status-dropdown-toggle-inner').find('span').text(dropdownText);

            let selectedEle = $('.status-dropdown-menu-inner').find(`.no-btn:eq(${dt.action_type - 1})`);

            if ($(selectedEle).length > 0) {
                $(selectedEle).attr('data-selchild', dt.time_type);
                $(selectedEle).text(timeString);
                $(selectedEle).parent().css('background-color', selectedColorBg);

                $('.status-dropdown-menu-inner-ul').attr('data-parenttype', dt.action_type);

                let selectedInnerEle = $('.status-dropdown-menu-inner-ul').find(`li:eq(${dt.time_type - 1})`);

                if ($(selectedInnerEle).length > 0) {
                    $(selectedInnerEle).css('background-color', selectedColorBg);

                    if (dt.time_type == 5) {
                        $('#add-task-hour').val(dt.hour);
                        $('#add-task-minute').val(dt.minute);
                    }
                }
            }

            $('#manage-status-id-for-add-task').val(dt.status_id);
            $('#manage-order-time-for-add-task').val(dt.time_type);
            $('#manage-order-type-for-add-task').val(dt.action_type);
            $('#manage-order-status-for-add-task').val(thisstatus);

        });

        $(document).on('click', '.trigger-change-order-status', function () {
            return false;

            let thisTitle = $(this).attr('data-title');
            let thisstatus = $(this).parent().parent().attr('data-thisstatus');
            editingBlock = this;   

            let dt = {
                status_id: $(this).attr('data-cs-statusid'),
                next_status_id: $(this).attr('data-cs-nextstatusid'),
                time_type: $(this).attr('data-cs-timetype'),
                hour: $(this).attr('data-cs-hour'),
                minute: $(this).attr('data-cs-minute'),
                action_type: $(this).attr('data-cs-actiontype'),
                type: $(this).attr('data-cs-type')
            };

            $('#editing-change-lead-status').val('1');
            $('#lead-stage').modal('show');
            $('#lead-stage').find('#modal-title-lead-stage').text(thisTitle);
            $('#manage-status-id-for-change-lead-stage').val(dt.status_id);
            $('#manage-order-status-for-change-lead-stage').val(dt.next_status_id);

            $(".status-dropdown-inner-2 .status-dropdown-menu-inner-2").hide();
            $(".status-dropdown-for-cs .status-dropdown-menu-for-cs").hide();
        });

        $(document).on('click', function(event) {
            var target = $(event.target);
            
            if (!target.parents().hasClass("status-dropdown-inner") && !$('.status-dropdown-menu-inner').hasClass('auto-hide') && $('.dropdown-menu-inner-sub').css('display') == 'none') {
                $(".status-dropdown-inner .status-dropdown-menu-inner").hide();
                $(".status-dropdown-inner .status-dropdown-toggle-inner").removeClass("active");
            }

            if (!target.parents().hasClass("status-dropdown-inner-2") && !$('.status-dropdown-menu-inner-2').hasClass('auto-hide') && $('.dropdown-menu-inner-sub-2').css('display') == 'none') {
                $(".status-dropdown-inner-2 .status-dropdown-menu-inner-2").hide();
                $(".status-dropdown-inner-2 .status-dropdown-toggle-inner-2").removeClass("active");
            }
        });

        $(document).on('click', '.status-dropdown-toggle-inner', function() {
            var isHidden = $(this).parents(".status-dropdown-inner").children(
                ".status-dropdown-menu-inner").is(":hidden");
            $(".status-dropdown-inner .status-dropdown-menu-inner").hide();
            $(".status-dropdown-inner .status-dropdown-toggle-inner").removeClass("active");

            if (isHidden) {
                $(this).parents(".status-dropdown-inner").children(".status-dropdown-menu-inner")
                    .toggle()
                    .parents(".status-dropdown-inner")
                    .children(".status-dropdown-toggle-inner").addClass("active");
            }
        });

        $(document).on('click', '.status-dropdown-menu-inner li', function(e) {

            var bgColor = $(this).css("background-color");
            var text = $(this).text();
            var thisTime = $(this).attr('data-time');
            var thisSid = $(this).attr('data-sid');
            var thisTtype = $(this).attr('data-ttype');
            var thisMtype = $(this).attr('data-mtype');

            var dropdownToggle = $(this).closest(".status-dropdown-inner").find(".status-dropdown-toggle-inner");
            var dropdownToggleText = $(this).closest(".status-dropdown-inner").find(".status-dropdown-toggle-inner");

            dropdownToggleText.html(`
                    <span> Execute: ${text} </span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                        <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                    </svg>
            `);

            dropdownToggle.css("background-color", '#fff');
            dropdownToggle.css("color", '#000');

            if ($(this).hasAttr('data-time')) {
                $('#manage-order-time-for-add-task').val(thisTime);
            }

            if ($(this).hasClass('selectable')) {
                if ($(this).hasAttr('data-mtype')) {
                    $('#manage-order-type-for-add-task').val(thisMtype);

                    let el = $(this).find('.no-btn');
                    if ($(el).hasAttr('data-selchild') && !isNaN($(el).attr('data-selchild'))) {
                        $('#manage-order-time-for-add-task').val($(el).attr('data-selchild'));
                    }
                }
            }

            $('.selectable').css('background', '#fff');
            $(this).css('background', selectedColorBg);

            if (!$(e.target).hasClass('no-btn')) {
                $(this).parent().hide();
                dropdownToggle.removeClass("active");
                $('.dropdown-menu-inner-sub').css('display', 'none');
            }

        });

        $(document).on('click', '.no-btn', function () {
            let top = $(this).attr('data-top');
            let left = $(this).attr('data-left');
            let parent = $(this).attr('data-parent');
            let selector = $(this).attr('data-firstclass');

            $(selector).show();
            $(selector).css({
                'left' : `${left}px`,
                'top' : `${top}px`
            });

            $(selector).attr('data-parenttype', parent);
        })

        $(document).on('click', '.selectable-inner', function (event) {
            let parent = $(this).parent().parent().attr('data-parenttype');
            let type = $(this).attr('data-ttype');

            let hour = $('#add-task-hour').val();
            let minute = $('#add-task-minute').val();
            
            if ($(`.opt-${parent}`).length > 0) {
                let timestamp = '';

                if (type == '1') {
                    timestamp = 'Immediately';
                } else if (type == '2') {
                    timestamp = '5 minutes';
                } else if (type == '3') {
                    timestamp = '10 minutes';
                } else if (type == '4') {
                    timestamp = 'one day';
                } else if (type == '5') {
                    timestamp = `Before delay ${hour} hour ${minute} minute`;
                }

                $(`.opt-${parent}`).text(timestamp)
                $('#manage-order-time-for-add-task').val(type)
                $(`.opt-${parent}`).attr('data-selchild', type);
            }
            
            $('.selectable-inner').css('background', '#fff');
            $('.status-dropdown-menu-inner').addClass('auto-hide');
            $(this).css('background', selectedColorBg);

            if (((hour == '' ||  minute == '') || (isNaN(hour) || isNaN(minute))) && type == '5') {
                $('.dropdown-menu-inner-sub-overlay').removeClass('d-none');
                return false;
            } else {
                $('.dropdown-menu-inner-sub-overlay').addClass('d-none');
            }

            if (!($(event.target).hasClass('add-task-hour') || $(event.target).hasClass('add-task-minute'))) {
                $('.dropdown-menu-inner-sub').hide();
            }
        });

        $(document).on('hidden.bs.modal', '#add-task', function(event) {
            if (event.namespace == 'bs.modal') {
                $('#task-desc').val(null);

                $('#manage-status-id-for-add-task').val(null);
                $('#manage-order-time-for-add-task').val('1');
                $('#manage-order-type-for-add-task').val('1');
                $('#manage-order-status-for-add-task').val(null);
                
                $('#add-task-hour').val(null).css('border-color', '#000');
                $('#add-task-minute').val(null).css('border-color', '#000');

                $('.hideable-add-task').show();
                $('.status-dropdown-menu-inner').find('.no-btn').text('Immediately');
                $('.add-task-def-selected').text(' Execute: Immediately After moved to this status ');
                $('.dropdown-menu-inner-sub').attr('data-parenttype', '1');
                $('.status-dropdown-toggle-inner').find('span').text('Execute: Immediately After moved to this status');
                $('.status-dropdown-menu-inner').removeClass('auto-hide');
                $('.selectable-inner').css('background', '#fff');
                $('.selectable').css('background', '#fff');
                $('.dropdown-menu-inner-sub').css('display', 'none');
                $('#task-desc').css('height', '35px');
                $('#at-type-error').text('');
                $('#at-status-error').text('');
                $('#editing-add-task').val('0');

            }
        });

        $('#addToTask').validate({
            rules: {
                add_task_hour: {
                    digits: true,
                    min: 0,
                    max: 720
                },
                add_task_minute: {
                    digits: true,
                    min: 0,
                    max: 60
                },
                task_desc: {
                    required: true,
                    maxlength: 500
                }
            },
            messages: {
                add_task_hour: {
                    digits: "Only digits allowed.",
                    min: "Minimum 0 hour allowed.",
                    max: "Maximum 720 hours allowed."
                },
                add_task_minute: {
                    digits: "Only digits allowed.",
                    min: "Minimum 0 minute allowed.",
                    max: "Maximum 60 minutes allowed."
                },
                task_desc: {
                    required: 'Enter description.',
                    maxlength: 'Maximum 500 characters allowed.'
                }
            },
            errorPlacement: function(error, element) {
                if ($(element).hasClass('add-task-minute')) {
                    $('#at-type-error').text(error.text());
                    $('#add-task-minute').css('border-color', '#ff0000');
                } else if ($(element).hasClass('add-task-hour')) {
                    $('#at-type-error').text(error.text());
                    $('#add-task-hour').css('border-color', '#ff0000');
                } else {
                    $('#at-status-error').text('');
                    $('#at-type-error').text('');
                    $('#add-task-hour').css('border-color', '#000');
                    $('#add-task-minute').css('border-color', '#000');
                }

                if ($(element).hasClass('task-desc')) {
                    error.appendTo(element.parent("div"));
                }
            },
            submitHandler: function(form, event) {
                event.preventDefault();

                if ($(form).valid()) {
                    let formData = {};
                    let thisData = $(form).serializeArray();

                    thisData.forEach(element => {
                        formData[element.name] = element.value;
                    });

                    if ($('#editing-add-task').val() == '1') {
                        let index = $(editingBlock).parent().index();

                        $(editingBlock).find('.trigger-saver-input').attr('name', `task[${formData.atstatus}][${index}][status]`);
                        $(editingBlock).find('.trigger-saver-input').val(formData.atstatus);

                        $(editingBlock).find('.trigger-saver-input-maintype').attr('name', `task[${formData.atstatus}][${index}][maintype]`);
                        $(editingBlock).find('.trigger-saver-input-maintype').val(formData.attype);
                        
                        $(editingBlock).find('.trigger-saver-input-timetype').attr('name', `task[${formData.atstatus}][${index}][timetype]`);
                        $(editingBlock).find('.trigger-saver-input-timetype').val(formData.attime);

                        $(editingBlock).find('.trigger-saver-input-hour').attr('name', `task[${formData.atstatus}][${index}][hour]`);
                        $(editingBlock).find('.trigger-saver-input-hour').val(formData.add_task_hour);

                        $(editingBlock).find('.trigger-saver-input-minute').attr('name', `task[${formData.atstatus}][${index}][minute]`);
                        $(editingBlock).find('.trigger-saver-input-minute').val(formData.add_task_minute);
                        
                        $(editingBlock).find('.trigger-saver-input-sequence').attr('name', `task[${formData.atstatus}][${index}][sequence]`);
                        $(editingBlock).find('.trigger-saver-input-sequence').val(index);

                        $(editingBlock).find('.trigger-saver-input-desc').attr('name', `task[${formData.atstatus}][${index}][desc]`);
                        $(editingBlock).find('.trigger-saver-input-desc').val(formData.task_desc);

                        $(editingBlock).attr('data-at-statusid', formData.atstatus);
                        $(editingBlock).attr('data-at-taskdescription', formData.task_desc);
                        $(editingBlock).attr('data-at-timetype', formData.attime);
                        $(editingBlock).attr('data-at-hour', formData.add_task_hour);
                        $(editingBlock).attr('data-at-minute', formData.add_task_minute);
                        $(editingBlock).attr('data-at-actiontype', formData.attype);
                        
                        let timeString = ``;
                        let dropdownText = '';

                        if (formData.attime == 2) {
                            timeString = ` after 5 minutes`;
                        } else if (formData.attime == 3) {
                            timeString = ` after 10 minutes`;
                        } else if (formData.attime == 4) {
                            timeString = ` after one day`;
                        } else if (formData.attime == 5) {
                            timeString = ` after delay ${formData.add_task_hour} hour ${formData.add_task_minute} minute`;
                        }

                        if (formData.attype == 1) {
                            dropdownText += ` After moved to this status`;
                        } else if (formData.attype == 2) {
                            dropdownText += ` After created in this status`;
                        } else {
                            dropdownText += ` After moved or created in this status`;
                        }

                        dropdownText += timeString;
                        
                        $(editingBlock).find('.trigger-box-label-task-description').html(formData.task_desc.substring(0, 18) + '...');
                        $(editingBlock).find('.trigger-box-label-timetype').html(dropdownText);

                        $('#add-task').modal('hide');
                        
                    } else {
                        if ($(triggerBlock).length > 0 && $(triggerBlock).parent().parent().parent().hasAttr('data-mainstatus')) {
                            let input = `<div class="inp-groups"><input type="hidden" data-type="1" class="trigger-saver-input" name="task[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][status]" value="${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}" />
                                <input type="hidden" data-type="1" class="trigger-saver-input-maintype" name="task[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][maintype]" value="${formData.attype}" />
                                <input type="hidden" data-type="1" class="trigger-saver-input-timetype" name="task[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][timetype]" value="${formData.attime}" />
                                <input type="hidden" data-type="1" class="trigger-saver-input-hour" name="task[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][hour]" value="${formData.add_task_hour}" />
                                <input type="hidden" data-type="1" class="trigger-saver-input-minute" name="task[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][minute]" value="${formData.add_task_minute}" />
                                <input type="hidden" data-type="1" class="trigger-saver-input-desc" name="task[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][desc]" value="${formData.task_desc}" />
                                <input type="hidden" data-type="1" class="trigger-saver-input-sequence" name="task[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][sequence]" value="${$(triggerBlock).parent().index()}" />
                                </div>`;

                            $(triggerBlock).attr('data-at-statusid', formData.atstatus);
                            $(triggerBlock).attr('data-at-taskdescription', formData.task_desc);
                            $(triggerBlock).attr('data-at-timetype', formData.attime);
                            $(triggerBlock).attr('data-at-hour', formData.add_task_hour);
                            $(triggerBlock).attr('data-at-minute', formData.add_task_minute);
                            $(triggerBlock).attr('data-at-actiontype', formData.attype);

                            $(triggerBlock).removeClass('opener');
                            $(triggerBlock).removeClass('justify-content-center');
                            $(triggerBlock).addClass('trigger-add-task');
                            $(triggerBlock).addClass('bg-light-green');
                            $(triggerBlock).html(getTriggerTypes(1, formData.attype, {
                                description : formData.task_desc,
                                time : formData.attime,
                                hour : formData.add_task_hour,
                                minute : formData.add_task_minute
                            },
                            input));

                            $('#add-task').modal('hide');
                        }
                    }

                }

                return false;
            }
        });
        /** Add Task JS **/


        /** Change Order Status **/
        $(document).on('click', '#lead-stage-btn', function() {
            let thisStatus = $('#performing-status').val();

            if (isNumeric(thisStatus)) {
                $.ajax({
                    url: "{{ route('sales-order-next-status') }}",
                    type: 'POST',
                    data: {
                        id: thisStatus
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        $('#trigger-options-modal').modal('hide');
                        $('#lead-stage').modal('show');
                        $('#lead-stage').find('#modal-title-lead-stage').text(performingStatusTitle);
                        $('#manage-status-id-for-change-lead-stage').val(thisStatus);
                        $('#stage-container').html(response.view);
                        $('#editing-change-lead-status').val('0');

                        if (Object.values(response.data).length > 0) {
                            $('#manage-order-status-for-change-lead-stage').val(Object.keys(
                                response.data)[0]);
                                $('#choosenColor').val('#dd2d20');
                        } else {
                            $('.hideable').hide();
                        }

                        if (response.added) {
                            $('#manage-order-status-for-change-lead-stage').val(response
                                .addedData.status);
                            $('#manage-order-time-for-change-lead-stage').val(response
                                .addedData.type);

                            $('.status-dropdown-toggle-status').text(response.addedData
                                .status_text);
                            $('.status-dropdown-toggle-status').css('background', response
                                .addedData.status_color);
                            $('.status-dropdown-toggle-status').css('color',
                                generateTextColor(response.addedData.status_color));

                            if (response.addedData.type == 5) {
                                $('#hour').val(response.addedData.hour);
                                $('#minute').val(response.addedData.minute);
                                $('.status-dropdown-toggle-2').text(`${response.addedData.hour} hours ${response.addedData.minute} minutes  ${getTypes(response.addedData.type)}`);
                            } else {
                                $('.status-dropdown-toggle-2').text(getTypes(response.addedData.type));
                            }
                        }
                    },
                    complete: function() {
                        $('body').find('.LoaderSec').addClass('d-none');
                        $(".status-dropdown-inner-2 .status-dropdown-menu-inner-2").hide();
                        $(".status-dropdown-for-cs .status-dropdown-menu-for-cs").hide();
                    }
                });
            }

        });

        $(document).on('hidden.bs.modal', '#lead-stage', function(event) {
            if (event.namespace == 'bs.modal') {

                $('#manage-status-id-for-change-lead-stage').val(null);
                $('#manage-order-time-for-change-lead-stage').val('1');
                $('#manage-order-type-for-change-lead-stage').val('1');
                $('#manage-order-status-for-change-lead-stage').val(null);
                
                $('#change-stage-hour').val(null).css('border-color', '#000');
                $('#change-stage-minute').val(null).css('border-color', '#000');

                $('.hideable-change-stage').show();
                $('.status-dropdown-menu-inner-2').find('.no-btn').text('Immediately');
                $('.add-task-def-selected').text(' Execute: Immediately After moved to this status ');
                $('.dropdown-menu-inner-2-sub').attr('data-parenttype', '1');
                $('.status-dropdown-toggle-inner-2').find('span').text('Execute: Immediately After moved to this status');
                $('.status-dropdown-menu-inner-2').removeClass('auto-hide');
                $('.selectable-inner-2').css('background', '#fff');
                $('.dropdown-menu-inner-2-sub').css('display', 'none');
                $('#cs-type-error').text('');
                $('.no-btn').attr('data-selchild', '1');
                $('.selectable-inner-p-2').css('background', '#fff');
                $('.selectable-inner-p-2').css('color', '#000');
                $('#editing-change-lead-status').val('0');
                editingBlock = null;

            }
        });

        $(document).on('click', '.status-dropdown-toggle-inner-2', function() {
            var isHidden = $(this).parents(".status-dropdown-inner-2").children(
                ".status-dropdown-menu-inner-2").is(":hidden");
            $(".status-dropdown-inner-2 .status-dropdown-menu-inner-2").hide();
            $(".status-dropdown-inner-2 .status-dropdown-toggle-inner-2").removeClass("active");

            if (isHidden) {
                $(this).parents(".status-dropdown-inner-2").children(".status-dropdown-menu-inner-2")
                    .toggle()
                    .parents(".status-dropdown-inner-2")
                    .children(".status-dropdown-toggle-inner-2").addClass("active");
            }
        });

        $(document).on('click', '.status-dropdown-menu-inner-2 li', function(e) {

            var text = $(this).text();
            var type = $(this).attr('data-mtype');

            var dropdownToggle = $(this).closest(".status-dropdown-inner-2").find(".status-dropdown-toggle-inner-2");
            var dropdownToggleText = $(this).closest(".status-dropdown-inner-2").find(".status-dropdown-toggle-inner-2");

            dropdownToggleText.html(`
            <span> Execute: ${text} </span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
            </svg>
            `);

            if ($(this).hasClass('selectable-inner-p-2') && $(this).hasAttr('data-mtype')) {
                $('#manage-order-type-for-change-lead-stage').val(type);
                let el = $(this).find('.no-btn');
                if ($(el).hasAttr('data-selchild') && !isNaN($(el).attr('data-selchild'))) {
                    $('#manage-order-time-for-change-lead-stage').val($(el).attr('data-selchild'));
                }
            }

            $('.selectable-inner-p-2').css('background', '#fff');
            $(this).css('background', selectedColorBg);

            if (!$(e.target).hasClass('no-btn')) {
                $(this).parent().hide();
                dropdownToggle.removeClass("active");
                $('.dropdown-menu-inner-2-sub').css('display', 'none');
            }
        });

        $(document).on('click', '.status-dropdown-menu-inner-2-ul li', function(e) {
            let parent = $(this).parent().parent().attr('data-parenttype');
            let type = $(this).attr('data-ttype');

            let hour = $('#change-stage-hour').val();
            let minute = $('#change-stage-minute').val();
            
            if ($(`.opt-2-${parent}`).length > 0) {
                let timestamp = '';

                if (type == '1') {
                    timestamp = 'Immediately';
                } else if (type == '2') {
                    timestamp = '5 minutes';
                } else if (type == '3') {
                    timestamp = '10 minutes';
                } else if (type == '4') {
                    timestamp = 'one day';
                } else if (type == '5') {
                    timestamp = `Before delay ${hour} hour ${minute} minute`;
                }

                $(`.opt-2-${parent}`).text(timestamp)
                $('#manage-order-time-for-change-lead-stage').val(type)
                $(`.opt-2-${parent}`).attr('data-selchild', type);
            }
            
            $('.status-dropdown-menu-inner').addClass('auto-hide');

            $('.selectable-inner-2').css('background', '#fff');
            $(this).css('background', selectedColorBg);

            if (((hour == '' ||  minute == '') || (isNaN(hour) || isNaN(minute))) && type == '5') {
                $('.dropdown-menu-inner-2-sub-overlay').removeClass('d-none');
                return false;
            } else {
                $('.dropdown-menu-inner-2-sub-overlay').addClass('d-none');
            }

            if (!($(event.target).hasClass('change-stage-hour') || $(event.target).hasClass('change-stage-minute'))) {
                $('.dropdown-menu-inner-2-sub').hide();
            }
        });

        $(document).on('click', '.selectable-inner-2', function (event) {
            let parent = $(this).parent().parent().attr('data-parenttype');
            let type = $(this).attr('data-ttype');

            let hour = $('#change-stage-hour').val();
            let minute = $('#change-stage-minute').val();
            
            if ($(`.opt-2-${parent}`).length > 0) {
                let timestamp = '';

                if (type == '1') {
                    timestamp = 'Immediately';
                } else if (type == '2') {
                    timestamp = '5 minutes';
                } else if (type == '3') {
                    timestamp = '10 minutes';
                } else if (type == '4') {
                    timestamp = 'one day';
                } else if (type == '5') {
                    timestamp = `Before delay ${hour} hour ${minute} minute`;
                }

                $(`.opt-2-${parent}`).text(timestamp)
                $('#manage-order-time-for-add-task').val(type)
                $(`.opt-2-${parent}`).attr('data-selchild', type);
            }
            
            $('.selectable-inner-2').css('background', '#fff');
            $('.status-dropdown-menu-inner-2').addClass('auto-hide');
            $(this).css('background', selectedColorBg);

            if (((hour == '' ||  minute == '') || (isNaN(hour) || isNaN(minute))) && type == '5') {
                $('.dropdown-menu-inner-2-sub-overlay').removeClass('d-none');
                return false;
            } else {
                $('.dropdown-menu-inner-2-sub-overlay').addClass('d-none');
            }

            if (!($(event.target).hasClass('change-stage-hour') || $(event.target).hasClass('change-stage-minute'))) {
                $('.dropdown-menu-inner-2-sub').hide();
            }
        });

        $(document).on('click', '.status-dropdown-toggle-for-cs', function() {
            var isHidden = $(this).parents(".status-dropdown-for-cs").children(
                ".status-dropdown-menu-for-cs").is(":hidden");
            $(".status-dropdown-for-cs .status-dropdown-menu-for-cs").hide();
            $(".status-dropdown-for-cs .status-dropdown-toggle-for-cs").removeClass("active");

            if (isHidden) {
                $(this).parents(".status-dropdown-for-cs").children(".status-dropdown-menu-for-cs")
                    .toggle()
                    .parents(".status-dropdown-for-cs")
                    .children(".status-dropdown-toggle-for-cs").addClass("active");
            }
        });

        $(document).on('click', '.status-dropdown-menu-for-cs li', function(e) {

            var bgColor = rgbToHex($(this).css("background-color"));
            var text = $(this).text();
            var thisTime = $(this).attr('data-time');
            var thisSid = $(this).attr('data-sid');
            var thisTtype = $(this).attr('data-ttype');
            var thisMtype = $(this).attr('data-mtype');
            var thisColor = $(this).attr('data-color');

            var dropdownToggle = $(this).closest(".status-dropdown-for-cs").find(".status-dropdown-toggle-for-cs");
            var dropdownToggleText = $(this).closest(".status-dropdown-for-cs").find(".status-dropdown-toggle-for-cs");

            dropdownToggleText.html(`
                    <span> ${text} </span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                        <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                    </svg>
            `);

            dropdownToggle.css("background-color", bgColor);
            dropdownToggle.css("color", generateTextColor(bgColor));

            $('#choosenColor').val(thisColor);

            if ($(this).hasClass('selectable-for-cs')) {
                $('#manage-order-status-for-change-lead-stage').val(thisSid);
            }

            if (!$(e.target).hasClass('no-btn')) {
                $(this).parent().hide();
                dropdownToggle.removeClass("active");
                $('.status-dropdown-menu-for-cs').css('display', 'none');
            }

        });

        $('#putOnCron').validate({
            rules: {
                change_stage_hour: {
                    digits: true,
                    min: 0,
                    max: 720
                },
                change_stage_minute: {
                    digits: true,
                    min: 0,
                    max: 60
                },
                cltime: {
                    required: true
                },
                clstatus: {
                    required: true
                }
            },
            messages: {
                change_stage_hour: {
                    digits: "Only digits allowed.",
                    min: "Minimum 0 hour allowed.",
                    max: "Maximum 720 hours allowed."
                },
                change_stage_minute: {
                    digits: "Only digits allowed.",
                    min: "Minimum 0 minute allowed.",
                    max: "Maximum 60 minutes allowed."
                },
                cltime: {
                    required: "Select time period."
                },
                clstatus: {
                    required: "Select a status."
                }
            },
            errorPlacement: function(error, element) {
                if ($(element).hasClass('change-stage-minute')) {
                    $('#at-type-error').text(error.text());
                    $('#change-stage-minute').css('border-color', '#ff0000');
                } else if ($(element).hasClass('change-stage-hour')) {
                    $('#at-type-error').text(error.text());
                    $('#change-stage-hour').css('border-color', '#ff0000');
                } else {
                    $('#cl-status-error').text('');
                    $('#cl-type-error').text('');
                    $('#change-stage-hour').css('border-color', '#000');
                    $('#change-stage-minute').css('border-color', '#000');
                }

            },
            submitHandler: function(form, event) {
                event.preventDefault();

                if ($(form).valid()) {
                    let formData = {};
                    let thisData = $(form).serializeArray();

                    thisData.forEach(element => {
                        formData[element.name] = element.value;
                    });


                    if ($(triggerBlock).length > 0 && $(triggerBlock).parent().parent().parent().hasAttr('data-mainstatus')) {
                        let input = `<div class="inp-groups" > <input type="hidden" class="trigger-saver-input" data-type="2" name="statuschange[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][status]" value="${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}" />
                            <input type="hidden" data-type="2" class="trigger-saver-input-maintype" name="statuschange[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][maintype]" value="${formData.cltype}" />
                            <input type="hidden" data-type="2" class="trigger-saver-input-timetype" name="statuschange[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][timetype]" value="${formData.cltime}" />
                            <input type="hidden" data-type="2" class="trigger-saver-input-hour" name="statuschange[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][hour]" value="${formData.change_stage_hour}" />
                            <input type="hidden" data-type="2" class="trigger-saver-input-minute" name="statuschange[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][minute]" value="${formData.change_stage_minute}" />
                            <input type="hidden" data-type="2" class="trigger-saver-input-next-status" name="statuschange[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][nextstatus]" value="${formData.clstatus}" />
                            <input type="hidden" data-type="2" class="trigger-saver-input-sequence" name="statuschange[${$(triggerBlock).parent().parent().parent().attr('data-mainstatus')}][${$(triggerBlock).parent().index()}][sequence]" value="${$(triggerBlock).parent().index()}" />
                            </div> `;

                        let statusName = 'status';
                        let statusColor = '#000';

                        if (allStatusesObj.length > 0) {
                            allStatusesObj.forEach(element => {
                                if (element.id == formData.clstatus) {
                                    statusName = element.name;
                                    statusColor = element.color;
                                }                                
                            });
                        }

                        $(triggerBlock).removeClass('opener');
                        $(triggerBlock).addClass('trigger-change-order-status');
                        $(triggerBlock).addClass('bg-light-grey');
                        $(triggerBlock).html(getTriggerTypes(2, formData.cltype, {
                            bg: statusColor,
                            color : generateTextColor(statusColor),
                            status : statusName,
                            time : formData.cltime,
                            hour : formData.change_stage_hour,
                            minute : formData.change_stage_minute
                        },
                        input));

                        $('#lead-stage').modal('hide');
                    }
                }

                return false;
            }
        });
        /** Change Order Status **/

        var getTypes = (type, hour, minute) => {
            if (type == 1) {
                return '';
            } else if (type == 2) {
                return ' after 5 minutes';
            } else if (type == 3) {
                return ' after 10 minutes';
            } else if (type == 4) {
                return ' after one day';
            } else if (type == 5) {
                return ` after ${hour} hour and ${minute} minute`;
            } else {
                return '';
            }
        }

        function getTriggerTypes(mainType, timeType, data, input) {
            let type = '<div class="d-flex flex-row portlet-header">';

            if (mainType == 1) {
                type += `<img src="${appUrl + '/assets/images/completed.png'}" class="width-35" /><div class="w-100"><div class="f-12 text-start trigger-box-label-timetype">`;
                if (timeType == 1) {
                    type += `After moved to this status`
                } else if (timeType == 2) {
                    type += `After created in this status`
                } else if (timeType == 3) {
                    type += `After moved or created in this status`
                }

                type += `${getTypes(data.time, data.hour, data.minute)} 
                    </div>
                        <div class="text-start">
                            <span class="f-12"> <strong>Task:</strong> <span class="trigger-box-label-task-description"> ${data.description.length > 18 ? (data.description.substring(0, 18) + '...') : data.description} </span> </span>
                        </div>${input}
                    </div>
                 </div>`;
            } else if (mainType == 2) {
                type += `<img src="${appUrl + '/assets/images/edit.png'}" class="width-35" /><div class="w-100"><div class="f-12 text-start trigger-box-label-timetype">`;
                if (timeType == 1) {
                    type += `After moved to this status`
                } else if (timeType == 2) {
                    type += `After created in this status`
                } else if (timeType == 3) {
                    type += `After moved or created in this status`
                }

                type += `${getTypes(data.time, data.hour, data.minute)} 
                    </div> 
                        <div class="text-start"> <strong class="f-12">Change status:</strong> 
                            <span class="status-lbl f-10 trigger-box-label-task-ns" style="background: ${data.bg};color:${data.color};text-transform:uppercase;"> ${data.status} </span> 
                        </div>${input}
                    </div>
                </div>`;
            } else if (mainType == 3) {
                type += `<img src="${appUrl + '/assets/images/completed.png'}" class="width-35" /><div class="w-100"><div class="f-12 text-start trigger-box-label-timetype">`;
                if (timeType == 1) {
                    type += `After moved to this status`
                } else if (timeType == 2) {
                    type += `After created in this status`
                } else if (timeType == 3) {
                    type += `After moved or created in this status`
                }

                type += `${getTypes(data.time, data.hour, data.minute)} 
                </div>
                    <div class="text-start"> 
                        <span class="f-12"> <strong>Change order's user:</strong> ${data.user} </span> 
                        </div>${input}
                    </div>
                </div>`;
            }

            return `${type}`;
        }

        $(".drag-area").sortable({
            connectWith: ".drag-area",
            handle: ".portlet-header",
            cancel: ".portlet-toggle",
            placeholder: "portlet-placeholder ui-corner-all",
            stop: function (event, ui) {
                
            },
            receive: function(event, ui) {
                
                if ($(ui.item).next().hasClass('opener')) {
                    $(ui.item).next().remove()
                } else if ($(ui.item).prev().hasClass('opener')) {
                    $(ui.item).prev().remove()
                }

                if ($(ui.item).parent().parent().parent().hasAttr('data-mainstatus') && isNumeric($(ui.item).parent().parent().parent().hasAttr('data-mainstatus'))) {
                    let thisStatus = $(ui.item).parent().parent().parent().attr('data-mainstatus');
                    let index = $(ui.item).parent().index();

                    if ($(ui.item).find('.trigger-saver-input').length > 0) {
                        let prefix = '';
                        let taskType = $(ui.item).find('.trigger-saver-input').attr('data-type');

                        if (taskType == '1') {
                            prefix = 'task';
                        } else if (taskType == '2') {
                            prefix = 'statuschange';
                        } else if (taskType == '3') {
                            prefix = 'userchange';
                        }

                        let triggerBtn = `
                        <div class="card-body text-center d-flex align-items-center justify-content-center custom-p cursor-pointer opener min-max-height" data-title="${$(ui.item).attr('data-title')}"  data-sid="${$(ui.item).attr('data-sid')}">
                            <i class="fa fa-plus-circle"></i> Add trigger
                        </div>`;

                        $(ui.item).find('.trigger-saver-input').attr('name', `${prefix}[${thisStatus}][${index}][status]`);
                        $(ui.item).find('.trigger-saver-input').val(thisStatus);

                        $(ui.item).find('.trigger-saver-input-maintype').attr('name', `${prefix}[${thisStatus}][${index}][maintype]`);
                        $(ui.item).find('.trigger-saver-input-maintype').val($(ui.item).find('.trigger-saver-input-maintype').val());

                        $(ui.item).find('.trigger-saver-input-timetype').attr('name', `${prefix}[${thisStatus}][${index}][timetype]`);
                        $(ui.item).find('.trigger-saver-input-timetype').val($(ui.item).find('.trigger-saver-input-timetype').val());

                        $(ui.item).find('.trigger-saver-input-hour').attr('name', `${prefix}[${thisStatus}][${index}][hour]`);
                        $(ui.item).find('.trigger-saver-input-hour').val($(ui.item).find('.trigger-saver-input-hour').val());

                        $(ui.item).find('.trigger-saver-input-minute').attr('name', `${prefix}[${thisStatus}][${index}][minute]`);
                        $(ui.item).find('.trigger-saver-input-minute').val($(ui.item).find('.trigger-saver-input-minute').val());
                        
                        $(ui.item).find('.trigger-saver-input-sequence').attr('name', `${prefix}[${thisStatus}][${index}][sequence]`);
                        $(ui.item).find('.trigger-saver-input-sequence').val(index);

                        if ($(ui.item).find('.trigger-saver-input-edit_id').length > 0) {
                            $(ui.item).find('.trigger-saver-input-edit_id').attr('name', `${prefix}[${thisStatus}][${index}][edit_id]`);
                            $(ui.item).find('.trigger-saver-input-edit_id').val($(ui.item).find('.trigger-saver-input-edit_id').val());
                        }

                        if (taskType == '1') {
                            $(ui.item).find('.trigger-saver-input-desc').attr('name', `${prefix}[${thisStatus}][${index}][desc]`);
                            $(ui.item).find('.trigger-saver-input-desc').val($(ui.item).find('.trigger-saver-input-desc').val());
                        } else if (taskType == '2') {
                            $(ui.item).find('.trigger-saver-input-next-status').attr('name', `${prefix}[${thisStatus}][${index}][nextstatus]`);
                            $(ui.item).find('.trigger-saver-input-next-status').val($(ui.item).find('.trigger-saver-input-next-status').val());
                        }
                        
                    }
                }
            }
        });

        $(".portlet").find(".portlet-header").addClass("ui-corner-all")


















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

            $('#manage-status-id').val(null);
        }

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
                    dropdownParent: $('#manage-next-possible-status'),
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
                dropdownParent: $('#manage-next-possible-status'),
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
                            $('#manage-next-possible-status').modal('hide');
                            resetModal();
                        } else if (response.status == false) {
                            // Swal.fire('', Object.values(response.messages).flat().toString(), 'error');
                            Swal.fire('', 'Something went wrong. Please try again.', 'error');                            
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