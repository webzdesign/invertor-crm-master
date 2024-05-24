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
        cursor: move;
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
        box-shadow: inset 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    }

    .trigger-btn {
        font-size: 12px;
        width: fit-content;
        height: fit-content;
        padding: 0;
        border: none;
        background: transparent;
        position: relative;
        top: 5px;
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
        box-shadow: 0 5px 10px 0 rgba(0,0,0, .1);
        box-sizing: border-box;
        padding: 12px;
        border: 1px solid #e8eaeb;
        width: 100%;
        background: #fff;
        z-index: 9;
    }
    .status-dropdown-toggle{
        border: 1px solid rgba(146, 152, 155, 0.4);
        width: 100%;
        text-align: left;
        border-radius: 3px;
        padding: 4px 6px;
        background: white;
    }
    .status-dropdown-menu{
        border: 1px solid rgba(146, 152, 155, 0.4);
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
                    <div class="card card-light card-outline mb-2 draggable-card portlet" data-cardchild="{{ $order['id'] }}">
                        <div class="card-body bg-white border-0 p-2 d-flex justify-content-between portlet-header">
                            <div>
                                <a target="_blank" href="{{ route('sales-orders.view', encrypt($order['id'])) }}" class="color-blue">{{ $order['order_no'] }}</a>
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
                                <img src="{{ asset('assets/images/change.png') }}" alt="Change lead stage">
                                <div> Change lead stage </div>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header no-border modal-padding">
                <h1 class="modal-title fs-5"> Lead state change for order <span id="modal-title-lead-stage"></span> </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="manage-order-id-for-change-lead-stage" name="id" />
                <div class="row">

                    <div class="col-12">
                        <div class="form-group">

                            <label class="c-gr f-500 f-16 w-100 mb-2"> Stages : </label>
                            <div class="status-dropdown">
                                @foreach ($statuses as $status)
                                @if($loop->first)
                                <button type="button" style="background:{{ $status->color }};" class="status-dropdown-toggle d-flex align-items-center justify-content-between f-14">
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


                            <label class="w-100 mb-2 text-danger text-center"> This functionality is in development. </label>

                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer no-border">
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {

        //
        $(document).on('click', '.trigger-btn', function () {
            let oId = $(this).attr('data-oid');
            let Title = $(this).attr('data-title');

            $('#trigger').modal('show');
            $('#trigger').find('#modal-title').text(Title);
            $('#manage-order-id').val(oId);
        });

        $(document).on('click', '#lead-stage-btn', function () {
            let oId = $('#manage-order-id').val();
            let Title = $('#modal-title').text();

            $('#trigger').modal('hide');
            $('#lead-stage').modal('show');
            $('#lead-stage').find('#modal-title-lead-stage').text(Title);
            $('#manage-order-id-for-change-lead-stage').val(oId);
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
    
        $(document).on('click', function() {
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
    
        $(document).on('click', '.status-dropdown-menu li', function() {
            var bgColor = $(this).css("background-color");
            var text = $(this).text();
            var thisSid = $(this).data('sid');
            var thisOid = $(this).data('oid');

            var dropdownToggle = $(this).closest(".status-dropdown").find(".status-dropdown-toggle");
            var dropdownToggleText = $(this).closest(".status-dropdown").find(".status-dropdown-toggle").find("span");
            dropdownToggleText.text(text);
            
            dropdownToggle.css("background-color", bgColor);
            
            // Hide the dropdown menu and remove the active class
            $(this).parent().hide();
            dropdownToggle.removeClass("active");
            
            $(this).parent().parent().parent().find('.status-action-btn').find('.status-save-btn').removeAttr("disabled");

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
