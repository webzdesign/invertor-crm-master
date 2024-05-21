@extends('layouts.master')

@section('content')
{{ Config::set('app.module', $moduleName) }}
@section('create_button')
<div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap" style="display: flex!important;justify-content: flex-end!important;">
    @permission("sales-order-status.edit")
    <a href="{{ route('sales-order-status-edit') }}" class="btn-primary f-500 f-14">
        <i class="fa fa-flash" style="color: #ffab00;"></i> &nbsp; AUTOMATE
    </a>
    @endpermission
</div>
@endsection


<style>
    .card-header{
        background: #f5f5f5;
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
</style>

<div class="content pb-3">

{{-- Board --}}
<div class="d-flex overflow-auto pb-3" id="sortable">
    
    @php $iteration = 0;  @endphp
    @forelse($statuses as $key => $status)
    <div class="card card-row card-secondary">
        @php $tempColor = !empty($status->color) ? $status->color : (isset($colours[$key]) ? $colours[$key] : (isset($colours[$iteration]) ? $colours[$iteration] : ($iteration = 0 and $colours[0] ? $colours[$iteration] : '#99ccff' )));  @endphp
        <div class="card-header" style="border-bottom: 5px solid {{ $tempColor }};">
            <h3 class="card-title">

                {{ strtoupper($status->name) }}

            </h3>
        </div>
        <div class="card-body">
        </div>
    </div>
    @empty
    <div class="card card-row card-secondary">
        <div class="card-header" style="border-bottom: 3px solid #c1c1c1;">
            <h3 class="card-title">

                TO DO
            </h3>
        </div>
        <div class="card-body">
        </div>
    </div>
    @endforelse

</div>
{{-- Board --}}

</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
               
    });
</script>
@endsection