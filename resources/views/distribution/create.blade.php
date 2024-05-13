@extends('layouts.master')

@section('css')
<style>
    .customLayout th {
        white-space: nowrap;
        font-size: 13px;
    }

    .srNumberClass {
        font-size: 10px !important;
    }
</style>
@endsection

@section('content')
    {{ Config::set('app.module', $moduleName) }}
    <h2 class="f-24 f-700 c-36 my-2"> {{ $moduleName }}</h2>
    <form action="{{ route('distribution.store') }}" method="POST" id="assignStock"> @csrf
        <div class="cards">
            <div class="cardsBody pb-0">

                <div class="row">

                    <div class="col-sm-12 col-md-4">
                        <div class="form-group">
                            <label for="type" class="c-gr f-500 f-16 w-100 mb-2">Distribution Type :
                                <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="type" class="select2-hidden-accessible select2" data-placeholder="--- Select a Type ---">
                                @forelse($types as $id => $type)
                                @if($loop->first)
                                <option value="" selected> --- Select a Distribution Type --- </option>
                                @endif
                                <option value="{{ $id }}">{{ $type }}</option>
                                @empty
                                <option value="" selected> --- No Distribution Type Available --- </option>
                                @endforelse
                            </select>
                            @if ($errors->has('supplier'))
                                <span class="text-danger d-block">{{ $errors->first('supplier') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="container-for-blade">

                    </div>
                </div>

                
            </div>

            <div class="cardsFooter d-flex justify-content-center">
                <a href="{{ route('distribution.index') }}">
                    <button type="button" class="btn-default f-500 f-14">Cancel</button>
                </a>
                <button type="submit" class="btn-primary f-500 f-14">Save</button>
            </div>
        </div>
    </form>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            
            $('#type').on('change', function () {
                let type = $(this).val();

                if (type !== '' && type !== null && type >= 1 && type <= 3) {
                    $.ajax({
                        url: "{{ route('get-blade-for-distribution') }}",
                        type: 'POST',
                        data: {
                            type : type
                        },
                        beforeSend: function () {
                            $('body').find('.LoaderSec').removeClass('d-none');
                        },
                        success: function (response) {
                            if (response.status) {
                                $('.container-for-blade').html(response.html);
                            } else {
                                $('.container-for-blade').html('');
                            }
                        },
                        complete: function () {
                            $('body').find('.LoaderSec').addClass('d-none');
                        }
                    });
                } else {
                    $('.container-for-blade').html('');
                }

            });

            $.validator.addMethod('diffDriver', function (value, element) {
                let bool = true;
                let thisInd = $(element).data('indexid');

                if (value !== '' && value !== null && value == $(`#driver-${thisInd}`).val()) {
                    bool = false;
                }

                return bool;
            }, 'You can\'t assign stock to self(driver).');

            $('#assignStock').validate({
                rules: {
                    'type': {
                        required: true
                    },
                    'product[0]': {
                        required: true
                    },
                    'driver[0]': {
                        required: true
                    },
                    'receiver[0]': {
                        required: true
                    },
                    'quantity[0]': {
                        required: true,
                        digits: true,
                        min: 1,
                    },
                    'from_driver[0]' : {
                        required: true,
                        diffDriver: true
                    }
                },
                messages: {
                    'type': {
                        required: "Select a type."
                    },
                    'product[0]': {
                        required: "Select a product."
                    },
                    'driver[0]': {
                        required: "Select a driver."
                    },
                    'receiver[0]': {
                        required: "Select a receiver driver."
                    },
                    'quantity[0]': {
                        required: "Enter quantity",
                        digits: "Enter valid format.",
                        min: "Quantity can\'t be less than 1."
                    },
                    'from_driver[0]': {
                        required: "Select a driver."
                    },
                },
                errorPlacement: function(error, element) {
                    error.appendTo(element.parent("div"));
                }
            });

        });
    </script>
@endsection
