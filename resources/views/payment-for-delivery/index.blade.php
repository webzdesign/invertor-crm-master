@extends('layouts.master')

@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="cards">
    <form action="{{ route('payment-for-delivery') }}" method="POST" id="delivery4Payment"> @csrf
    <div class="cardsBody pb-0">

            <div class="row">
                
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2"> <strong>Distance from customer to driver</strong> : <span class="text-danger">*</span></label>
                            <input type="text" name="distance" id="distance" value="{{ old('distance', $payment->distance ?? '') }}" class="form-control" placeholder="Enter distance from customer to driver">
                            @if ($errors->has('distance'))
                                <span class="text-danger d-block">{{ $errors->first('distance') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2"> <strong>Payment for distance</strong> : <span class="text-danger">*</span></label>
                            <input type="text" name="payment" id="payment" value="{{ old('payment', $payment->payment ?? '') }}" class="form-control" placeholder="Enter payment for distance">
                            @if ($errors->has('payment'))
                                <span class="text-danger d-block">{{ $errors->first('payment') }}</span>
                            @endif
                        </div>
                    </div>

            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">

                        <div class="main-btn-container mb-6">
                            <button type="button" class="btn btn-primary" id="price-adder" style="width:fit-content;margin-bottom:10px;" > Add For Specific Driver </button>
                        </div>

                        <div class="price-container">
                            <table class="table table-bordered customLayout">
                                <thead>
                                    <tr>
                                        <th class="block-a"> Driver <span class="text-danger">*</span> </th>
                                        <th class="block-b"> Distance from customer to driver <span class="text-danger">*</span> </th>                
                                        <th class="block-c"> Payment for distance <span class="text-danger">*</span> </th>                
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                
                                <tbody class="upsertable">
                                    @forelse($payments as $key => $value)
                                    <tr>
                                        <td class="block-a">
                                            <div style="min-width: 200px;width: 100%" class="removable-driver">
                                                <select name="mdriver[{{ $key }}]" data-indexid="{{ $key }}" id="m-driver-{{ $key }}" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver">
                                                    @forelse($drivers as $did => $dname)
                                                        @if($loop->first)
                                                        <option value="" selected> --- Select a Driver --- </option>
                                                        @endif
                                                        <option value="{{ $did }}" @if($did == $value->driver_id) selected @endif >{{ $dname }}</option>
                                                        @empty
                                                        <option value="" selected> --- No Driver Available --- </option>
                                                    @endforelse
                                                </select>
                                            </div>
                                        </td>
                
                                        <td class="block-b">
                                            <div style="min-width: 200px;width: 100%" class="removable-product">
                                                <input type="hidden" data-indexid="{{ $key }}" value="{{ $value->id }}" name="edit_id[{{ $key }}]" id="edit_id-{{ $key }}" class="form-control edit_id">
                                                <input type="text" data-indexid="{{ $key }}" name="mdistance[{{ $key }}]" id="m-distance-{{ $key }}" class="form-control m-distance" style="background:#ffffff" value="{{ $value->distance }}" placeholder="Enter payment for distance">
                                            </div>
                                        </td>
                
                                        <td class="block-c">
                                            <div style="min-width: 200px;">
                                                <input type="text" data-indexid="{{ $key }}" name="mpayment[{{ $key }}]" id="m-payment-{{ $key }}" class="form-control m-payment" style="background:#ffffff" value="{{ $value->payment }}" placeholder="Enter payment for distance">
                                            </div>
                                        </td>
                
                                        <td style="width:100px;">
                                            <div style="min-width: 100px;">
                                                <button type="button" class="btn btn-primary btn-sm addNewRow">+</button>
                                                <button type="button" class="btn btn-danger btn-sm removeRow" tabindex="-1">-</button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    {{-- <tr>
                                        <td class="block-a">
                                            <div style="min-width: 200px;width: 100%" class="removable-driver">
                                                <select name="mdriver[0]" data-indexid="0" id="m-driver-0" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver">
                                                    @forelse($drivers as $did => $dname)
                                                        @if($loop->first)
                                                        <option value="" selected> --- Select a Driver --- </option>
                                                        @endif
                                                        <option value="{{ $did }}">{{ $dname }}</option>
                                                        @empty
                                                        <option value="" selected> --- No Driver Available --- </option>
                                                    @endforelse
                                                </select>
                                            </div>
                                        </td>
                
                                        <td class="block-b">
                                            <div style="min-width: 200px;width: 100%" class="removable-product">
                                                <input type="text" data-indexid="0" name="mdistance[0]" id="m-distance-0" class="form-control m-distance" style="background:#ffffff">
                                            </div>
                                        </td>
                
                                        <td class="block-c">
                                            <div style="min-width: 200px;">
                                                <input type="text" data-indexid="0" name="mpayment[0]" id="m-payment-0" class="form-control m-payment" style="background:#ffffff">
                                            </div>
                                        </td>
                
                                        <td style="width:100px;">
                                            <div style="min-width: 100px;">
                                                <button type="button" class="btn btn-primary btn-sm addNewRow">+</button>
                                                <button type="button" class="btn btn-danger btn-sm removeRow" tabindex="-1">-</button>
                                            </div>
                                        </td>
                                    </tr> --}}
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="block-a"></td>
                                        <td class="block-b"></td>
                                        <td class="block-c"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            @php
                                $ers = array_unique(\Illuminate\Support\Arr::flatten($errors->toArray()));
                            @endphp

                            @forelse($ers as $key => $value)
                            <p> {{ $value }} </p>
                            @empty
                            <p>Something went wrong.</p>
                            @endforelse

                        </div>
                        @endif

                    </div>
                </div>
            </div>
    </div>

    <div class="cardsFooter d-flex justify-content-center">
        <button type="submit" class="btn-primary f-500 f-14">Save</button>
    </div>
</form>

</div>

@endsection

@section('script')

<script>
var drivers = {!! json_encode($drivers) !!};
driversHtml = `<option value="" selected> --- Select a Driver --- </option>`;

for (key in drivers) {
    driversHtml += `<option value="${key}"> ${drivers[key]} </option>`;
}

var content = '<tr><td class="block-a"><div style="min-width: 200px;width: 100%" class="removable-driver"><select name="mdriver[0]" data-indexid="0" id="m-driver-0" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver"><option value="" selected> --- Select a Driver --- </option></select></div></td><td class="block-b"><div style="min-width: 200px;width: 100%" class="removable-distance"><input type="text" data-indexid="0" placeholder="Enter distance from customer to driver" name="mdistance[0]" id="m-distance-0" class="form-control m-distance" style="background:#ffffff"></div></td><td class="block-c"><div style="min-width: 200px;"><input type="text" data-indexid="0" name="mpayment[0]" placeholder="Enter payment for distance" id="m-payment-0" class="form-control m-payment" style="background:#ffffff"></div></td><td style="width:100px;"><div style="min-width: 100px;"><button type="button" class="btn btn-primary btn-sm addNewRow">+</button> <button type="button" class="btn btn-danger btn-sm removeRow" tabindex="-1">-</button></div></td></tr>';

$(document).ready(function() {

    @if(count($payments) > 0)
        $('#price-adder').hide();
    @else
        $('#price-adder').show();
        $('.customLayout').hide();
    @endif

    let lastElementIndex = {{ count($payments) > 0 ? count($payments) : 0 }};

    $(document).on('click', '#price-adder', function () {
        if ($('.upsertable tr').length < 1) {
            $('#price-adder').hide();
            $('.customLayout').show();
            $('.upsertable').html(content);

            $('.upsertable tr').find('.removable-driver').empty().append(`<select data-indexid="${lastElementIndex}" name="mdriver[${lastElementIndex}]" id="m-driver-${lastElementIndex}" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver"> ${driversHtml} </select> `);
            $('.upsertable tr').find('.removable-driver .m-driver').select2({
                width: '100%',
                allowClear: true
            });
        }
    });
    
    $(document).on('click', '.removeRow', function(event) {
        let count = $('.upsertable tr').length;

        if (count > 0) {
            $(this).closest("tr").remove();
            if (count === 1) {
                $('#price-adder').show();
                $('.customLayout').hide();
            }
        }

    });

    $(document).on('click', '.addNewRow', function (event) {

        cloned = $('.upsertable').find('tr').eq(0).clone();
        lastElementIndex++;

        cloned.find('.removable-driver').empty().append(`<select data-indexid="${lastElementIndex}" name="mdriver[${lastElementIndex}]" id="m-driver-${lastElementIndex}" class="select2 select2-hidden-accessible m-driver" style="width:100%" data-placeholder="Select a Driver"> ${driversHtml} </select> `);
        cloned.find('.m-driver').select2({
            width: '100%',
            allowClear: true
        });

        cloned.find('.m-distance').attr('id', `m-distance-${lastElementIndex}`).attr('data-indexid', lastElementIndex).attr('name', `mdistance[${lastElementIndex}]`).val(null);
        cloned.find('.m-payment').attr('id', `m-payment-${lastElementIndex}`).attr('data-indexid', lastElementIndex).attr('name', `mpayment[${lastElementIndex}]`).val(null);

        cloned.find('label.error').remove();
        $('.upsertable').append(cloned.get(0));

        cloned.find('.m-driver').rules('add', {
            required: true,
            messages: {
                required: "Select a driver."
            }
        }); 

        cloned.find('.m-payment').rules('add', {
            required: true,
            number: true,
            min: 1,
            messages: {
                required: "Enter payment amount.",
                digits: "Enter valid format.",
                min: "Payment amount can\'t be less than 1.",
            }
        }); 

        cloned.find('.m-distance').rules('add', {
            required: true,
            number: true,
            min: 0,
            messages: {
                required: "Enter distance.",
                digits: "Enter valid format.",
                min: "Distance can\'t be less than 1.",
            }
        }); 

    });

    $(document).on('change', '.m-driver', function (event) {
        let indexId = $(this).data('indexid');
        let thisId = $(this).val();
        
        let that = $(this);

        $('.m-driver').not(this).each(function (index, element) {
            if ($(element).val() !== null && thisId == $(element).val()) {
                $(that).val(null).trigger('change');
                Swal.fire('Warning', 'Driver is already selected.', 'warning');
                return false;
            }
        });
    });

    $('#delivery4Payment').validate({
        rules: {
            distance : {
                required: true,
                min: 0,
                number: true
            },
            payment : {
                required: true,
                min: 0,
                number: true
            },
            @forelse ([] as $key => $value)
                "mdriver[{{ $key }}]" : {
                    required: true
                },  
                "mdistance[{{ $key }}]" : {
                    required: true,
                    min: 0,
                    number: true
                },  
                "mpayment[{{ $key }}]" : {
                    required: true,
                    min: 0,
                    number: true
                },  
            @empty
                "mdriver[0]" : {
                    required: true
                },  
                "mdistance[0]" : {
                    required: true,
                    min: 0,
                    number: true
                },  
                "mpayment[0]" : {
                    required: true,
                    min: 0,
                    number: true
                },  
            @endforelse
        },
        messages: {
            distance : {
                required: "Enter distance.",
                min: "Distance can\'t be less than 1.",
                number: "Enter valid distance."
            },
            payment : {
                required: "Enter payment amount.",
                min: "Payment amount can\'t be less than 1.",
                number: "Enter valid payment amount."
            },
            @forelse ([] as $key => $value)
                "mdriver[{{ $key }}]" : {
                    required: "Select a driver"
                },  
                "mdistance[{{ $key }}]" : {
                    required: "Enter distance.",
                    min: "Distance can\'t be less than 1.",
                    number: "Enter valid distance."
                },  
                "mpayment[{{ $key }}]" : {
                    required: "Enter payment amount.",
                    min: "Payment amount can\'t be less than 1.",
                    number: "Enter valid payment amount."
                },  
            @empty
                "mdriver[0]" : {
                    required: "Select a driver"
                },  
                "mdistance[0]" : {
                    required: "Enter distance.",
                    min: "Distance can\'t be less than 1.",
                    number: "Enter valid distance."
                },  
                "mpayment[0]" : {
                    required: "Enter payment amount.",
                    min: "Payment amount can\'t be less than 1.",
                    number: "Enter valid payment amount."
                },  
            @endforelse
        },
        errorPlacement: function(error, element) {
            error.appendTo(element.parent("div"));
        },
    });
});
</script>
@endsection