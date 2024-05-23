@extends('layouts.master')

{{ Config::set('app.module', $moduleName) }}

@section('css')
<style>
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
        visibility: hidden;
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
        z-index: 1;
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
    }
    .status-dropdown-menu li{
        padding: 3px 6px;
        cursor: pointer;
    }
    .dataTables_wrapper .col-sm-12{
        overflow-x: auto;
        overflow-y: inherit;
    }
    .btn-primary:disabled:hover{
        background: #E9EAED !important;
    }
    .-z-1{
        z-index: -1;
    }

</style>
@endsection

@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">

    @if(in_array(1, auth()->user()->roles->pluck('id')->toArray()))
    <div>
        <a href="{{ route('sales-order-status') }}" class="btn-default f-500 f-14 d-inline-block"> 
            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="2" width="2" height="16" fill="black" />
                <rect x="9" y="2" width="2" height="12" fill="black" />
                <rect x="14" y="2" width="2" height="6" fill="black" />
            </svg>
           </a>
        <a href="{{ route('sales-order-status-list') }}" class="btn-primary f-500 f-14 d-inline-block">  
            <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="4" width="16" height="2" fill="white" />
                <rect x="2" y="9" width="16" height="2" fill="white" />
                <rect x="2" y="14" width="16" height="2" fill="white" />
            </svg>                           
        </a>
    </div>
    @endif

</div>
@endsection

@section('content')
<div class="cards">
    <div class="row m-0 filterColumn">
        <div class="col-12 position-relative">
            <button class="btn-primary" id="status-change"> <i class="fa fa-edit"></i> Change Status </button>
        </div>
    </div>

    <table id="list" class="table datatableMain" style="width: 100%!important;">
        <thead>
            <tr>
                <th> <input type="checkbox" class="form-check-input" id="main-checkbox"> </th>
                <th>TITLE</th>
                <th>STATUS</th>
                <th>ORDER DATE</th>
                <th>AMOUNT</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

</div>

<div class="modal fade" id="status-modal" tabindex="-1" aria-labelledby="exampleModalLabelA" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header no-border modal-padding">
            <h1 class="modal-title fs-5" id="exampleModalLabel">Change status </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Do you want to apply the action to all orders or only to the current page orders?
            </div>
            <div class="modal-footer no-border">
            <button type="button" class="btn btn-primary" id="final-status-changer-modal-2">Current page only</button>
            <button type="button" class="btn btn-primary" id="for-all-orders">For the all orders</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="all-orders-confirmation-modal" tabindex="-1" aria-labelledby="exampleModalLabelB" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                Are you sure to change <span id="number-of-orders"> all </span> orders statuses?
            </div>
            <div class="modal-footer no-border">
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" id="final-status-changer-modal">Apply</button>
            </div>
          </div>
    </div>
</div>

<div class="modal fade s2-parent" id="final-status-modal" tabindex="-1" aria-labelledby="exampleModalLabelC" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="w-100" action="{{ route('sales-order-status-update-status-bulk') }}" method="POST" id="os-1"> @csrf
            <div class="modal-content">
                <div class="modal-header no-border modal-padding">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Change status </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body select2-putter">

                    <input type="hidden" name="ids" id="set-id-os-1" value="all">

                    <select class='status-select1' name="status">
                    @foreach ($statuses as $status)
                        <option value="{{ $status['id'] }}" data-color="{{ $status['color'] }}"> {{ $status['name'] }} </option>
                    @endforeach                    
                    </select>

                </div>
                <div class="modal-footer no-border">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="save-status">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade s2-parent" id="final-status-modal-b" tabindex="-1" aria-labelledby="exampleModalLabelD" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="w-100" action="{{ route('sales-order-status-update-status-bulk') }}" method="POST" id="os-2"> @csrf
            <div class="modal-content">
                <div class="modal-header no-border modal-padding">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Change status </h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body select2-putter">

                    <input type="hidden" name="ids" id="set-id-os-2">

                    <div class="status-dropdown">
                        <button type="button" style="background:pink;" class="status-dropdown-toggle d-flex align-items-center justify-content-between f-14">
                            <span>Open</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" height="12" width="12" viewBox="0 0 330 330">
                                <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393  c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393  s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
                            </svg>
                        </button>
                        <div class="status-dropdown-menu">
                            <li style="background: pink">1</li>
                            <li style="background: antiquewhite">2</li>
                            <li style="background: beige">3</li>
                        </div>
                    </div>
                    <!-- <select class='status-select1' name="status">
                    @foreach ($statuses as $status)
                        <option value="{{ $status['id'] }}" data-color="{{ $status['color'] }}"> {{ $status['name'] }} </option>
                    @endforeach                    
                    </select> -->

                </div>
                <div class="modal-footer no-border">
                <button type="button" class="btn-default f-500 f-14" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-primary f-500 f-14" id="save-status">Apply</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('script')
<script>
var totalOrders = 0;

    $(document).ready(function() {

        function generateTextColor(hexcolor){
            hexcolor = hexcolor.replace("#", "");
            var r = parseInt(hexcolor.substr(0,2),16);
            var g = parseInt(hexcolor.substr(2,2),16);
            var b = parseInt(hexcolor.substr(4,2),16);
            var yiq = ((r*299)+(g*587)+(b*114))/1000;
            return (yiq >= 128) ? '#000' : '#fff';
        }

        var ServerDataTable = $('#list').DataTable({
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search here",

            },
            processing: true,
            serverSide: true,
            oLanguage: {sProcessing: "<div id='dataTableLoader'></div>"},
            "dom": "<'filterHeader d-block-500 cardsHeader'l<'#filterInput'>>" + "<'row m-0'<'col-sm-12 p-0'tr>>" + "<'row datatableFooter'<'col-md-5 align-self-center'i><'col-md-7'p>>",
            language: {info: ""},
            ajax: {
                "url": "{{ route('sales-order-status-list') }}",
                "dataType": "json",
                "type": "POST"
            },
            columnDefs: [{ orderable: false, targets: 0 }],
            order: [[1, 'asc']],
            columns: [{
                    data: 'checkbox',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'order_no',
                },
                {
                    data: 'status',
                    searchable: false,
                },
                {
                    data: 'date',
                    searchable: false,
                },
                {
                    data: 'amount',
                    searchable: false,
                }
            ],
            drawCallback: function (data) {
                $('.status-select2').select2({
                    width: '100%',
                    allowClear: true,
                    templateResult: function (option, ele) {

                        if (!option.id) {
                            return option.text;
                        }

                        var color = $(option.element).data('color');
                        $(ele).css({
                            'background-color' : color,
                            'color' : generateTextColor(color)
                        });

                        var $result = $('<span></span>');
                        $result.text(option.text);
                        $result.attr('data-oid', option.oid);
                        return $result;
                    },
                    templateSelection: function (container) {
                        $(container.element).attr("data-oid", container.oid);
                        return container.text;
                    }
                });
                totalOrders = data.json.totalOrders;
            }
        });

        $('.status-select1').select2({
            dropdownParent: $('.s2-parent'),
            width: '100%',
            allowClear: true,
            templateResult: function (option, ele) {

                if (!option.id) {
                    return option.text;
                }

                var color = $(option.element).data('color');
                $(ele).css({
                    'background-color' : color,
                    'color' : generateTextColor(color)
                });

                return option.text;
            }
        });

        ServerDataTable.on('page.dt', function () {
            $('#main-checkbox').prop('checked', false);
        });

        $(document).on('change', '#main-checkbox', function () {
            if ($(this).prop('checked')) {
                $('.single-checkbox').prop('checked', true);
            } else {
                $('.single-checkbox').prop('checked', false);
            }

            showToolBox();
        });

        $(document).on('change', '.single-checkbox', function () {
            if ($(this).prop('checked')) {
                let anyUnchecked = false;

                $('.single-checkbox').each(function (index, element) {
                    if (!$(element).prop('checked')) {
                        anyUnchecked = true;
                    }
                });

                if (anyUnchecked) {
                    $('#main-checkbox').prop('checked', false);
                } else {
                    $('#main-checkbox').prop('checked', true);
                }

            } else {
                if ($('#main-checkbox').prop('checked')) {
                    $('#main-checkbox').prop('checked', false);
                }
            }

            showToolBox();
        });

        $(document).on('change', '.status-select2', function () {
            let thisStatus = $(this).val();
            let thisOrder = $('option:selected', this).data('oid');

            if (thisStatus !== '' && thisOrder !== '') {
                $.ajax({
                    url: "{{ route('sales-order-status-update-status') }}",
                    type: "POST",
                    data: {
                        status : thisStatus,
                        order : thisOrder
                    },
                    success: function (response) {
                        if (response.status) {
                            Swal.fire('', response.message, 'success');
                        } else {
                            Swal.fire('', response.message, 'error');
                        }
                    },
                    
                });                
            }
        })
     
        $(document).on('click', '#status-change', function () {
            if ($('#main-checkbox').prop('checked')) {
                $('#status-modal').modal('show');
            } else {
                $('#final-status-modal-b').modal('show');
            }
        })

        $(document).on('click', '#for-all-orders', function () {
            $('#number-of-orders').text(totalOrders);
            $('#status-modal').modal('hide');
            $('#all-orders-confirmation-modal').modal('show');
        })

        $(document).on('click', '#final-status-changer-modal', function() {
            $('#status-modal').modal('hide');
            $('#all-orders-confirmation-modal').modal('hide');
            $('#final-status-modal').modal('show');
        });

        $(document).on('click', '#final-status-changer-modal-2', function() {
            $('#status-modal').modal('hide');
            $('#final-status-modal-b').modal('show');
        });

        function showToolBox () {
            let anyCheckboxChecked = false;
            
            if ($('#main-checkbox').prop('checked')) {
                anyCheckboxChecked = true;
            }

            $('.single-checkbox').each(function (index, element) {
                if ($(element).prop('checked')) {
                    anyCheckboxChecked = true;
                }
            });
            
            if (anyCheckboxChecked) {
                $('.filterColumn').show();
            } else {
                $('.filterColumn').hide();
            }
        }

        $('#os-1').validate({
            rules: {
                'status' : {
                    required: true
                }
            },
            messages: {
                'status' : {
                    required: "Select a status."
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent("div"));
            },
            submitHandler:function(form) {
                $('button[type="submit"]').attr('disabled', true);
                if(!this.beenSubmitted) {
                    this.beenSubmitted = true;
                    form.submit();
                }
            }
        });

        $('#os-2').validate({
            rules: {
                'status' : {
                    required: true
                }
            },
            messages: {
                'status' : {
                    required: "Select a status."
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent("div"));
            },
            submitHandler:function(form) {
                $('button[type="submit"]').attr('disabled', true);
                if(!this.beenSubmitted) {

                    var ids = [];

                    $('.single-checkbox').each(function (index, element) {
                        if ($(element).prop('checked')) {
                            ids.push($(element).val());
                        }
                    });

                    if (ids.length > 0) {
                        $('#set-id-os-2').val(ids.toString());
                        this.beenSubmitted = true;
                        form.submit();
                    } else {
                        Swal.fire('', 'Something went wrong please try again later.', 'error');
                        return false;
                    }
                }
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
    
        $(document).on('click', function() {
            var target = $(event.target);
            
            if (!target.parents().hasClass("button-dropdown")) {
                $(".button-dropdown .dropdown-menu").hide();
                $(".button-dropdown .dropdown-toggle").removeClass("active");
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
    
        $(document).on('click', function() {
            var target = $(event.target);
            
            if (!target.parents().hasClass("status-dropdown")) {
                $(".status-dropdown .status-dropdown-menu").hide();
                $(".status-dropdown .status-dropdown-toggle").removeClass("active");
            }
        });

        

        $(document).on('click', '.status-dropdown-menu li', function() {
            var bgColor = $(this).css("background-color");
            var text = $(this).text();
            
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
        
    });
</script>
@endsection
