@extends('layouts.master')

@section('content')
    {{ Config::set('app.module', $moduleName) }}
@section('create_button')
    <div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap">

        @if(in_array(1, auth()->user()->roles->pluck('id')->toArray()))
        <div>
            <a href="{{ route('sales-order-status-list') }}" class="btn-primary f-500 f-14 d-inline-block"> 
                <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                    <rect x="4" y="2" width="2" height="16" fill="white" />
                    <rect x="9" y="2" width="2" height="12" fill="white" />
                    <rect x="14" y="2" width="2" height="6" fill="white" />
                </svg>
               </a>
            <a href="{{ route('sales-order-status') }}" class="btn-default f-500 f-14 d-inline-block">  
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
@endsection

@section('script')
<script>
    $(document).ready(function() {

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
