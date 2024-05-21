@extends('layouts.master')

@section('content')
    {{ Config::set('app.module', $moduleName) }}
@section('create_button')
    <div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap"
        style="display: flex!important;justify-content: flex-end!important;">
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
    .card-body-main {
        padding: 0.6rem 0.2rem;
    }

    .card-body-main {
        padding: 0.6rem 0.2rem;
    }

    .draggable-card  {
        cursor: move;
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
</style>

<div class="content pb-3">

    {{-- Board --}}
    <div class="d-flex overflow-auto pb-3" id="sortable">

        @php $iteration = 0;  @endphp
        @forelse($statuses as $key => $status)
            <div class="card card-row card-secondary drag-area no-border">
                @php $tempColor = !empty($status->color) ? $status->color : (isset($colours[$key]) ? $colours[$key] : (isset($colours[$iteration]) ? $colours[$iteration] : ($iteration = 0 and $colours[0] ? $colours[$iteration] : '#99ccff' )));  @endphp
                <div class="card-header" style="border-bottom: 2px solid {{ $tempColor }};">
                    <h3 class="card-title">

                        {{ strtoupper($status->name) }}

                    </h3>
                </div>
                <div class="card-body-main area" data-cardparent="{{ $status->id }}">

                    @if(isset($orders[$status->id]))
                    @foreach ($orders[$status->id] as $order)
                    <div class="card card-light card-outline mb-2 draggable-card" data-cardchild="{{ $order['id'] }}">
                        <div class="card-body">
                            <a target="_blank" href="{{ route('sales-orders.view', encrypt($order['id'])) }}" class="color-blue">{{ $order['order_no'] }}</a>
                            <p class="no-m font-13"> {{ Helper::currencyFormatter($order['amount'], true) }} </p>
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
        $( ".draggable-card" ).draggable({
            scope: 'demoBox',
            revertDuration: 100,
            start: function( event, ui ) {
                    $( ".draggable-card" ).draggable( "option", "revert", true );
                }
            });
            $( ".drag-area" ).droppable({
            scope: 'demoBox',
            drop: function( event, ui ) {
                var area = $(this).find(".area").data('cardparent');
                var box = $(ui.draggable).data('cardchild')
                var that = this;

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
                                if (response.responseJSON.status) {
                                    $( ".draggable-card" ).draggable( "option", "revert", false );
                                    $(ui.draggable).detach().css({top: 0,left: 0}).appendTo($(that).find(".area"));   
                                } else {
                                    Swal.fire('', response.responseJSON.message, 'error');
                                }
                            }
                        }
                    }
                })

            }
        })

    });
</script>
@endsection
