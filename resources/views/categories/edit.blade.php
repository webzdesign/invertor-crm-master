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
<form action="{{ route('categories.update', $id) }}" method="POST" id="addCategory"> @csrf @method('PUT')
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Name : <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" class="form-control" placeholder="Enter name">
                        @if ($errors->has('name'))
                            <span class="text-danger d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                
                @if (isset($category->filters) && !$category->filters->isEmpty())
                   
                    @foreach ($category->filters as $filterKey => $filter)
                    <div class="col-12 main-filter-section">
                            <input type="hidden" name="selection_id" class="selection_id" value="{{ $filter->id }}">
                            <div class="form-group border rounded-2 p-3">
                                <label class="c-gr f-500 f-16 w-100 mb-2 border-bottom pb-3">
                                    Filter Options :
                                    <span class="btn btn-primary ms-1 add-main-filter-section">+</span>
                                    <span class="btn btn-danger ms-1 remove-main-filter-section">−</span>
                                </label>

                                <div class="filter-row pt-3">
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Name :</label>
                                        <div class="col-sm-10">
                                            <input type="hidden" name="selection_filter_id[]" value="{{ $filter->id }}">
                                            <input type="text" name="seclection_name[]" class="form-control seclection-name" value="{{ $filter->name }}" placeholder="Enter name">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Selection :</label>
                                        <div class="col-sm-10">
                                            <select name="selection[]" class="form-control select2 selection-selects" data-placeholder="--- Select a Selection ---">
                                                <option value="">--- Select a Selection ---</option>
                                                <option value="0" {{ $filter->selection == 0 ? 'selected' : '' }} >Single</option>
                                                <option value="1" {{ $filter->selection == 1 ? 'selected' : '' }} >Multiple</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-top pt-3">

                                    @if (isset($filter->options) && !$filter->options->isEmpty())
                                        @foreach ($filter->options as $optionKey => $option)
                                            
                                            <div class="filter-row mb-3 main-filter-value-section">
                                                <div class="row align-items-center">
                                                    <label class="col-sm-2 col-form-label">Value:</label>
                                                    <div class="col-sm-9">
                                                        <input type="hidden" name="filter_options_value_id[{{ $filterKey }}][{{$optionKey}}]" class="filter_options_value_id" value="{{ $option->id }}">
                                                        <input type="text" name="value[{{ $filterKey }}][{{$optionKey}}]" class="form-control sectionValue" value="{{$option->value}}" placeholder="Enter value">
                                                    </div>
                                                    <div class="col-sm-1 text-end">
                                                        <span class="btn btn-primary me-1 add-main-filter-value-section">+</span>
                                                        <span class="btn btn-danger remove-main-filter-value-section">−</span>
                                                    </div>
                                                </div>
                                            </div>

                                        @endforeach
                                    @else
                                            <div class="filter-row mb-3 main-filter-value-section">
                                                <div class="row align-items-center">
                                                    <label class="col-sm-2 col-form-label">Value:</label>
                                                    <div class="col-sm-9">
                                                        <input type="hidden" name="filter_options_value_id[0][]" class="filter_options_value_id" value="">
                                                        <input type="text" name="value[0][]" class="form-control sectionValue" value="" placeholder="Enter value">
                                                    </div>
                                                    <div class="col-sm-1 text-end">
                                                        <span class="btn btn-primary me-1 add-main-filter-value-section">+</span>
                                                        <span class="btn btn-danger remove-main-filter-value-section">−</span>
                                                    </div>
                                                </div>
                                            </div>
                                    @endif

                                </div>
                            </div>
                        </div>

                    @endforeach
                @else
                    <div class="col-12 main-filter-section">
                        <div class="form-group border rounded-2 p-3">
                            <label class="c-gr f-500 f-16 w-100 mb-2 border-bottom pb-3">
                                Filter Options :
                                <span class="btn btn-primary ms-1 add-main-filter-section">+</span>
                                <span class="btn btn-danger ms-1 remove-main-filter-section">−</span>
                            </label>

                            <div class="filter-row pt-3">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Name :</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="seclection_name[]" class="form-control seclection-name" placeholder="Enter name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Selection :</label>
                                    <div class="col-sm-10">
                                        <select name="selection[]" class="form-control select2 selection-selects" data-placeholder="--- Select a Selection ---">
                                            <option value="">--- Select a Selection ---</option>
                                            <option value="0">Single</option>
                                            <option value="1">Multiple</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <div class="filter-row mb-3 main-filter-value-section">
                                    <div class="row align-items-center">
                                        <label class="col-sm-2 col-form-label">Value:</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="value[0][]" class="form-control sectionValue" placeholder="Enter value">
                                        </div>
                                        <div class="col-sm-1 text-end">
                                            <span class="btn btn-primary me-1 add-main-filter-value-section">+</span>
                                            <span class="btn btn-danger remove-main-filter-value-section">−</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>        
                @endif
                
            </div>
            <input type="hidden" name="deleted_selection_id" class="deleted_selection_id" value="">
            <input type="hidden" name="deleted_values_id" class="deleted_values_id" value="">
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('categories.index') }}">
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

    $("#addCategory").validate({
        rules: {
            name: {
                required: true,
                remote: {
                    url: "{{ url('checkCategory') }}",
                    type: "POST",
                    async: false,
                    data: {
                        name: function() {
                            return $( "#name" ).val();
                        },
                        id: "{{ $id }}"
                    }
                },
            }
        },
        messages: {
            name: {
                required: "Name is required.",
                remote: "This name is already exists.",
            }
        },
        errorPlacement: function (error, element) {
            // Handle select2
            if (element.hasClass('select2-hidden-accessible')) {
                error.addClass('d-block text-danger');
                // Insert after the select2 container, not the hidden select
                error.insertAfter(element.next('.select2'));
            } else if (
                element.hasClass("sectionValue") ||
                element.hasClass("seclection-name") ||
                element.hasClass("selection-selects")
            ) {
                error.addClass('d-block text-danger');
                error.insertAfter(element);
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function (element) {
            $(element).removeClass('is-invalid');
        },
        submitHandler:function(form) {
            if(!this.beenSubmitted) {

                let isValid = true;

                // $('.main-filter-section').each(function (i, section) {
                //     let $section = $(section);
                //     let seclectionName = $section.find('input[name="seclection_name[]"]').val();

                //     if (seclectionName.trim() !== '') {
                //         $section.find('.main-filter-value-section').each(function () {
                //             let $valueInput = $(this).find('.sectionValue');
                //             if ($valueInput.val().trim() === '') {
                //                 isValid = false;
                //                 // $valueInput.addClass('is-invalid');
                //                 if ($valueInput.next('.invalid-feedback').length === 0) {
                //                     $valueInput.after('<div class="invalid-feedback d-block">Value is required.</div>');
                //                 }
                //             } else {
                //                 // $valueInput.removeClass('is-invalid');
                //                 $valueInput.next('.invalid-feedback').remove();
                //             }
                //         });
                //     }
                // });

                if (isValid) {
                    this.beenSubmitted = true;
                    $('button[type="submit"]').attr('disabled', true);
                    form.submit();
                }
            }
        }
    });

    $.validator.addClassRules('seclection-name', {
        required: true
    });
    $.validator.addClassRules('selection-selects', {
        required: true
    });
    $.validator.addClassRules('sectionValue', {
        required: true
    });

    $(document).on('click', '.add-main-filter-section', function () {
        let $currentSection = $(this).closest('.main-filter-section');
        let $clonedSection = $currentSection.clone();

        let MainSrno = $('.main-filter-section').length;
        let ValueSrno = $('.sectionValue').length;
        console.log($clonedSection.find('.seclection-name').attr('name', 'seclection_name[' + MainSrno + ']'));
        
        $clonedSection.find('.seclection-name').attr('name', 'seclection_name[' + MainSrno + ']');
        $clonedSection.find('.selection-selects').attr('name', 'selection[' + MainSrno + ']');

        $clonedSection.find('.sectionValue').each(function () {
            $(this).attr('name', 'value[' + MainSrno + ']['+ValueSrno+']');
        });

        if($clonedSection.find('.main-filter-value-section').length > 1){
            $clonedSection.find('.main-filter-value-section').slice(1).remove();
        }

        $clonedSection.find('.invalid-feedback').remove();
        $clonedSection.find('input').removeClass('is-invalid');
        $clonedSection.find('.error').remove();
        $clonedSection.find('input').val('');
        $clonedSection.find('.select2').select2({width: '100%', allowClear: true}).val('').trigger('change').on("load", function(e) {$(this).prop('tabindex',0);}).trigger('load');
        $clonedSection.find('span:nth-child(3)').remove();
        $clonedSection.find('span:nth-child(3)').remove();
        $currentSection.after($clonedSection);

        $('.select2').select2({
            width: '100%',
            allowClear: true
        }).on("load", function(e) {
            $(this).prop('tabindex',0);
        }).trigger('load');

        $clonedSection.find('.seclection-name').each(function () {
            $(this).rules('add', {
                required: true
            });
        });

        $clonedSection.find('.selection-selects').each(function () {
            $(this).rules('add', {
                required: true
            });
        });

        $clonedSection.find('.sectionValue').each(function () {
            $(this).rules('add', {
                required: true
            });
        });
    });

    let selectionDeletedIds = [];
    $(document).on('click', '.remove-main-filter-section', function () {
        let $currentSection = $(this).closest('.main-filter-section');
        let id = $currentSection.find('.selection_id').val()

        if ($('.main-filter-section').length > 1) {
            if(id) {
                if (id && !selectionDeletedIds.includes(id)) {
                    selectionDeletedIds.push(id);
                    $('.deleted_selection_id').val(selectionDeletedIds.join(','));
                }
            }
            $(this).closest('.main-filter-section').remove();
        }  else {  
            
            $('.main-filter-section').each(function (mainIndex) {
                const $section = $(this);
                $section.find('input').removeClass('is-invalid');
                $section.find('.error').remove();
                $section.find('.seclection-name').attr('name', 'seclection_name[' + mainIndex + ']');
                $section.find('.selection-selects').attr('name', 'selection[' + mainIndex + ']');

                // Update value[] names inside this section
                $section.find('.sectionValue').attr('name', 'value[' + mainIndex + '][]');
            });
            
            if($currentSection.find('.main-filter-value-section').length > 1){
                $currentSection.find('.main-filter-value-section').slice(1).remove();
            }

            if(id) {
                if (id && !selectionDeletedIds.includes(id)) {
                    selectionDeletedIds.push(id);
                    $('.deleted_selection_id').val(selectionDeletedIds.join(','));
                }
            }

            $currentSection.find('input').val('');
            $currentSection.find('.select2').select2({width: '100%', allowClear: true}).val('').trigger('change').on("load", function(e) {$(this).prop('tabindex',0);}).trigger('load');
            $currentSection.find('span:nth-child(3)').remove();
            $currentSection.find('span:nth-child(3)').remove();

            $('.select2').select2({
                width: '100%',
                allowClear: true
            }).on("load", function(e) {
                $(this).prop('tabindex',0);
            }).trigger('load');
        }
    });

    $(document).on('click', '.add-main-filter-value-section', function () {
        let $valueRow = $(this).closest('.main-filter-value-section');
        let $clonedRow = $valueRow.clone();
        
        let ValueSrno = $('.sectionValue').length;
        let $section = $(this).closest('.main-filter-section');
        let sectionIndex = $('.main-filter-section').index($section);
        $clonedRow.find('.sectionValue').attr('name', 'value[' + sectionIndex + ']['+ValueSrno+']');

        $clonedRow.find('.invalid-feedback, .error').remove();
        $clonedRow.find('input').removeClass('is-invalid');
        $clonedRow.find('input').val('');
        $valueRow.after($clonedRow);
    });

    let deletedIds = [];
    $(document).on('click', '.remove-main-filter-value-section', function () {
        let $section = $(this).closest('.main-filter-section');
        let $valueSection = $(this).closest('.main-filter-value-section');
        if ($section.find('.main-filter-value-section').length > 1) {
           let id = $valueSection.find('.filter_options_value_id').val()

            if(id) {
                if (id && !deletedIds.includes(id)) {
                    deletedIds.push(id);
                    $('.deleted_values_id').val(deletedIds.join(','));
                }
            }
            
            $(this).closest('.main-filter-value-section').remove();
        } else {
            let id = $valueSection.find('.filter_options_value_id').val()

            if(id) {
                if (id && !deletedIds.includes(id)) {
                    deletedIds.push(id);
                    $('.deleted_values_id').val(deletedIds.join(','));
                }
            }

            $valueSection.find('.filter_options_value_id').val('');
            $valueSection.find('.sectionValue').val('');
        }
    });

});
</script>
@endsection
