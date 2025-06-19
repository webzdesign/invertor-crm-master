@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">Add </li>
@endsection

@section('content')

{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">Add {{ $moduleName }}</h2>
<form action="{{ route('products.store') }}" method="POST" id="addProduct" enctype="multipart/form-data"> @csrf
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Number : </label>
                        <input type="text" class="form-control" value="{{ $prodNo }}" readonly>
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Category : <span class="text-danger">*</span></label>
                        <select name="category" id="category" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Category ---">
                            @forelse($categories as $cid => $cname)
                                @if($loop->first)
                                <option value="" selected> --- Select a Category --- </option>
                                @endif
                                <option value="{{ $cid }}"> {{ $cname }} </option>
                            @empty                                
                                <option value=""> --- No Category Found --- </option>
                            @endforelse
                        </select>
                        @if ($errors->has('category'))
                            <span class="text-danger d-block">{{ $errors->first('category') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="name">Product Name : <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" placeholder="Enter product name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="web_sales_price">New Price : </label>
                        <input type="text" name="web_sales_price" id="web_sales_price" value="{{ old('web_sales_price') }}" class="form-control" placeholder="Enter Product Sales Price">
                        @if ($errors->has('web_sales_price'))
                            <span class="text-danger d-block">{{ $errors->first('web_sales_price') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="web_sales_old_price">Old Price :</label>
                        <input type="text" name="web_sales_old_price" id="web_sales_old_price" value="{{ old('web_sales_old_price') }}" class="form-control" placeholder="Enter Product Old Sales Price">
                        @if ($errors->has('web_sales_old_price'))
                            <span class="text-danger d-block">{{ $errors->first('web_sales_old_price') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Detail URL : <span class="text-danger">*</span></label>
                        <input type="text" readonly name="slug" id="slug" value="{{ old('slug') }}" class="form-control" placeholder="Product URL">
                        @if ($errors->has('slug'))
                            <span class="text-danger d-block">{{ $errors->first('slug') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="brand">Brand :</label>
                        <select name="brand" id="brand" class="select2 select2-hidden-accessible" data-placeholder="--- Select a Brand ---">
                        </select>
                        @if ($errors->has('brand'))
                            <span class="text-danger d-block">{{ $errors->first('brand') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="slider_title">Short Title :</label>
                        <input type="text" name="slider_title" id="slider_title" value="{{ old('slider_title') }}" class="form-control" placeholder="Product Short Title">
                        @if ($errors->has('slider_title'))
                            <span class="text-danger d-block">{{ $errors->first('slider_title') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="slider_content">Short Content :</label>
                        <input type="text" name="slider_content" id="slider_content" value="{{ old('slider_content') }}" class="form-control" placeholder="Product Short Content">
                        @if ($errors->has('slider_content'))
                            <span class="text-danger d-block">{{ $errors->first('slider_content') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="sku">SKU :</label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="form-control" placeholder="Product SKU">
                        @if ($errors->has('sku'))
                            <span class="text-danger d-block">{{ $errors->first('sku') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="gtin">GTIN :</label>
                        <input type="text" name="gtin" id="gtin" value="{{ old('gtin') }}" class="form-control" placeholder="Product GTIN">
                        @if ($errors->has('gtin'))
                            <span class="text-danger d-block">{{ $errors->first('gtin') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="mpn">MPN :</label>
                        <input type="text" name="mpn" id="mpn" value="{{ old('mpn') }}" class="form-control" placeholder="Product MPN">
                        @if ($errors->has('mpn'))
                            <span class="text-danger d-block">{{ $errors->first('mpn') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="youtube_video_url">Youtube Video Url :</label>
                        <input type="text" name="youtube_video_url" id="youtube_video_url" value="{{ old('youtube_video_url') }}" class="form-control" placeholder="Youtube Video Url">
                        @if ($errors->has('youtube_video_url'))
                            <span class="text-danger d-block">{{ $errors->first('youtube_video_url') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2" for="air_conditioner_capacity">Air Conditioner’s Capacity :</label>
                        <input type="text" name="air_conditioner_capacity" id="air_conditioner_capacity" value="{{ old('air_conditioner_capacity') }}" class="form-control" placeholder="Air Conditioner’s Capacity">
                        @if ($errors->has('air_conditioner_capacity'))
                            <span class="text-danger d-block">{{ $errors->first('air_conditioner_capacity') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md col-sm-12">
                </div>
            </div>

            <div class="row border-top border-bottom py-2 mb-2">
                <div class="col-lg-3">
                    <div id="capacity-container">
                        <div class="form-group capacity-group">
                            <label class="c-gr f-500 f-16 w-100 mb-2">Available Power Capacity :</label>
                            <div class="d-flex flex-wrap mb-2 available_power_capacity-input">
                                <input type="text" name="available_power_capacity[0]" class="form-control w-75" id="available_power_capacity" placeholder="Available Power Capacity">
                                <div class="input-group-btns ms-1">
                                    <button type="button" class="btn btn-primary addNewRow">
                                        +
                                    </button>
                                    <button type="button" class="btn btn-danger removeRow">
                                        -
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="row categoryFiltersOptions"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Product Description : </label>
                        <textarea name="description" class="form-control ckeditorField" id="description" cols="30" rows="10" placeholder="Enter product description">{{ old('description') }}</textarea>
                        @if ($errors->has('description'))
                            <span class="text-danger d-block">{{ $errors->first('description') }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 col-sm-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Shipping & Payment : </label>
                        <textarea name="shipping_and_payment" class="form-control ckeditorField" id="shipping_and_payment" cols="30" rows="10" placeholder="Enter Shipping & Payment Details">{{ old('shipping_and_payment') }}</textarea>
                        @if ($errors->has('shipping_and_payment'))
                            <span class="text-danger d-block">{{ $errors->first('shipping_and_payment') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('products.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
            <button type="submit" class="btn-primary f-500 f-14">Save</button>
        </div>
    </div>
</form>
@endsection

{{-- <script src="{{ asset('assets/ckeditor/ckeditor.js') }}"></script> --}}
@section('script')
<script>
$(document).ready(function(){

    $.validator.addMethod("noSpace", function(value, element) {
        return value.indexOf(" ") < 0; 
    }, "Space are not allowed");

    $('body').on('input', '#name', function(e) {
        var name = $(this).val();
        var slug = name.replace(/[^a-zA-Z0-9\s\+\-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-').toLowerCase();

        $('body').find('#slug').val(slug);
    });

    $("#addProduct").validate({
        rules: {
            category: {
                required: true,                
            },
            name: {
                required: true,                
            },
            slug: {
                required: true,
                remote: {
                    url: "{{ url('checkProductSlug') }}",
                    type: "POST",
                    async: false,
                    data: {
                        slug: function() {
                            return $("#slug").val();
                        }
                    }
                }
            },
            unique_number: {
                noSpace: true,
                remote: {
                    url: "{{ url('checkProduct') }}",
                    type: "POST",
                    async: false,
                    data: {
                        name: function() {
                            return $("#unique_number").val();
                        },
                    }
                },
            },
            pprice: {
                required: true,
                number: true,
                min: 0
            },
            web_sales_price: {
                number: true,
                min: 0
            },
            web_sales_old_price: {
                number: true,
                min: 0
            },
        },
        messages: {
            category: {
                required: "Select a category."
            },
            name: {
                required: "Product name is required."
            },
            slug: {
                required: "Slug is required.",
                remote: "Slug already exists.",
            },
            unique_number: {
                remote: "This product number is already exists.",
            },
            pprice: {
                required: "Purchase price is required.",
                number: "Enter valid price format.",
                min: "Price can not be in negative amount."
            },
            web_sales_price: {
                number: "Enter valid price format.",
                min: "Price can not be in negative amount."
            },
            web_sales_old_price: {
                number: "Enter valid price format.",
                min: "Price can not be in negative amount."
            },
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
    $(".ckeditorField").each(function() {
		// CKEDITOR.config.autoParagraph = false;
		// CKEDITOR.replace($(this).attr("id"), {
		// 	enterMode: CKEDITOR.ENTER_BR,
		// 	shiftEnterMode: CKEDITOR.ENTER_BR
		// });
        initEditor(`#${$(this).attr("id")}`);
	});

    $(document).on('click', '.addNewRow', function () {
        let currentGroup = $(this).closest('.available_power_capacity-input');
        let newGroup = currentGroup.clone();

        newGroup.find('input').val('');
        
        let inputLength = $('.available_power_capacity-input').length;
        newGroup.find('input').attr('name', `available_power_capacity[${inputLength}]`);

        currentGroup.after(newGroup);
    });

    $(document).on('click', '.removeRow', function () {
        let allGroups = $('#capacity-container .available_power_capacity-input');

        if (allGroups.length > 1) {
            $(this).closest('.available_power_capacity-input').remove();
        }
    });

    $(document).on('change', '#category', function () {
        let categoryId = $(this).val();
        let brandSelect = $('#brand');
        
        if (categoryId) {
            $.ajax({
                url: "{{ route('getBrandsByCatgeory') }}",
                type: "POST",
                data: {
                    category_id: categoryId,
                },
                success: function (response) {
                    brandSelect.attr('disabled',true);
                    if (response.success) {

                        if(response.brands.length > 0) {
                            
                            brandSelect.empty(); 
                            brandSelect.append('<option value="">--- Select a Brands ---</option>');
                            brandSelect.attr('disabled', false).trigger('change');
                            
                            $.each(response.brands, function (index, brand) {
                                brandSelect.append('<option value="' + brand.id + '">' + brand.name + '</option>');
                            });
                            
                        }

                        let FilterOptionsHtml = '';

                        if (response.filters_options) {
                            $('.categoryFiltersOptions').empty();
                            
                            $.each(response.filters_options, function (index, FilterOption) {
                                let multiple = '';
                                let nameAttr = 'category_filter_option_id[]';
                                if(FilterOption.selection == 1) {
                                    multiple = 'multiple';
                                    nameAttr = `category_filter_option_id[${index}][]`;
                                } 

                                FilterOptionsHtml += `<div class="col-md-4 col-sm-12 dynamicFilterOptions">
                                    <div class="form-group">
                                        <input type="hidden" name="category_filter_id[]" value="${FilterOption.id}">
                                        <label class="c-gr f-500 f-16 w-100 mb-2">${FilterOption.name} : </label>
                                            <select name="${nameAttr}" class="select2 select2-hidden-accessible category_filter_option_id" data-placeholder="--- Select a Option ---" ${multiple}>
                                            <option value="">--- Select a Option ---</option>`;

                                $.each(FilterOption.options, function (i, Option) {
                                    FilterOptionsHtml += `<option value="${Option.id}">${Option.value}</option>`;
                                });

                                FilterOptionsHtml += `</select>
                                    </div>
                                </div>`;
                            });

                            $('.category_filter_option_id').select2({width: '100%', allowClear: true}).val('').trigger('change').on("load", function(e) {}).trigger('load');

                            $('.categoryFiltersOptions').append(FilterOptionsHtml);
                            
                            $('.category_filter_option_id').select2({
                                width: '100%',
                                allowClear: true
                            }).on("load", function(e) {
                                $(this).prop('tabindex',0);
                            }).trigger('load');
                        } else {
                            $('.categoryFiltersOptions').empty();
                        }

                    } else {
                        console.log(response.message || "Something went wrong while fetching brands.");
                    }
                },
                error: function () {
                    console.log("Something went wrong while fetching brands.");
                }
            });
        } else {
            brandSelect.empty();
            brandSelect.attr('disabled', false);
            $('.categoryFiltersOptions').empty();
        }
    });

});
</script>
@endsection