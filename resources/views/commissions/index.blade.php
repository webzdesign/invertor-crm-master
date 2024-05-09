@extends('layouts.master')

@section('content')
{{ Config::set('app.module', $moduleName) }}
<div class="cards">
    <form action="{{ route('commissions.index') }}" method="POST" id="commission"> @csrf
    <div class="cardsBody pb-0">

            <div class="row">
                
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Bonus : <span class="text-danger">*</span></label>
                            <input type="text" name="bonus" id="bonus" value="{{ old('bonus', $settings->bonus ?? null) }}" class="form-control" placeholder="Enter bonus amount">
                            @if ($errors->has('bonus'))
                                <span class="text-danger d-block">{{ $errors->first('bonus') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Seller Commission : <span class="text-danger">*</span></label>
                            <input type="text" name="seller_commission" id="seller_commission" value="{{ old('seller_commission', $settings->seller_commission ?? null) }}" class="form-control" placeholder="Enter commission amount">
                            @if ($errors->has('seller_commission'))
                                <span class="text-danger d-block">{{ $errors->first('seller_commission') }}</span>
                            @endif
                        </div>
                    </div>

            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">

                        <label class="c-gr f-500 f-16 w-100 mb-2">Prices : </label>

                        <div class="main-btn-container mb-4">
                            <button type="button" class="btn btn-primary" id="price-adder" style="width:fit-content;" > Add Price </button>
                        </div>

                        <div class="price-container">
                            @forelse($prices as $key => $price)
                            <div class="row flex-nowrap mb-2 main-row">
                                <div class="col-auto">
                                    <input type="text" class="form-control price" id="price-{{ $key }}" name="price[{{ $key }}]" value="{{ $price }}">
                                </div>
                                <div class="col-auto">
                                    <button type="button" class="btn btn-primary add-price"> + </button>
                                    <button type="button" class="btn btn-danger remove-price"> - </button>
                                </div>
                            </div>
                            @empty
                            @endforelse
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
    $(document).ready(function() {

        @if(count($prices) > 0)
            $('#price-adder').hide();
        @endif

        let content = `<div class="row flex-nowrap mb-2 main-row"><div class="col-auto"><input type="text" class="form-control price" id="price-0" name="price[0]"></div><div class="col-auto"><button type="button" class="btn btn-primary add-price"> + </button> <button type="button" class="btn btn-danger remove-price"> - </button></div></div>`;
        let lastElementIndex = {{ count($prices) > 0 ? count($prices) : 0 }};

        $(document).on('click', '#price-adder', function () {
            $('#price-adder').hide();
            $('.price-container').html(content);            
        });

        $(document).on('click', '.remove-price', function(event) {
            let count = $('.price-container div.main-row').length;

            if (count > 0) {
                $(this).closest(".main-row").remove();
                if (count === 1) {
                    $('#price-adder').show();   
                }
            }
        });

        $(document).on('click', '.add-price', function (event) {
            cloned = $('.price-container').find('div.main-row').eq(0);

            if (cloned.length) {
                cloned = cloned.clone();
                lastElementIndex++;

                cloned.find('.price').attr('id', `price-${lastElementIndex}`).attr('name', `price[${lastElementIndex}]`).val(null);

                cloned.find('label.error').remove();
                $('.price-container').append(cloned.get(0));

                cloned.find('.price').rules('add', {
                    required: true,
                    number: true,
                    min: 1,
                    messages: {
                        required: "Enter price.",
                        min: "Minimum price must be 1.",
                        number: "Enter valid price format."
                    }
                }); 
            }
        });

        $('#commission').validate({
            rules: {
                bonus : {
                    required: true,
                    min: 0,
                    number: true
                },
                seller_commission : {
                    required: true,
                    min: 0,
                    number: true
                },
                @forelse ($prices as $key => $value)
                    "price[{{ $key }}]" : {
                        required: true,
                        number: true,
                        min: 1,
                    },  
                @empty
                    "price[0]" : {
                        required: true,
                        min: 1,
                        number: true,
                    },                      
                @endforelse
            },
            messages: {
                bonus : {
                    required: "Enter bonus amount.",
                    min: "Bonus amount can\'t be in negative.",
                    number: "Enter valid format."
                },
                seller_commission : {
                    required: "Enter commission amount.",
                    min: "Commission amount can\'t be in negative.",
                    number: "Enter valid format."
                },
                @forelse ($prices as $key => $value)
                    "price[{{ $key }}]" : {
                        required: "Enter price.",
                        min: "Minimum price must be 1.",
                        number: "Enter valid price format."
                    },  
                @empty
                    "price[0]" : {
                        required: "Enter price.",
                        min: "Minimum price must be 1.",
                        number: "Enter valid price format."
                    },                      
                @endforelse
            }
        });
        
    });
</script>
@endsection