@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Edit </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Edit {{ $moduleName }}</h2>
<form action="{{ route('procurement-cost.update', $id) }}" method="POST" id="addPco"> @csrf @method('PUT')
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">


                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Category: <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="select2 select2-hidden-accessible category" style="width:100%" data-placeholder="Select a Category">
                            @forelse($categories as $cid => $category)
                            @if($loop->first)
                            <option value="" selected> --- Select a Category --- </option>
                            @endif
                            <option value="{{ $cid }}" @if($cid == $cost->category_id) selected @endif >{{ $category }}</option>
                            @empty
                            <option value="" selected> --- No Category Available --- </option>
                            @endforelse
                        </select>
                        @if ($errors->has('category'))
                            <span class="text-danger d-block">{{ $errors->first('category') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product: <span class="text-danger">*</span></label>
                        <select name="product" id="product" class="select2 select2-hidden-accessible product" style="width:100%" data-placeholder="Select a Product">
                            <option value="">Select Product </option>
                            @php
                                $cats = [];
                                if ($cost?->category?->status == 1) {
                                    $cats = $cost?->category?->product ?? [];
                                }
                            @endphp
                            @forelse ($cats as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->purchase_price }}"  @if($product->id == $cost->product_id) selected @endif > {{ $product->name }} </option>
                            @empty
                            <option value="" data-price="0" selected> --- No Product Available --- </option>
                            @endforelse
                        </select>
                        @if ($errors->has('product'))
                            <span class="text-danger d-block">{{ $errors->first('product') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Base Price: <span class="text-danger">*</span></label>
                        <input type="text" name="base_price" id="base_price" value="{{ old('base_price', $cost->base_price) }}" class="form-control" placeholder="Enter Base Price">
                        @if ($errors->has('base_price'))
                            <span class="text-danger d-block">{{ $errors->first('base_price') }}</span>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('procurement-cost.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
            <button type="submit" class="btn-primary f-500 f-14">Save changes</button>
        </div>
    </div>
</form>
@endsection

@section('script')
<script>
$(document).ready(function(){

    $(document).on('change', '.category', function (event) {
        let thisId = $(this).val();

        if (thisId !== '') {
            $.ajax({
                url: "{{ route('get-products-on-category') }}",
                type: 'POST',
                data: {
                    id: thisId
                },
                beforeSend: function () {
                    $('body').find('.LoaderSec').removeClass('d-none');
                },
                success: function (response) {
                    if (response !== '') {
                        $(`#product`).empty().append(response);
                        $(`#product`).select2({
                            width: '100%',
                            allowClear: true,
                            placeholder: "Select a Product"
                        });
                    }
                },
                complete: function () {
                    $('body').find('.LoaderSec').addClass('d-none');
                }
            });
        } else {
            $(`#product`).empty().append(`<option value="" selected> Select a Product </option>`);
            $(`#product`).select2({
                width: '100%',
                allowClear: true,
                placeholder: "Select a Product"
            });
        }
    })

    $("#addPco").validate({
        rules: {
            base_price: {
                required: true,
                number: true,
                min: 0
            },
            category: {
                required: true,
            },
            product: {
                required: true,
                remote: {
                    url: "{{ url('procurement-cost/check') }}",
                    type: "POST",
                    async: false,
                    data: {
                        product_id: function() {
                            return $("#product").val();
                        },
                        id: function () {
                            return "{{ $id }}"
                        }
                    }
                },
            }
        },
        messages: {
            base_price: {
                required: "Enter base price.",
                number: "Enter valid format.",
                min: "Base price can\'t be less than 0."
            },
            category: {
                required: "Select a category.",
            },
            product: {
                required: "Select a product.",
                remote: "Cost for this product is already added.",
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
});
</script>
@endsection
