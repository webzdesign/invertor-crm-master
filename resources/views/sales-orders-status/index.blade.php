@extends('layouts.master')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
<style>
    .card-header {
        background: #ffffff;
        color: #000;
        padding: 6px 15px;
        text-align: center;
        display: grid;
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
        margin: 0.1rem;
        min-width: 270px;
        height: calc(100vh - 180px);
    }

    .drag-area {
        height: 100%;
    }

    .card-body-main {
        padding: 0.6rem 0.2rem;
    }

    .card-body-main {
        padding: 0.6rem 0.2rem;
    }

    .draggable-card {
        cursor: pointer;
        z-index: 9;
    }


    hr {
        margin-top: 1rem;
        margin-bottom: 1rem;
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .color-blue {
        color: #0057a9;
        cursor: pointer;
    }

    .no-border {
        border: none;
    }

    .no-m {
        margin-bottom: 0rem !important;
    }

    .font-13 {
        font-size: 13px;
    }

    .portlet-placeholder {
        background: #ececec;
        height: 60px;
        box-shadow: inset 0 10px 15px -3px #0000001a, 0 4px 6px -4px #0000001a;
    }

    .trigger-btn {
        font-size: 12px;
        width: fit-content;
        height: fit-content;
        padding: 0;
        border: none;
        background: transparent;
        position: relative;
        top: 18px;
        right: 3px;
    }

    .box {
        padding: 12px 38px;
        display: flex;
        flex-direction: column;
        align-items: center;
        border-radius: 10px;
        cursor: pointer;
        border: 2px solid #0a141e;
    }

    .box img {
        height: 40px;
        width: 40px;
    }

    .box div {
        margin-top: 10px;
    }

    .color-blue {
        color: #0057a9;
        cursor: pointer;
    }

    .select2-selection__clear {
        display: none !important;
    }

    .filterColumn,
    .select2-selection__arrow {
        display: none;
    }

    .no-border {
        border: none;
    }

    .modal-padding {
        padding: 10px 14px;
    }

    .status-opener {
        background: #fff;
        border: 1px solid #ccc;
        height: 20px;
        width: 20px;
        font-size: 13px;
        border-radius: 2px;
    }

    .status-label {
        padding: 0 6px;
        border-radius: 4px;
    }

    .status-opener i {
        position: relative;
        top: 1px;
        left: 1px;
    }

    .status-opener:hover {
        background: #f0f0f0;
    }

    .status-main:hover .status-opener {
        visibility: visible;
    }

    .overflowTable .odd td:nth-child(3) {
        width: 260px;
        min-width: 260px;
    }

    .overflowTable .odd td:nth-child(4) {
        width: 150px;
        min-width: 150px;
    }

    .overflowTable .odd td:nth-child(2) {
        width: 150px;
        min-width: 150px;
    }

    .status-modal {
        position: absolute;
        top: 0;
        left: 0;
        box-shadow: 0 5px 10px 0 #0000001a;
        box-sizing: border-box;
        padding: 12px;
        border: 1px solid #e8eaeb;
        width: 100%;
        background: #fff;
        z-index: 9;
    }

    .status-dropdown-toggle,
    .status-dropdown-toggle-inner {
        border: 1px solid #92989b66;
        width: 100%;
        text-align: left;
        border-radius: 3px;
        padding: 4px 6px;
        background: #fff;
    }

    .status-dropdown-menu,
    .status-dropdown-menu-inner,
    .status-dropdown-menu-inner-ul {
        border: 1px solid #92989b66;
        width: 100%;
        text-align: left;
        border-radius: 3px;
        background: #fff;
        position: absolute;
        top: 0;
        z-index: 1;
        max-height: 185px;
        overflow: auto;
    }

    .status-dropdown-menu::-webkit-scrollbar-track,
    .status-dropdown-menu-inner::-webkit-scrollbar-track {
        background-color: #ffffff;
    }

    .status-dropdown-menu::-webkit-scrollbar,
    .status-dropdown-menu-inner::-webkit-scrollbar {
        width: 6px;
        background-color: #ffffff;
    }

    .status-dropdown-menu::-webkit-scrollbar-thumb,
    .status-dropdown-menu-inner::-webkit-scrollbar-thumb {
        background-color: #c7c7c7;
    }

    .status-dropdown-menu li,
    .status-dropdown-menu-inner li,
    .status-dropdown-menu-inner-ul li {
        padding: 3px 6px;
        cursor: pointer;
    }

    .dataTables_wrapper .col-sm-12 {
        overflow: inherit;
    }

    .btn-primary:disabled:hover {
        background: #E9EAED !important;
    }

    .-z-1 {
        z-index: -1;
    }

    div.dt-processing>div:last-child {
        display: none;
    }

    div.dt-processing {
        margin-left: 0;
        margin-top: 0;
        border: 0;
        background: transparent;
    }

    @media (min-width:992px) {
        .status-opener {
            visibility: hidden;
        }
    }

    .no-btn {
        border: none;
        background: transparent;
        color: #0057a9;
    }

    .status-dropdown-menu>li:hover,
    .status-dropdown-menu-inner>li:hover {
        background: #e8e8e8;
        color: #000;
    }

    #hour,
    #add-task-hour {
        margin-left: 10px;
        height: 28px;
        width: 52px;
    }

    #minute,
    #add-task-minute {
        width: 67px;
        margin-left: 10px;
        height: 28px;
    }

    button.status-dropdown-toggle-2 {
        background: #fff !important;
        color: #000 !important;
    }



    .status-lbl {
        padding: 4px;
        border-radius: 4px;
        margin-right: 2px;
        line-height: 1;
        display: inline-block;
    }

    .dropdown-menu-inner-sub {
        position: absolute;
        top: 8px;
        background: #fff;
        left: 7px;
        width: 260px;
        border: 1px solid #ddd;
    }

    .dropdown-menu-inner-sub li:hover {
        background: #e8e8e8!important;
        color: #000;
    }

    .zindex-1 {
        z-index: 1;
    }

    .dis-none {
        display: none;
    }
    .dropdown-menu-inner-sub-overlay{
        position: fixed;
        width: 100%;
        height: 100%;
        left: 0;
        top: 0;
        z-index: 1;
    }
    #task-desc {
        height: 100px;
    }
    .error-text {
        color: #ff00009f;
    }

    .small-btn {
        height: 27px;
        line-height: 1.5;
        padding: 0 10px;
    }
    .status-date-lbl{
        top: 50%;
        left: 50%;
        transform: translate(-50%,-50%);
    }
    .cw-status-border {
        border: 1px solid #4CAF50;
        cursor: help;
    }
</style>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
    @section('create_button')
        <div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">

            {{-- @if(!in_array(3, User::getUserRoles()))
            <a href="{{ route('sales-order-status') }}" class="btn-primary f-500 f-14 d-inline-block">
                <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="2" width="2" height="16" fill="white" />
                        <rect x="9" y="2" width="2" height="12" fill="white" />
                        <rect x="14" y="2" width="2" height="6" fill="white" />
                    </svg>
                </a>
                <a href="{{ route('sales-order-status-list') }}" class="btn-default f-500 f-14 d-inline-block">
                    <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="4" width="16" height="2" fill="black" />
                        <rect x="2" y="9" width="16" height="2" fill="black" />
                        <rect x="2" y="14" width="16" height="2" fill="black" />
                    </svg>
                </a>
            @endif --}}
            <div>
            </div>

            @permission('sales-order-status.edit')
                <a href="{{ route('sales-order-status-edit') }}" class="btn-primary f-500 f-14">
                    <i class="fa fa-flash" style="color: #ffab00;"></i> &nbsp; AUTOMATE
                </a>
            @endpermission
        </div>
    @endsection

<div class="content pb-3">

    {{-- Board --}}
    <div class="d-flex overflow-auto pb-3" id="sortable">

        @php $iteration = 0;  @endphp
        @forelse($statuses as $key => $status)
            <div class="card card-row card-secondary no-border">
                <div class="card-header" style="border-bottom: 2px solid {{ $status->color }};">
                    <h3 class="card-title">

                        {{ strtoupper($status->name) }}

                    </h3>
                </div>
                <div class="card-body-main @if($cwStatus != $status->id) drag-area @endif" data-cardparent="{{ $status->id }}">

                    @if (isset($orders[$status->id]))
                        @foreach ($orders[$status->id] as $order)
                            <div class="card card-light card-outline mb-2 draggable-card @if($cwStatus != $status->id) portlet @else cw-status-border @endif"
                                data-cardchild="{{ $order['id'] }}" data-otitle="{{ $order['order_no'] }}" >
                                <div
                                    class="card-body bg-white border-0 p-2 d-flex justify-content-between portlet-header">
                                    <div>
                                        <p class="color-blue">{{ $order['order_no'] }}</p>
                                        <p class="no-m font-13">
                                            @if($order['soldamount'])
                                                {{ Helper::currency($order['soldamount'] + $order['driveramount']) }} </p>
                                            @else
                                                {{ Helper::currency($order['amount']) }} </p>
                                            @endif
                                    </div>
                                    <div class="d-flex align-items-end flex-column">
                                        <div class="card-date f-12 c-7b">
                                            {{ \Carbon\Carbon::parse($order['date'])->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
        @empty
        @endforelse

    </div>
    {{-- Board --}}

</div>

{{-- Order details modal --}}
@include('sales-orders-status.modal.order-details')
{{-- Order details modal --}}
@endsection

@section('script')
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script src="{{ asset('assets/js/pusher.min.js') }}"></script>
<script>
    var selectedOpt = null;
    var thisWindowId = uuid();
    var selectedColorBg = '#e8e8e8';

    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    $(document).ready(function() {

        /** Pusher Code **/
        // Pusher.logToConsole = true;
        var pusher = new Pusher("{{ env('PUSHER_APP_KEY') }}", {
            cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
            encrypted: true
        });

        var channel = pusher.subscribe('card-trigger');
        channel.bind('order-status-change', function(data) {
            let shouldMove = false;

            if ('windowId' in data) {
                if (thisWindowId !== data.windowId) {
                    if ('users' in data) {
                        if (typeof data.users == 'object' && (data.users.includes({{ auth()->user()->id }}) || "{{ auth()->user()->roles->first()->id }}" == 1)) {
                            shouldMove = true;
                        }
                    } else {
                        shouldMove = true;
                    }
                }
            } else {
                shouldMove = true;
            }

        
            if (shouldMove && 'element' in data) {
                let toBeMovedAt = $(`[data-cardparent="${data.orderStatus}"]`);
                $(toBeMovedAt).append(data.element);
            } else if (shouldMove && $(`[data-cardchild="${data.orderId}"]`).length > 0 && $(
                    `[data-cardparent="${data.orderStatus}"]`).length > 0) {
                let toBeMoved = $(`[data-cardchild="${data.orderId}"]`).get(0);
                let toBeMovedAt = $(`[data-cardparent="${data.orderStatus}"]`);

                $(toBeMoved).find('.trigger-btn').attr('data-soid', data.orderStatus);

                $(`[data-cardchild="${data.orderId}"]`).remove();
                $(toBeMovedAt).append(toBeMoved)

                if (data.orderStatus == "{{ $cwStatus }}") {
                    $(toBeMoved).addClass('cw-status-border');
                }
            } else if ('orderOldStatus' in data && data.orderStatus == '1' && data.orderOldStatus == '2') {
                if ($(`[data-cardchild="${data.orderId}"]`).length > 0) {
                    $(`[data-cardchild="${data.orderId}"]`).remove();
                }
                return false;
            } else if ('removing' in data && data.removing && data.orderStatus == '1') {
                if ($(`[data-cardchild="${data.orderId}"]`).length > 0) {
                    $(`[data-cardchild="${data.orderId}"]`).remove();
                }
                return false;
            }
        });

        channel.bind('add-task-to-order', function(data) {
            toastr["info"](data.orderId, "Task added successfully")
        });

        channel.bind('change-user-for-order', function(data) {
            toastr["info"](data.orderId, "Responsible user changed for order successfully.")
        });
        /** Pusher Code **/

        $.validator.setDefaults({
            ignore: []
        });

        /** Order details and Card JS **/
        $(document).on('click', '.draggable-card', function(event) {

            let thisOrderId = $(this).attr('data-cardchild');
            let thisOrderTitle = $(this).attr('data-otitle');

            if (!($(event.target).hasClass('trigger-btn') || $(event.target).hasClass('fa-plus'))) {
                if (thisOrderId != '' && thisOrderId != null) {
                    $.ajax({
                        url: "{{ route('order-detail-in-board') }}",
                        type: "POST",
                        data: {
                            id: thisOrderId
                        },
                        success: function(response) {
                            if (response.status) {
                                $('#modal-title-1').text(thisOrderTitle);
                                $('#orderDetails').empty().html(response.view);
                                $('#order-details').modal('show');
                            }
                        }

                    });
                }
            }

        });

        $(document).on('click', '#toggle-status-trigger-list', function (event) {
            if ($('.actvt').hasClass('d-none')) {
                $('.actvt').removeClass('d-none')
                $(this).text('Show less');
            } else {
                $('.actvt').not('.show-first').addClass('d-none');
                $(this).text('Show All');
            }
        })

        $(document).on('click', '#toggle-task-trigger-list', function (event) {
            if ($('.actvt-at').hasClass('d-none')) {
                $('.actvt-at').removeClass('d-none')
                $(this).text('Show less');
            } else {
                $('.actvt-at').not('.show-first-at').addClass('d-none');
                $(this).text('Show All');
            }
        })

        $(document).on('click', '#toggle-change-user-trigger-list', function (event) {
            if ($('.actvt-cu').hasClass('d-none')) {
                $('.actvt-cu').removeClass('d-none')
                $(this).text('Show less');
            } else {
                $('.actvt-cu').not('.show-first-cu').addClass('d-none');
                $(this).text('Show All');
            }
        })

        $(document).on('click', '#toggle-history', function (event) {
            if ($('.hist').hasClass('d-none')) {
                $('.hist').removeClass('d-none')
                $(this).text('Show less');
            } else {
                $('.hist').not('.show-first-history').addClass('d-none');
                $(this).text('Show All');
            }
        })
        /** Order details and Card JS **/

        /** Change lead stage JS **/
        var getTypes = (type) => {
            if (type == '1') {
                return 'Immediatly';
            } else if (type == '2') {
                return '5 minutes';
            } else if (type == '3') {
                return '10 minutes';
            } else if (type == '4') {
                return 'One day';
            } else if (type == '5') {
                return '';
            } else {
                return '';
            }
        }

        $('#putOnCron').validate({
            rules: {
                hour: {
                    digits: true,
                    min: 0,
                    max: 720
                },
                minute: {
                    digits: true,
                    min: 0,
                    max: 60
                }
            },
            messages: {
                hour: {
                    digits: "Only digits allowed.",
                    min: "Minimum 0 hour allowed.",
                    max: "Maximum 720 hours allowed."
                },
                minute: {
                    digits: "Only digits allowed.",
                    min: "Minimum 0 minute allowed.",
                    max: "Maximum 60 minutes allowed."
                }
            },
            errorPlacement: function(error, element) {
                if ($(element).hasClass('minute')) {
                    $('#status-dropdown-2-error').text(error.text());
                    $('#minute').css('border-color', '#ff0000');
                } else if ($(element).hasClass('hour')) {
                    $('#status-dropdown-2-error').text(error.text());
                    $('#hour').css('border-color', '#ff0000');
                } else {
                    $('#status-dropdown-2-error').text('');
                    $('#hour').css('border-color', '#000');
                    $('#minute').css('border-color', '#000');
                }
            },
            submitHandler: function(form, event) {
                event.preventDefault();

                if ($('#putOnCron').valid()) {
                    $.ajax({
                        url: "{{ route('put-order-on-cron') }}",
                        type: "POST",
                        data: $('#putOnCron').serializeArray(),
                        beforeSend: function() {
                            $('body').find('.LoaderSec').removeClass('d-none');
                            $('button[type="submit"]').attr('disabled', true);
                        },
                        success: function(response) {
                            if (response.status) {
                                $('#lead-stage').modal('hide');
                                Swal.fire('', response.message, 'success');
                            } else {
                                Swal.fire('', response.message, 'error');
                            }
                        },
                        complete: function() {
                            $('body').find('.LoaderSec').addClass('d-none');
                            $('button[type="submit"]').attr('disabled', false);
                        }
                    });
                } else {
                    return false;
                }
            }
        });

        $(document).on('click', '#lead-stage-btn', function() {
            let oId = $('#manage-order-id').val();
            let Title = $('#modal-title').text();

            if (oId != '' && oId != null) {
                $.ajax({
                    url: "{{ route('sales-order-next-status') }}",
                    type: 'POST',
                    data: {
                        id: oId
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        $('#trigger').modal('hide');
                        $('#lead-stage').modal('show');
                        $('#lead-stage').find('#modal-title-lead-stage').text(Title);
                        $('#manage-order-id-for-change-lead-stage').val(oId);
                        $('#stage-container').html(response.view);

                        if (Object.values(response.data).length > 0) {
                            $('#manage-order-status-for-change-lead-stage').val(Object.keys(
                                response.data)[0]);
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
                        $(".status-dropdown .status-dropdown-menu").hide();
                    }
                });
            }

        });

        $(document).on('keyup', '#minute', function() {
            if (isNaN($('#hour').val())) {
                $('#status-dropdown-2-error').text('Only digits allowed.');
            } else if ($('#hour').val() > 60) {
                $('#status-dropdown-2-error').text('Maximum 720 hours allowed.');
            } else if ($('#hour').val() < 0) {
                $('#status-dropdown-2-error').text('Minimum 0 hour allowed.');
            } else {
                $('#status-dropdown-2-error').text('');
            }

            $(this).css('border-color', '#000');
        })

        $(document).on('keyup', '#hour', function() {
            if (isNaN($('#minute').val())) {
                $('#status-dropdown-2-error').text('Only digits allowed.');
            } else if ($('#minute').val() > 60) {
                $('#status-dropdown-2-error').text('Maximum 60 minutes allowed.');
            } else if ($('#minute').val() < 0) {
                $('#status-dropdown-2-error').text('Minimum 0 minute allowed.');
            } else {
                $('#status-dropdown-2-error').text('');
            }

            $(this).css('border-color', '#000');
        })

        $(document).on('keyup', '#add-task-hour', function() {

            if (isNaN($('#add-task-minute').val())) {
                $('#at-type-error').text('Only digits allowed.');
            } else if ($('#add-task-minute').val() > 60) {
                $('#at-type-error').text('Maximum 60 minutes allowed.');
            } else if ($('#add-task-minute').val() < 0) {
                $('#at-type-error').text('Minimum 0 minute allowed.');
            } else {
                $('#at-type-error').text('');
            }

            $(this).css('border-color', '#000');
        })

        $(document).on('keyup', '#add-task-minute', function() {

            if (isNaN($('#add-task-hour').val())) {
                $('#at-type-error').text('Only digits allowed.');
            } else if ($('#add-task-hour').val() > 60) {
                $('#at-type-error').text('Maximum 720 hours allowed.');
            } else if ($('#add-task-hour').val() < 0) {
                $('#at-type-error').text('Minimum 0 hour allowed.');
            } else {
                $('#at-type-error').text('');
            }

            $(this).css('border-color', '#000');
        })

        $(document).on('hidden.bs.modal', '#lead-stage', function(event) {
            if (event.namespace == 'bs.modal') {
                $('#stage-container').empty();
                $('.status-dropdown-toggle-2').text('Immediatly');
                $('#manage-order-status-for-change-lead-stage').val(null);
                $('#manage-order-time-for-change-lead-stage').val('1');
                $('#manage-order-id-for-change-lead-stage').val(null);
                $('#hour').val(null).css('border-color', '#000');
                $('#minute').val(null).css('border-color', '#000');
                $('#status-dropdown-2-error').text('');
                $('.hideable').show();
                $('.sel-time').css('background', '#fff');
            }
        });
        /** Change lead stage JS **/

        /** Common JS for custom select modal **/
        function bindClickToHide(selector) {
            $(selector).on("click", function(event) {
                event.preventDefault();
                $(this).parent().fadeOut();
            });
        }

        function bindClickToHideModal(selector) {
            $(selector).on("click", function(event) {
                event.preventDefault();
                $(this).parent().fadeOut();
            });
        }
        /** Common JS for custom select modal **/

        /** Common JS for custom select **/
        $(document).on('click', '.dropdown-toggle', function() {
            var isHidden = $(this).parents(".button-dropdown").children(".dropdown-menu").is(":hidden");
            $(".button-dropdown .dropdown-menu").hide();
            $(".button-dropdown .dropdown-toggle").removeClass("active");

            if (isHidden) {
                $(this).parents(".button-dropdown").children(".dropdown-menu").toggle()
                    .parents(".button-dropdown")
                    .children(".dropdown-toggle").addClass("active");
            }
        });

        $(document).on('click', function(event) {
            var target = $(event.target);

            let hour = $('#add-task-hour').val();
            let minute = $('#add-task-minute').val();

            if (!target.parents().hasClass("button-dropdown")) {
                $(".button-dropdown .dropdown-menu").hide();
                $(".button-dropdown .dropdown-toggle").removeClass("active");
            }

            if (!target.parents().hasClass("status-dropdown")) {
                if ($('.can-hide-time-picker').css('display') == 'none') {
                    $(".status-dropdown .status-dropdown-menu").hide();
                    $(".status-dropdown .status-dropdown-toggle").removeClass("active");
                }
            }
            
            if (!target.parents().hasClass("status-dropdown-inner") && !$('.status-dropdown-menu-inner').hasClass('auto-hide') && $('.dropdown-menu-inner-sub').css('display') == 'none') {
                $(".status-dropdown-inner .status-dropdown-menu-inner").hide();
                $(".status-dropdown-inner .status-dropdown-toggle-inner").removeClass("active");
            }
        });

        $(document).on('click', '.status-dropdown-toggle', function() {
            var isHidden = $(this).parents(".status-dropdown").children(".status-dropdown-menu").is(
                ":hidden");
            $(".status-dropdown .status-dropdown-menu").hide();
            $(".status-dropdown .status-dropdown-toggle").removeClass("active");

            if (isHidden) {
                $(this).parents(".status-dropdown").children(".status-dropdown-menu").toggle()
                    .parents(".status-dropdown")
                    .children(".status-dropdown-toggle").addClass("active");
            }
        });

        $(document).on('click', '.status-dropdown-menu li', function(e) {

            if (!($(e.target).hasClass('hour') || $(e.target).hasClass('minute'))) {
                var bgColor = rgbToHex($(this).css("background-color"));
                var text = $(this).text();
                var thisTime = $(this).attr('data-time');
                var thisSid = $(this).data('sid');

                var dropdownToggle = $(this).closest(".status-dropdown").find(
                ".status-dropdown-toggle");
                var dropdownToggleText = $(this).closest(".status-dropdown").find(
                    ".status-dropdown-toggle");

                dropdownToggleText.html(`
                <span> ${text} </span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                    <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                </svg>
                `);

                dropdownToggle.css("background-color", bgColor);
                dropdownToggle.css("color", generateTextColor(bgColor));

                if ($(this).hasClass('sel-time')) {
                    if ($(this).attr('data-time') !== '5') {
                        $(this).parent().hide();
                        dropdownToggle.removeClass("active");
                    }
                } else {
                    $(this).parent().hide();
                    dropdownToggle.removeClass("active");
                }

                if (!$(this).hasClass('selectable-2')) {
                    $('.sel-time').css('background', '#fff');
                    $(this).css('background', selectedColorBg);
                }

                $(this).parent().parent().parent().find('.status-action-btn').find('.status-save-btn')
                    .removeAttr("disabled");

                if ($(this).hasClass('selectable')) {
                    $('#manage-order-status-for-change-lead-stage').val(thisSid);
                }

                if ($(this).hasClass('selectable-2')) {
                    $('#manage-order-status-for-add-task').val(thisSid);
                    $('#manage-order-status-for-change-lead-stage').val(thisSid);
                }

                if ($(this).hasAttr('data-time')) {
                    $('#manage-order-time-for-change-lead-stage').val(thisTime);
                }
            }

        });


        $(document).on('click', '.hide-dropdown', function() {
            $('.dropdown-menu').hide();
        });
        /** Common JS for custom select **/


        /** Add Task JS **/
        $(document).on('click', '.dropdown-toggle-inner', function() {
            var isHidden = $(this).parents(".button-dropdown").children(".dropdown-menu").is(":hidden");
            $(".button-dropdown .dropdown-menu").hide();
            $(".button-dropdown .dropdown-toggle").removeClass("active");

            if (isHidden) {
                $(this).parents(".button-dropdown").children(".dropdown-menu").toggle()
                    .parents(".button-dropdown")
                    .children(".dropdown-toggle").addClass("active");
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

        $(document).on('click', '#add-task-btn', function() {
            let oId = $('#manage-order-id').val();
            let Title = $('#modal-title').text();

            if (oId != '' && oId != null) {
                $.ajax({
                    url: "{{ route('sales-order-next-status-for-add-task') }}",
                    type: 'POST',
                    data: {
                        id: oId
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        $('#trigger').modal('hide');
                        $('#add-task').modal('show');
                        $('#add-task').find('#modal-title-add-task').text(Title);
                        $('#manage-order-id-for-add-task').val(oId);
                        $('#add-task-status-container').html(response.view);

                        if (Object.values(response.data).length > 0) {
                            $('#manage-order-status-for-add-task').val(Object.keys(response.data)[0]);
                        } else {
                            $('.hideable-add-task').hide();
                        }
                    },
                    complete: function() {
                        $('body').find('.LoaderSec').addClass('d-none');
                        $(".status-dropdown .status-dropdown-menu").hide();
                    }
                });
            }

        });

        $(document).on('click', '.no-btn', function () {
            let top = $(this).attr('data-top');
            let left = $(this).attr('data-left');
            let parent = $(this).attr('data-parent');

            $('.dropdown-menu-inner-sub').show();
            $('.dropdown-menu-inner-sub').css({
                'left' : `${left}px`,
                'top' : `${top}px`
            });

            $('.dropdown-menu-inner-sub').attr('data-parenttype', parent);
        })

        $(document).on('click', '.sel-time', function (e) {
            let time = $(this).attr('data-time');

            if ($(this).attr('data-time') == '5') {

                let hour = $(this).find('#hour').val();
                let minute = $(this).find('#minute').val();

                $('.status-dropdown-toggle-2').find('span').text(`${hour} hours ${minute} minutes`);

                if (((hour == '' ||  minute == '') || (isNaN(hour) || isNaN(minute)))) {

                    $('.dropdown-menu-inner-sub-overlay').removeClass('d-none');
                    return false;
                } else {
                    if (!($(event.target).hasClass('hour') || $(event.target).hasClass('minute'))) {
                        $('.can-hide-time-picker').hide();
                    }
                    $('.dropdown-menu-inner-sub-overlay').addClass('d-none');
                }

            } else {
                $('.status-dropdown-toggle-2').find('span').text(getTypes(time));
                $('.dropdown-menu-inner-sub-overlay').addClass('d-none');
            }
        });

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
                    timestamp = 'One day';
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
                $('#add-task-status-container').empty();


                $('#manage-order-id-for-add-task').val(null);
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
                $('#task-desc').css('height', '100px');
                $('#at-type-error').text('')
                $('#at-status-error').text('')

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
                attype: {
                    required: true
                },
                atstatus: {
                    required: true
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
                attype: {
                    required: "Select trigger time."
                },
                atstatus: {
                    required: "Select trigger status."
                }
            },
            errorPlacement: function(error, element) {
                if ($(element).hasClass('add-task-minute')) {
                    $('#at-type-error').text(error.text());
                    $('#add-task-minute').css('border-color', '#ff0000');
                } else if ($(element).hasClass('add-task-hour')) {
                    $('#at-type-error').text(error.text());
                    $('#add-task-hour').css('border-color', '#ff0000');
                } else if ($(element).hasClass('manage-order-type-for-add-task')) {
                    $('#at-type-error').text(error.text());
                } else if ($(element).hasClass('manage-order-status-for-add-task')) {
                    $('#at-status-error').text(error.text());
                } else {
                    $('#at-status-error').text('');
                    $('#at-type-error').text('');
                    $('#status-dropdown-2-error').text('');
                    $('#add-task-hour').css('border-color', '#000');
                    $('#add-task-minute').css('border-color', '#000');
                }
            },
            submitHandler: function(form, event) {
                event.preventDefault();

                if ($('#addToTask').valid()) {
                    $.ajax({
                        url: "{{ route('put-task-for-order') }}",
                        type: "POST",
                        data: $('#addToTask').serializeArray(),
                        beforeSend: function() {
                            $('body').find('.LoaderSec').removeClass('d-none');
                            $('button[type="submit"]').attr('disabled', true);
                        },
                        success: function(response) {
                            if (response.status) {
                                $('#add-task').modal('hide');
                                Swal.fire('', response.message, 'success');
                            } else {
                                Swal.fire('', response.message, 'error');
                                // location.reload();
                            }
                        },
                        complete: function() {
                            $('body').find('.LoaderSec').addClass('d-none');
                            $('button[type="submit"]').attr('disabled', false);
                        }
                    });
                } else {
                    return false;
                }
            }
        });

        $(document).on('click', '.remove-task', function () {
            let id = $(this).attr('data-tid');
            let order = $(this).attr('data-oid');
            let element = $(this).parent().parent().parent();

            if (id !== '' && order !== '') {
                Swal.fire({
                    title: 'Are you sure want to delete task?',
                    text: "As that can be undone by doing reverse.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('remove-task') }}",
                            type: "POST",
                            data: {
                                id: id,
                                order: order
                            },
                            beforeSend: function () {
                                $('body').find('.LoaderSec').removeClass('d-none');
                            },
                            success: function (response) {
                                if (response.status) {
                                    Swal.fire('', response.message, 'success');
                                    $(element).remove();

                                    if (response.count > 0) {
                                        if (response.count <= 3) {
                                            $('#toggle-task-trigger-list').text('Show All');
                                        }

                                        if (!($('.actvt-at:eq(0)').hasClass('show-first-at') && $('.actvt-at:eq(1)').hasClass('show-first-at') && $('.actvt-at:eq(2)').hasClass('show-first-at'))) {
                                            if ($('.actvt-at').not('.show-first-at').first().hasClass('d-none')) {
                                                $('.actvt-at').not('.show-first-at').first().removeClass('d-none').addClass('show-first-at');
                                            } else if ($('.actvt-at').not('.show-first-at').first().length > 0) {
                                                $('.actvt-at').not('.show-first-at').first().addClass('show-first-at');
                                            }
                                        }
                                    } else {
                                        $('.task-trigger-activity-row').html(`<div class="activity py-2 f-13 border-bottom">No Activity to Show</div>`);
                                        $('#toggle-task-trigger-list').remove();
                                    }
                                } else {
                                    Swal.fire('', response.message, 'error');
                                }
                            },
                            complete: function () {
                                $('body').find('.LoaderSec').addClass('d-none');
                            }
                        });
                    }
                });
            }
        });

        $(document).on('click', '.edit-task', function () {
            $(this).addClass('d-none');
            $(this).parent().prev().find('.completion-content').attr('contenteditable', true)
            $(this).parent().prev().find('.completion-content').focus();
            $(this).parent().parent().next().removeClass('d-none');
        })

        $(document).on('click', '.hide-complete-task-textarea', function () {
            $(this).parent().addClass('d-none')
            $(this).parent().prev().find('.edit-task').removeClass('d-none');
            $(this).parent().prev().find('.completion-content').attr('contenteditable', false);
        })

        $(document).on('click', '.save-complete-task-textarea', function () {
            let description = $(this).parent().prev().find('.completion-content').text();
            let task = $(this).attr('data-taskid');
            let errorElement = $(this).parent().prev().find('.completion-content').next();
            let that = this;

            if (description.trim().length > 0 && task !== '') {
                if ($(this).parent().prev().find('.completion-content').next().hasClass('this-error')) {
                    $(this).parent().prev().find('.completion-content').next().text('');
                }

                $.ajax({
                    url: "{{ route('save-completion-description-for-task') }}",
                    type: "POST",
                    data: {
                        id: task,
                        text: description
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {

                        if (response.status) {
                            $(that).parent().addClass('d-none')
                            $(that).parent().prev().find('.edit-task').removeClass('d-none');
                            $(that).parent().prev().find('.completion-content').attr('contenteditable', false);
                        } else {
                            errorElement.text(response.message);
                        }

                    },
                    complete: function() {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });

            } else {
                if ($(this).parent().prev().find('.completion-content').next().hasClass('this-error')) {
                    $(this).parent().prev().find('.completion-content').next().text(`Description field can't be saved empty.`);
                }
            }

        })
        /** Add Task JS **/

        /** Change User **/
        $(document).on('click', '#change-user-btn', function () {
            let osId = $('#manage-order-status-id').val();
            let oId = $('#manage-order-id').val();
            let Title = $('#modal-title').text();

            if (oId != '' && oId != null) {
                $.ajax({
                    url: "{{ route('sales-order-responsible-user') }}",
                    type: 'POST',
                    data: {
                        id: oId,
                        status: osId
                    },
                    beforeSend: function() {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function(response) {
                        $('#trigger').modal('hide');
                        $('#change-user').modal('show');
                        $('#change-user').find('#modal-title-change-user').text(Title);
                        $('#manage-order-id-for-change-user').val(oId);
                        $('#manage-order-status-for-change-user').val(osId);

                        $(`#change-user-picker`).empty().append(response.users);
                        $(`#change-user-picker`).select2({
                            dropdownParent: $('#change-user'),
                            width: '100%',
                            allowClear: true,
                            placeholder: "Select a User"
                        });

                        if (response.total > 0) {
                            $('.hideable-user-change-sbmt-btn').show();
                        } else {
                            $('.hideable-user-change-sbmt-btn').hide();
                        }

                    },
                    complete: function() {
                        $('body').find('.LoaderSec').addClass('d-none');
                    }
                });
            }
        });

        $(document).on('hidden.bs.modal', '#change-user', function (event) {
            if (event.namespace == 'bs.modal') {
                $('#change-user-picker-error').remove();
            }
        });

        $('#changeUser').validate({
            submitHandler: function (form, event) {
                event.preventDefault();

                $.ajax({
                    url: "{{ route('sales-order-responsible-user-save') }}",
                    type: "POST",
                    data: $('#changeUser').serializeArray(),
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                        $('button[type="submit"]').attr('disabled', true);
                    },
                    success: function (response) {
                        if (response.status) {
                            $('#change-user').modal('hide');
                            Swal.fire('', response.message, 'success');
                        } else {
                            Swal.fire('', 'Something went wrong. please try again.', 'error');
                        }
                    },
                    complete: function (response) {
                        $('body').find('.LoaderSec').addClass('d-none');
                        $('button[type="submit"]').attr('disabled', false);
                    }
                });
            }
        });
        /** Change User **/

        /** Drag and Drop **/
        $(".drag-area").sortable({
            connectWith: ".drag-area",
            handle: ".portlet-header",
            cancel: ".portlet-toggle",
            placeholder: "portlet-placeholder ui-corner-all",
            over: function(event, ui) {
                var $this = $(this);

                if ($this.attr('data-cardparent') == "{{ $cwStatus }}") {
                    $(ui.sender).sortable('cancel');
                }
            },
            receive: function(event, ui) {
                var $this = $(this);
                var area = $(event.target).data('cardparent');
                var box = $(ui.item).data('cardchild');

                if ($this.attr('data-cardparent') == "{{ $cwStatus }}") {
                    $(ui.sender).sortable('cancel');
                    return false;
                }

                $.ajax({
                    url: "{{ route('sales-order-status-sequence') }}",
                    type: 'POST',
                    data: {
                        'status': area,
                        'order': box,
                        'windowId': thisWindowId
                    },
                    complete: function(response) {
                        if ('responseJSON' in response && response.responseJSON) {
                            if ('container' in response.responseJSON) {
                                $(that).remove();
                            }

                            if ('card' in response.responseJSON) {
                                $(ui.draggable).remove();
                            }

                            if ('status' in response.responseJSON) {
                                if (!response.responseJSON.status) {
                                    Swal.fire('', response.responseJSON.message,
                                        'error');
                                    setTimeout(() => {
                                        location.reload();
                                    }, 500);
                                } else if (response.responseJSON.status && 'show_message' in response.responseJSON && response.responseJSON.show_message) {
                                    Swal.fire('', response.responseJSON.message,
                                    'success');
                                }
                            }
                        }
                    }
                });
            }
        });

        $(".portlet").find(".portlet-header").addClass("ui-corner-all")
        /** Drag and Drop **/

    });
</script>
@endsection
