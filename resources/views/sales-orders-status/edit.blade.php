@extends('layouts.master')

@section('css')
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
        right: -18px;
        top: 18px;
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
        width: 13px;
        height: 13px;
        position: relative;
        left: 5px;
        top: 1px;
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
</style>
@endsection

@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="content pb-3">

{{-- Board --}}
<form action="{{ route('sales-order-status-update') }}" method="POST" id="cardForm" > @csrf

    <div class="d-flex align-items-center justify-content-between filterPanelbtn my-2 flex-wrap" style="display: flex!important;justify-content: flex-end!important;">
        @permission("sales-order-status.edit")
        <button type="submit" class="btn-primary f-500 f-14" style="margin-right:10px;"> SAVE </button>
        <a href="{{ route('sales-order-status') }}" class="btn-default f-500 f-14"> BACK </a>
        @endpermission
    </div>

<div class="d-flex overflow-auto pb-3" id="sortable">

    @php $iteration = 0;  @endphp
    @forelse($statuses as $key => $status)
    <div class="card card-row card-secondary parent-card @if($status->id == '1') disable-sorting @endif ">
        @php $tempColor = !empty($status->color) ? $status->color : (isset($colours[$key]) ? $colours[$key] : (isset($colours[$iteration]) ? $colours[$iteration] : ($iteration = 0 and $colours[0] ? $colours[$iteration] : '#99ccff' )));  @endphp
        <input type="hidden" name="sequence[]" value="{{ $status->id }}">
        <div class="card-header" style="border-bottom: 5px solid {{ $tempColor }};">
            @if(count($statuses) == 1 || !$loop->last)
            <span class="sticky-add-icon" data-color="{{ $tempColor }}">
                <i class="fa fa-plus" style="color: #bfbfbf;"></i>
            </span>
            @endif

            <h3 class="card-title">

                @if($status->id != '1')
                <span style="float: left;">
                    <i class="fa fa fa-arrows" style="color: #bfbfbf;"></i>
                </span>
                @endif

                <input type="text" name="name[]" class="title-of-card" value="{{ $status->name }}" @if($status->id == '1') disabled @endif >

                <span style="float: right;">
                    @if($status->id != '1')
                    <i class="fa fa fa-trash"></i>
                    @endif
                    <input type="color" name="color[]" class="color-picker" value="{{ $tempColor }}">
                </span>

            </h3>
        </div>
        <div class="card-body">
        </div>
    </div>
    @empty
    <div class="card card-row card-secondary parent-card">
        <input type="hidden" name="sequence[]" value="1">
        <div class="card-header" style="border-bottom: 3px solid #c1c1c1;">
            <h3 class="card-title">
                <span style="float: left;">
                    <i class="fa fa fa-arrows" style="color: #bfbfbf;"></i>
                </span>

                <input type="text" name="name[]" class="title-of-card" value="TO DO">
            </h3>
        </div>
        <div class="card-body">
        </div>
    </div>
    @endforelse
    
</div>
</form>
{{-- Board --}}

</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#sortable').sortable();

        let hasDuplicateValues = (className) => {
            var valuesCount = {};
            var hasDuplicates = false;
            var valueOfDuplicate = '';

            $(className).each(function() {
                var value = $(this).val();
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
                <div class="card-header" style="border-bottom: 5px solid ${thisColor};">
                    ${totalCards - thisIndex !== 0 ? `<span class="sticky-add-icon" data-color="${thisColor}"><i class="fa fa-plus" style="color:#bfbfbf;"></i></span>` : ''}
                    <h3 class="card-title">

                        <span style="float: left;">
                            <i class="fa fa fa-arrows" style="color: #bfbfbf;"></i>
                        </span>

                        <input type="text" name="name[]" class="title-of-card" value="">

                        <span style="float: right;">
                            <i class="fa fa fa-trash"></i>
                            <input type="color" name="color[]" class="color-picker" value="${thisColor}">
                        </span>

                    </h3>
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

        $(document).on('click', '.fa-trash', function () {
            Swal.fire('', 'This functionality is in development.', 'info');            
        })

    });
</script>
@endsection