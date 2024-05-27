@extends('layouts.master')

@section('content')
    {{ Config::set('app.module', $moduleName) }}
@section('create_button')
    <div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">

        @if(in_array(1, auth()->user()->roles->pluck('id')->toArray()))
        <div>
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
        </div>
        @endif

        @permission('sales-order-status.edit')
            <a href="{{ route('sales-order-status-edit') }}" class="btn-primary f-500 f-14">
                <i class="fa fa-flash" style="color: #ffab00;"></i> &nbsp; AUTOMATE
            </a>
        @endpermission
    </div>
@endsection


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

    .draggable-card  {
        cursor: pointer;
        z-index: 9;
    }


    ::selection {
        -webkit-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .color-blue {
        color: #0057a9;
        cursor: pointer;
    }

    .no-border {
        border: none;
    }

    .no-m {
        margin-bottom: 0rem!important;
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

    .box img{
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
        display: none!important;
    }  
    .filterColumn, .select2-selection__arrow {
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
    .status-label{
        padding: 0 6px;
        border-radius: 4px;
    }
    .status-opener i{
        position: relative;
        top: 1px;
        left: 1px;
    }
    .status-opener:hover{
        background: #f0f0f0;
    }
    .status-main:hover .status-opener{
        visibility: visible;
    }
    .overflowTable .odd td:nth-child(3){
        width: 260px;
        min-width: 260px;
    }
    .overflowTable .odd td:nth-child(4){
        width: 150px;
        min-width: 150px;
    }
    .overflowTable .odd td:nth-child(2){
        width: 150px;
        min-width: 150px;
    }
    .status-modal{
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
    .status-dropdown-toggle{
        border: 1px solid #92989b66;
        width: 100%;
        text-align: left;
        border-radius: 3px;
        padding: 4px 6px;
        background: white;
    }
    .status-dropdown-menu{
        border: 1px solid #92989b66;
        width: 100%;
        text-align: left;
        border-radius: 3px;
        background: white;
        position: absolute;
        top: 0;
        z-index: 1;
        max-height: 185px;
        overflow: auto;
    }
    .status-dropdown-menu::-webkit-scrollbar-track{
        background-color: #ffffff;
    }

    .status-dropdown-menu::-webkit-scrollbar{
        width: 6px;
        background-color: #ffffff;
    }

    .status-dropdown-menu::-webkit-scrollbar-thumb{
        background-color: #c7c7c7;
    }
    .status-dropdown-menu li{
        padding: 3px 6px;
        cursor: pointer;
    }
    .dataTables_wrapper .col-sm-12{
        overflow: inherit;
    }
    .btn-primary:disabled:hover{
        background: #E9EAED !important;
    }
    .-z-1{
        z-index: -1;
    }

    div.dt-processing > div:last-child{
        display:none;
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

    .status-dropdown-menu > li:hover {
        background: #0a141e;
        color: white;
    }

    #minute {
        width: 67px;
        margin-left: 10px;
        height: 28px;
    }

    #hour {
        margin-left: 10px;
        height: 28px;
        width: 52px;
    }

    #minute {
        width: 67px;
        margin-left: 10px;
        height: 28px;
    }

    button.status-dropdown-toggle-2 {
        background: white!important;
        color: black!important;
    }

    .activity {
        font-size: 15px;
    }

    .activity-date {
        font-size: 12px;
    }

    .status-lbl {
        padding: 4px;
        border-radius: 15px;
        margin-right: 2px;
    }
</style>

<div class="content pb-3">

    {{-- Board --}}
    <div class="d-flex overflow-auto pb-3" id="sortable">

        @php $iteration = 0;  @endphp
        @forelse($statuses as $key => $status)
            <div class="card card-row card-secondary no-border">
                @php $tempColor = !empty($status->color) ? $status->color : (isset($colours[$key]) ? $colours[$key] : (isset($colours[$iteration]) ? $colours[$iteration] : ($iteration = 0 and $colours[0] ? $colours[$iteration] : '#99ccff' )));  @endphp
                <div class="card-header" style="border-bottom: 2px solid {{ $tempColor }};">
                    <h3 class="card-title">

                        {{ strtoupper($status->name) }}

                    </h3>
                </div>
                <div class="card-body-main drag-area" data-cardparent="{{ $status->id }}">

                    @if(isset($orders[$status->id]))
                    @foreach ($orders[$status->id] as $order)
                    <div class="card card-light card-outline mb-2 draggable-card portlet" data-cardchild="{{ $order['id'] }}" data-otitle="{{ $order['order_no'] }}">
                        <div class="card-body bg-white border-0 p-2 d-flex justify-content-between portlet-header">
                            <div>
                                <p class="color-blue">{{ $order['order_no'] }}</p>
                                <p class="no-m font-13"> {{ Helper::currencyFormatter($order['amount'], true) }} </p>
                            </div>
                            <div>
                                <div class="card-date f-12 c-7b">
                                    {{ \Carbon\Carbon::parse($order['date'])->diffForHumans() }}
                                </div>
                                <button type="button" class="trigger-btn" data-oid="{{ $order['id'] }}" data-bs-toggle="modal"  data-bs-target="#trigger" data-title="{{ $order['order_no'] }}">
                                    <i class="fa fa-plus"></i>
                                    Add Trigger
                                </button>
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


<div class="modal fade" id="trigger" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header no-border modal-padding">
                <h1 class="modal-title fs-5"> Add Trigger for Order <span id="modal-title"></span> </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="manage-order-id" name="id" />
                <div class="row">

                    <div class="col-4">
                        <div class="form-group">
                            <div class="box">
                                <img src="{{ asset('assets/images/add.png') }}" alt="Add Task">
                                <div> Add Task </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <div class="box" id="lead-stage-btn" >
                                <img src="{{ asset('assets/images/change.png') }}" alt="Change order status">
                                <div> Change order status </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-4">
                        <div class="form-group">
                            <div class="box">
                                <img src="{{ asset('assets/images/swap.png') }}" alt="Change lead's user">
                                <div> Change lead's user </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer no-border">
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="lead-stage" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-xs modal-dialog-centered" style="width: 400px;">
        <div class="modal-content">
            <form action="{{ route('put-order-on-cron') }}" method="POST" id="putOnCron"> @csrf
            <div class="modal-header no-border modal-padding">
                <h1 class="modal-title fs-5"> <span id="modal-title-lead-stage"></span> </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="manage-order-id-for-change-lead-stage" name="clid" />
                <input type="hidden" id="manage-order-time-for-change-lead-stage" name="cltime" value="1" />
                <input type="hidden" id="manage-order-status-for-change-lead-stage" name="clstatus" />
                <div class="row">

                    <div class="col-12 mb-2">
                        <div class="form-group">

                            <label class="c-gr f-500 f-16 w-100 mb-2"> Status Trigger : </label>
                            <div class="status-dropdown status-dropdown-2">
                                <button type="button" class="status-dropdown-toggle status-dropdown-toggle-2 d-flex align-items-center justify-content-between f-14">
                                    <span> Immediatly </span>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                                        <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                                    </svg>
                                </button>
                                <div class="status-dropdown-menu">
                                    <li class="f-14" data-time="1"> Immediatly </li>
                                    <li class="f-14" data-time="2"> 5 minutes </li>
                                    <li class="f-14" data-time="3"> 10 minutes </li>
                                    <li class="f-14" data-time="4"> One day </li>
                                    <li class="f-14 d-flex" data-time="5" style="flex-direction:row;align-items:center;justify-content:left;"> 
                                        <span>Select interval</span>
                                        <div class="d-flex w-75" style="flex-direction:row;align-items:center;justify-content:right;">
                                            <input type="text" class="hour form-control" name="hour" id="hour" placeholder="hour" />
                                            <input type="text" class="minute form-control" name="minute" id="minute" placeholder="minute" />
                                        </div>
                                    </li>
                                </div>
                            </div>
                            <div id="status-dropdown-2-error" class="text-danger"></div>

                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">

                            <label class="c-gr f-500 f-16 w-100 mb-2"> Status : </label>
                            <div id="stage-container">
                                <div class="status-dropdown">
                                    @foreach ($statuses as $status)
                                    @if($loop->first)
                                    <button type="button" style="background:{{ $status->color }};" class="status-dropdown-toggle status-dropdown-toggle-status d-flex align-items-center justify-content-between f-14">
                                        <span>{{ $status['name'] }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                                            <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                                        </svg>
                                    </button>
                                    @endif
                                    @endforeach
                                    <div class="status-dropdown-menu">
                                        @foreach ($statuses as $status)
                                        <li class="f-14" data-sid="{{ $status->id }}" style="background: {{ $status->color }};"> {{ $status->name }} </li>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer no-border hideable">
                <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal"> Cancel </button>
                <button type="submit" class="btn-primary f-500 f-14"> Done </button>
            </div>
            </form>
        </div>
    </div>
</div>


{{-- Order details modal --}}
<div class="modal fade" id="order-details" tabindex="-1" aria-labelledby="exampleModalLongTitle" aria-modal="true" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLongTitle"> <span id="modal-title-1"></span> </h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div id="orderDetails">
                
            </div>
        </div>
      </div>
    </div>
  </div>
{{-- Order details modal --}}
@endsection

@section('script')
<script>
var selectedOpt = null;

    $(document).ready(function() {

        //
        $(document).on('click', '.trigger-btn', function () {
            let oId = $(this).attr('data-oid');
            let Title = $(this).attr('data-title');

            $('#trigger').modal('show');
            $('#trigger').find('#modal-title').text(Title);
            $('#manage-order-id').val(oId);
        });

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
                return 'Select interval';
            } else {
                return '';
            }
        }

        $(document).on('click', '#lead-stage-btn', function () {
            let oId = $('#manage-order-id').val();
            let Title = $('#modal-title').text();

            if (oId != '' && oId != null) {
                $.ajax({
                    url : "{{ route('sales-order-next-status') }}",
                    type : 'POST',
                    data : {
                        id : oId
                    },
                    beforeSend: function () {
                        $('body').find('.LoaderSec').removeClass('d-none');
                    },
                    success: function (response) {
                        $('#trigger').modal('hide');
                        $('#lead-stage').modal('show');
                        $('#lead-stage').find('#modal-title-lead-stage').text(Title);
                        $('#manage-order-id-for-change-lead-stage').val(oId);
                        $('#stage-container').html(response.view);

                        if (Object.values(response.data).length > 0) {
                            $('#manage-order-status-for-change-lead-stage').val(Object.keys(response.data)[0]);
                        } else {
                            $('.hideable').hide();
                        }

                        if (response.added) {
                            $('#manage-order-status-for-change-lead-stage').val(response.addedData.status);
                            $('#manage-order-time-for-change-lead-stage').val(response.addedData.type);

                            $('.status-dropdown-toggle-status').text(response.addedData.status_text);
                            $('.status-dropdown-toggle-status').css('background', response.addedData.status_color);
                            $('.status-dropdown-toggle-status').css('color', generateTextColor(response.addedData.status_color));

                            $('.status-dropdown-toggle-2').text(getTypes(response.addedData.type));

                            if (response.addedData.type == 5) {
                                $('#hour').val(response.addedData.hour);
                                $('#minute').val(response.addedData.minute);
                            }
                        }
                    },
                    complete: function () {
                        $('body').find('.LoaderSec').addClass('d-none');
                        $(".status-dropdown .status-dropdown-menu").hide();
                    }
                });
            }

        });

        // $(document).on('click', '.draggable-card', function () {
        //     let thisOrderId = $(this).attr('data-cardchild');
        //     let thisOrderTitle = $(this).attr('data-otitle');
            
        //     if (thisOrderId != '' && thisOrderId != null) {
        //         $.ajax({
        //             url: "{{ route('order-detail-in-board') }}",
        //             type: "POST",
        //             data: {
        //                 id : thisOrderId
        //             },
        //             success: function (response) {
        //                 if (response.status) {
        //                     $('#modal-title-1').text(thisOrderTitle);
        //                     $('#orderDetails').empty().html(response.view);
        //                     $('#order-details').modal('show');
        //                 }
        //             }

        //         });                
        //     }
        // });

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
            errorPlacement: function(error, element) {
                if ($(element).hasClass('minute')) {
                    $('#status-dropdown-2-error').text('Enter valid hour/minute format.');
                    $('#minute').css('border-color', 'red');
                } else if ($(element).hasClass('hour')) {
                    $('#status-dropdown-2-error').text('Enter valid hour/minute format.');
                    $('#hour').css('border-color', 'red');
                }
            },
            submitHandler: function (form, event) {
                event.preventDefault();

                if ($('#putOnCron').valid()) {
                    $.ajax({
                        url: "{{ route('put-order-on-cron') }}",
                        type: "POST",
                        data: $('#putOnCron').serializeArray(),
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                            $('button[type="submit"]').attr('disabled', true);
                        },
                        success: function (response) {
                            if (response.status) {
                                $('#lead-stage').modal('hide');
                                Swal.fire('', response.message, 'success');
                            } else {
                                Swal.fire('', response.message, 'error');
                            }
                        },
                        complete: function () {
                            $('body').find('.LoaderSec').addClass('d-none');
                            $('button[type="submit"]').attr('disabled', false);      
                        }
                    });
                } else {
                    return false;
                }
            }
        });

        $(document).on('keyup', '#minute', function () {
            $('#status-dropdown-2-error').text('');
            $(this).css('border-color', 'black');
        })

        $(document).on('keyup', '#hour', function () {
            $('#status-dropdown-2-error').text('');
            $(this).css('border-color', 'black');
        })

        $(document).on('hidden.bs.modal', '#lead-stage', function (event) {
            if (event.namespace == 'bs.modal') {
                $('#stage-container').empty();
                $('.status-dropdown-toggle-2').text('Immediatly');
                $('#manage-order-status-for-change-lead-stage').val(null);
                $('#manage-order-time-for-change-lead-stage').val('1');
                $('#manage-order-id-for-change-lead-stage').val(null);
                $('#hour').val(null).css('border-color', 'black');
                $('#minute').val(null).css('border-color', 'black');
                $('#status-dropdown-2-error').text('');
                $('.hideable').show();
            }
        });

        function bindClickToHide(selector) {
            $(selector).on("click", function (event) {
                event.preventDefault();
                $(this).parent().fadeOut();
            });
        }
    
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
            
            if (!target.parents().hasClass("button-dropdown")) {
                    $(".button-dropdown .dropdown-menu").hide();
                    $(".button-dropdown .dropdown-toggle").removeClass("active");
                //hide
            }

            if (!target.parents().hasClass("status-dropdown")) {
                $(".status-dropdown .status-dropdown-menu").hide();
                $(".status-dropdown .status-dropdown-toggle").removeClass("active");
            }
            
        });

        function bindClickToHideModal(selector) {
            $(selector).on("click", function (event) {
                event.preventDefault();
                $(this).parent().fadeOut();
            });
        }
    
        $(document).on('click', '.status-dropdown-toggle', function() {
            var isHidden = $(this).parents(".status-dropdown").children(".status-dropdown-menu").is(":hidden");
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
                var bgColor = $(this).css("background-color");
                var text = $(this).text();
                var thisTime = $(this).attr('data-time');
                var thisSid = $(this).data('sid');

                var dropdownToggle = $(this).closest(".status-dropdown").find(".status-dropdown-toggle");
                var dropdownToggleText = $(this).closest(".status-dropdown").find(".status-dropdown-toggle").find("span");
                dropdownToggleText.text(text);
                
                dropdownToggle.css("background-color", bgColor);
                dropdownToggle.css("color", generateTextColor(bgColor));
                
                // Hide the dropdown menu and remove the active class
                $(this).parent().hide();
                dropdownToggle.removeClass("active");
                
                $(this).parent().parent().parent().find('.status-action-btn').find('.status-save-btn').removeAttr("disabled");

                if ($(this).hasClass('selectable')) {
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
        //




        $( ".drag-area" ).sortable({
            connectWith: ".drag-area",
            handle: ".portlet-header",
            cancel: ".portlet-toggle",
            placeholder: "portlet-placeholder ui-corner-all",
            receive: function (event, ui) {
                var area = $(event.target).data('cardparent');
                var box = $(ui.item).data('cardchild');

                $.ajax({
                    url: "{{ route('sales-order-status-sequence') }}",
                    type: 'POST',
                    data: {
                        'status': area,
                        'order': box
                    },
                    complete: function (response) {
                        if ('responseJSON' in response && response.responseJSON) {
                            if ('container' in response.responseJSON) {
                                $(that).remove();
                            }

                            if ('card' in response.responseJSON) {
                                $(ui.draggable).remove();
                            }

                            if ('status' in response.responseJSON) {
                                if (!response.responseJSON.status) {
                                    Swal.fire('', response.responseJSON.message, 'error');
                                    setTimeout(() => {
                                        location.reload();
                                    }, 500);
                                }
                            }
                        }
                    }
                });
            }
        });

        $( ".portlet" ).find( ".portlet-header" ).addClass( "ui-corner-all" )

    });
</script>
@endsection
