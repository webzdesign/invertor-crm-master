@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36"> Documents </li>
@endsection
@section('css')

<link href="{{ asset('assets/css/summernote.min.css') }}" rel="stylesheet">
<style>
    .note-btn {
        margin-right: 5px;
        border: none!important;
    }

    .note-editing-area {
        padding-bottom: 50px;
    }
    .note-resizebar {
        display: none; /* Hide the resize bar */
    }

    .note-editing-area {
        padding-bottom: 0px!important;
    }
</style>
@endsection

@section('content')

<form action="{{ route('save-required-documents', $id) }}" method="POST" id="RoleRequiredDocumentForm"> @csrf @method('PUT')
<div class="cardsBody py-0 mb-3 roleForm">
    <input type="hidden" name="role" value="{{ $role->id }}">
    <label class="c-gr f-500 f-16 w-100 mb-2">Required documents for <strong>{{ $role->name }}</strong> registration : </label>

    {{-- Container --}}
    <div class="upsertable" id="sortable">
        @forelse($role->documents as $key => $document)
        <input type="hidden" name="id[{{ $key }}]" value="{{ $document->id }}">
        <div class="d-flex mb-3 clonable">
            <div class="PlBox w-100">

                <textarea name="document_name[{{ $key }}]" class="doc-name" data-indexid="{{ $key }}">{{ $document->name }}</textarea>
                <div class="mt-2 desc-container @if(empty($document->description)) d-none @endif" id="desc-container-{{ $key }}">
                    <textarea name="document_description[{{ $key }}]" class="doc-desc" data-indexid="{{ $key }}">{{ $document->description }}</textarea>
                </div>

                <div class="roleFormSub">
                    <div class="d-flex mt-4 formLbl w-100 justify-content-between">
                        <label class="f-14-500">Allow only specific file types</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input doc-allow-only-specific-file-type" type="checkbox" id="doc-allow-only-specific-file-type-{{ $key }}" data-indexid="{{ $key }}" name="allow_only_specific_file_format[{{ $key }}]" @if($document->allow_only_specific_file_format) checked @endif>
                        </div>
                    </div>
                    @php
                        $allowedFileTypes = array_filter(explode(',', $document->allowed_file));
                    @endphp
                    <div class="d-flex mt-4 formLbl w-100 justify-content-between file-type-section @if(!$document->allow_only_specific_file_format) d-none @endif" id="file-type-section-{{ $key }}" data-indexid="{{ $key }}">
                        <div class="row">
                            <div class="col-6">
                                <input id="doc-doc-{{ $key }}" type="checkbox" class="form-check-input doc-doc-inp" name="doc_type[{{ $key }}][]" value="1" data-indexid="{{ $key }}" @if(in_array(1, $allowedFileTypes)) checked @endif>
                                <label for="doc-doc-{{ $key }}" class="doc-doc-lab" data-indexid="{{ $key }}">Document</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-pres-{{ $key }}" type="checkbox" class="form-check-input doc-pres-inp" name="doc_type[{{ $key }}][]" value="2" data-indexid="{{ $key }}" @if(in_array(2, $allowedFileTypes)) checked @endif>
                                <label for="doc-pres-{{ $key }}" class="doc-pres-lab" data-indexid="{{ $key }}">Presentation</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-spre-{{ $key }}" type="checkbox" class="form-check-input doc-spre-inp" name="doc_type[{{ $key }}][]" value="3" data-indexid="{{ $key }}" @if(in_array(3, $allowedFileTypes)) checked @endif>
                                <label for="doc-spre-{{ $key }}" class="doc-spre-lab" data-indexid="{{ $key }}">Spreadsheet</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-draw-{{ $key }}" type="checkbox" class="form-check-input doc-draw-inp" name="doc_type[{{ $key }}][]" value="4" data-indexid="{{ $key }}" @if(in_array(4, $allowedFileTypes)) checked @endif>
                                <label for="doc-draw-{{ $key }}" class="doc-draw-lab" data-indexid="{{ $key }}">Drawing</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-pdf-{{ $key }}" type="checkbox" class="form-check-input doc-pdf-inp" name="doc_type[{{ $key }}][]" value="5" data-indexid="{{ $key }}" @if(in_array(5, $allowedFileTypes)) checked @endif>
                                <label for="doc-pdf-{{ $key }}" class="doc-pdf-lab" data-indexid="{{ $key }}">PDF</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-img-{{ $key }}" type="checkbox" class="form-check-input doc-img-inp" name="doc_type[{{ $key }}][]" value="6" data-indexid="{{ $key }}" @if(in_array(6, $allowedFileTypes)) checked @endif>
                                <label for="doc-img-{{ $key }}" class="doc-img-lab" data-indexid="{{ $key }}">Image</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-vid-{{ $key }}" type="checkbox" class="form-check-input doc-vid-inp" name="doc_type[{{ $key }}][]" value="7" data-indexid="{{ $key }}" @if(in_array(7, $allowedFileTypes)) checked @endif>
                                <label for="doc-vid-{{ $key }}" class="doc-vid-lab" data-indexid="{{ $key }}">Video</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-aud-{{ $key }}" type="checkbox" class="form-check-input doc-aud-inp" name="doc_type[{{ $key }}][]" value="8" data-indexid="{{ $key }}" @if(in_array(8, $allowedFileTypes)) checked @endif>
                                <label for="doc-aud-{{ $key }}" class="doc-aud-lab" data-indexid="{{ $key }}">Audio</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-4 formLbl w-100 justify-content-between">
                        <label class="f-14-500">Maximum number of files</label>
                        <div>
                            <div class="dropdown">
                                <input type="hidden" name="doc_max_file_count[{{ $key }}]" value="{{ $document->maximum_upload_count }}" class="doc-max-file-count" id="doc-max-file-count-{{ $key }}">
                                <button class="btn dropdown-toggle doc-max-file-count-display" type="button" data-bs-toggle="dropdown" id="doc-max-file-count-display-{{ $key }}" data-indexid="{{ $key }}"> {{ $document->maximum_upload_count }} </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-item cursor-pointer file-count-options" data-indexid="{{ $key }}" data-value="1" @if($document->maximum_upload_count == '1') style="background: #15283c;color:white;" @endif>1</li>
                                    <li class="dropdown-item cursor-pointer file-count-options" data-indexid="{{ $key }}" data-value="5" @if($document->maximum_upload_count == '5') style="background: #15283c;color:white;" @endif>5</li>
                                    <li class="dropdown-item cursor-pointer file-count-options" data-indexid="{{ $key }}" data-value="10" @if($document->maximum_upload_count == '10') style="background: #15283c;color:white;" @endif>10</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3 formLbl w-100 justify-content-between">
                        <label class="f-14-500">Maximum file size</label>
                        <div>
                            <div class="dropdown">
                                <input type="hidden" name="doc_max_file_size[{{ $key }}]" value="{{ $document->maximum_upload_size }}" class="doc-max-file-size" id="doc-max-file-size-{{ $key }}">
                                <button class="btn dropdown-toggle doc-max-file-size-display" type="button" data-bs-toggle="dropdown" id="doc-max-file-size-display-{{ $key }}" data-indexid="{{ $key }}"> {{ Helper::formatBytes($document->maximum_upload_size) }} </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="{{ $key }}" data-value="1024" @if($document->maximum_upload_size == '1024') style="background: #15283c;color:white;" @endif >1 MB</li>
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="{{ $key }}" data-value="10240" @if($document->maximum_upload_size == '10240') style="background: #15283c;color:white;" @endif >10 MB</li>
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="{{ $key }}" data-value="102400" @if($document->maximum_upload_size == '102400') style="background: #15283c;color:white;" @endif >100 MB</li>
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="{{ $key }}" data-value="1024000" @if($document->maximum_upload_size == '1024000') style="background: #15283c;color:white;" @endif>1 GB</li>
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="{{ $key }}" data-value="10240000" @if($document->maximum_upload_size == '10240000') style="background: #15283c;color:white;" @endif>10 GB</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="formFooter border-top mt-3 pt-3 pb-2 d-flex aling-items-center justify-content-end">
                    <button type="button" class="bg-transparent border-0 defaultBtn addNewRow doc-copy" title="Copy" data-indexid="{{ $key }}">
                        <i class="fa fa-copy"></i>
                    </button>
                    <button type="button" class="bg-transparent border-0 defaultBtn ms-2 me-3 doc-delete" title="Remove" data-indexid="{{ $key }}">
                        <i class="fa fa-trash"></i>
                    </button>
                    <div class="form-check form-switch d-flex flex-row-reverse align-items-center border-start ps-3">
                        <input class="form-check-input doc-required" type="checkbox" id="doc-required-{{ $key }}" name="is_required[{{ $key }}]" data-indexid="{{ $key }}" @if($document->is_required) checked @endif >
                        <label class="form-check-label mb-0 doc-required-lab" for="doc-required-{{ $key }}">Required</label>
                    </div>
                    <div class="dropdown ms-1">
                        <button type="button" class="bg-transparent border-0 defaultBtn ms-2 dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <label class="ms-2 c-7b f-14">Show</label>
                            <li class="dropdown-item cursor-pointer doc-should-show-description" id="doc-desc-shower-{{ $key }}" data-indexid="{{ $key }}"> @if(!empty($document->description)) <i class="fa fa-check"></i> @endif Description</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            <button type="button" class="btnAdd btn-primary f-500 f-16 ms-2 addNewRow">
                +
            </button>
        </div>
        @empty
        <div class="d-flex mb-3 clonable">
            <div class="PlBox w-100">

                <textarea name="document_name[0]" class="doc-name" data-indexid="0"></textarea>
                <div class="mt-2 desc-container d-none" id="desc-container-0">
                    <textarea name="document_description[0]" class="doc-desc" data-indexid="0"></textarea>
                </div>

                <div class="roleFormSub">
                    <div class="d-flex mt-4 formLbl w-100 justify-content-between">
                        <label class="f-14-500">Allow only specific file types</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input doc-allow-only-specific-file-type" type="checkbox" id="doc-allow-only-specific-file-type-0" data-indexid="0" name="allow_only_specific_file_format[0]">
                        </div>
                    </div>
                    <div class="d-flex mt-4 formLbl w-100 justify-content-between file-type-section d-none" id="file-type-section-0" data-indexid="0">
                        <div class="row">
                            <div class="col-6">
                                <input id="doc-doc-0" type="checkbox" class="form-check-input doc-doc-inp" name="doc_type[0][]" value="1" data-indexid="0">
                                <label for="doc-doc-0" class="doc-doc-lab" data-indexid="0">Document</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-pres-0" type="checkbox" class="form-check-input doc-pres-inp" name="doc_type[0][]" value="2" data-indexid="0">
                                <label for="doc-pres-0" class="doc-pres-lab" data-indexid="0">Presentation</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-spre-0" type="checkbox" class="form-check-input doc-spre-inp" name="doc_type[0][]" value="3" data-indexid="0">
                                <label for="doc-spre-0" class="doc-spre-lab" data-indexid="0">Spreadsheet</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-draw-0" type="checkbox" class="form-check-input doc-draw-inp" name="doc_type[0][]" value="4" data-indexid="0">
                                <label for="doc-draw-0" class="doc-draw-lab" data-indexid="0">Drawing</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-pdf-0" type="checkbox" class="form-check-input doc-pdf-inp" name="doc_type[0][]" value="5" data-indexid="0">
                                <label for="doc-pdf-0" class="doc-pdf-lab" data-indexid="0">PDF</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-img-0" type="checkbox" class="form-check-input doc-img-inp" name="doc_type[0][]" value="6" data-indexid="0">
                                <label for="doc-img-0" class="doc-img-lab" data-indexid="0">Image</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-vid-0" type="checkbox" class="form-check-input doc-vid-inp" name="doc_type[0][]" value="7" data-indexid="0">
                                <label for="doc-vid-0" class="doc-vid-lab" data-indexid="0">Video</label>
                            </div>
                            <div class="col-6">
                                <input id="doc-aud-0" type="checkbox" class="form-check-input doc-aud-inp" name="doc_type[0][]" value="8" data-indexid="0">
                                <label for="doc-aud-0" class="doc-aud-lab" data-indexid="0">Audio</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-4 formLbl w-100 justify-content-between">
                        <label class="f-14-500">Maximum number of files</label>
                        <div>
                            <div class="dropdown">
                                <input type="hidden" name="doc_max_file_count[0]" value="1" class="doc-max-file-count" id="doc-max-file-count-0">
                                <button class="btn dropdown-toggle doc-max-file-count-display" type="button" data-bs-toggle="dropdown" id="doc-max-file-count-display-0" data-indexid="0"> 1 </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-item cursor-pointer file-count-options" data-indexid="0" data-value="1" style="background: #15283c;color:white;">1</li>
                                    <li class="dropdown-item cursor-pointer file-count-options" data-indexid="0" data-value="5">5</li>
                                    <li class="dropdown-item cursor-pointer file-count-options" data-indexid="0" data-value="10">10</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3 formLbl w-100 justify-content-between">
                        <label class="f-14-500">Maximum file size</label>
                        <div>
                            <div class="dropdown">
                                <input type="hidden" name="doc_max_file_size[0]" value="10240" class="doc-max-file-size" id="doc-max-file-size-0">
                                <button class="btn dropdown-toggle doc-max-file-size-display" type="button" data-bs-toggle="dropdown" id="doc-max-file-size-display-0" data-indexid="0"> 10 MB </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="0" data-value="1024">1 MB</li>
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="0" data-value="10240" style="background: #15283c;color:white;" >10 MB</li>
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="0" data-value="102400">100 MB</li>
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="0" data-value="1024000">1 GB</li>
                                    <li class="dropdown-item cursor-pointer file-size-options" data-indexid="0" data-value="10240000">10 GB</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="formFooter border-top mt-3 pt-3 pb-2 d-flex aling-items-center justify-content-end">
                    <button type="button" class="bg-transparent border-0 defaultBtn addNewRow doc-copy" title="Copy" data-indexid="0">
                        <i class="fa fa-copy"></i>
                    </button>
                    <button type="button" class="bg-transparent border-0 defaultBtn ms-2 me-3 doc-delete" title="Remove" data-indexid="0">
                        <i class="fa fa-trash"></i>
                    </button>
                    <div class="form-check form-switch d-flex flex-row-reverse align-items-center border-start ps-3">
                        <input class="form-check-input doc-required" type="checkbox" id="doc-required-0" name="is_required[0]" data-indexid="0">
                        <label class="form-check-label mb-0 doc-required-lab" for="doc-required-0">Required</label>
                    </div>
                    <div class="dropdown ms-1">
                        <button type="button" class="bg-transparent border-0 defaultBtn ms-2 dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <label class="ms-2 c-7b f-14">Show</label>
                            <li class="dropdown-item cursor-pointer doc-should-show-description" id="doc-desc-shower-0" data-indexid="0"> Description</li>
                        </ul>
                    </div>
                    
                </div>
            </div>
            <button type="button" class="btnAdd btn-primary f-500 f-16 ms-2 addNewRow">
                +
            </button>
        </div>
        @endforelse
    </div>
    {{-- Container --}}
    
</div>

<div class="card">
    <div class="card-footer">
        <center>
            <button type="submit" class="btn btn-primary"> Save </button>
            <a href="{{ route('roles.index') }}">
                <button type="button" class="btn-default f-500 f-14"> Back </button>
            </a>
        </center>
    </div>
</div>
</form>

@endsection

@section('script')

<script src="{{ asset('assets/js/summernote.min.js') }}"></script>
<script>
    var lastElementIndex = {{ count($role->documents) > 0 ? count($role->documents) : 0 }};
    var editorForDocName = {
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
            ['insert', ['link']]
        ],
        placeholder: 'Document name'
    };
    var editorForDocDesc = {
        toolbar: [
            ['style', ['bold', 'italic', 'underline']],
            ['insert', ['link']],
            ['list', ['ol', 'ul']]
        ],
        placeholder: 'Document description'
    };

    $(document).ready(function(){

    // $( "#sortable" ).sortable();

    $(document).on('click', '.addNewRow', function (event) {

        cloned = $('.upsertable').find('.clonable').eq(0).clone();
        lastElementIndex++;

        if ($(this).hasClass('doc-copy')) {
            if ($(this).closest('.clonable').length > 0) {
                cloned = $(this).closest('.clonable').clone();
            }
        }

        if (cloned.find('.doc-name').next().hasClass('note-editor')) {
            cloned.find('.doc-name').next().remove();
            cloned.find('.doc-name').summernote(editorForDocName);
            cloned.find('.note-placeholder').css('display', 'block');
        }

        if (cloned.find('.doc-desc').next().hasClass('note-editor')) {
            cloned.find('.doc-desc').next().remove();
            cloned.find('.doc-desc').summernote(editorForDocDesc); 
            cloned.find('.note-placeholder').css('display', 'block');
        }

        cloned.find('.doc-name').attr('data-indexid', lastElementIndex).attr('name', `document_name[${lastElementIndex}]`);
        cloned.find('.doc-desc').attr('data-indexid', lastElementIndex).attr('name', `document_description[${lastElementIndex}]`);
        cloned.find('.doc-allow-only-specific-file-type').attr('data-indexid', lastElementIndex).attr('id', `doc-allow-only-specific-file-type-${lastElementIndex}`).attr('name', `allow_only_specific_file_format[${lastElementIndex}]`);
        cloned.find('.doc-max-file-count').attr('id', `doc-max-file-count-${lastElementIndex}`).attr('name', `doc_max_file_count[${lastElementIndex}]`);
        cloned.find('.doc-max-file-size').attr('id', `doc-max-file-size-${lastElementIndex}`).attr('name', `doc_max_file_size[${lastElementIndex}]`);
        cloned.find('.doc-max-file-count-display').attr('data-indexid', lastElementIndex).attr('id', `doc-max-file-count-display-${lastElementIndex}`);
        cloned.find('.doc-max-file-size-display').attr('data-indexid', lastElementIndex).attr('id', `doc-max-file-size-display-${lastElementIndex}`);
        cloned.find('.doc-copy').attr('data-indexid', lastElementIndex);
        cloned.find('.doc-delete').attr('data-indexid', lastElementIndex);
        cloned.find('.doc-is-required').attr('data-indexid', lastElementIndex);
        cloned.find('.desc-container').attr('id', `desc-container-${lastElementIndex}`);

        cloned.find('.file-count-options').attr('data-indexid', lastElementIndex);
        cloned.find('.file-size-options').attr('data-indexid', lastElementIndex);

        cloned.find('.doc-should-show-description').attr('data-indexid', lastElementIndex).attr('id', `doc-desc-shower-${lastElementIndex}`);
        cloned.find('.file-type-section').attr('data-indexid', lastElementIndex).attr('id', `file-type-section-${lastElementIndex}`);

        cloned.find('.doc-required').attr('data-indexid', lastElementIndex).attr('id', `doc-required-${lastElementIndex}`).attr('name', `is_required[${lastElementIndex}]`);
        cloned.find('.doc-required-lab').attr('for', `doc-required-${lastElementIndex}`);

        cloned.find('.doc-doc-inp').attr('data-indexid', lastElementIndex).attr('id', `doc-doc-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        cloned.find('.doc-doc-lab').attr('data-indexid', lastElementIndex).attr('for', `doc-doc-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);

        cloned.find('.doc-pres-inp').attr('data-indexid', lastElementIndex).attr('id', `doc-pres-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        cloned.find('.doc-pres-lab').attr('data-indexid', lastElementIndex).attr('for', `doc-pres-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);

        cloned.find('.doc-spre-inp').attr('data-indexid', lastElementIndex).attr('id', `doc-spre-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        cloned.find('.doc-spre-lab').attr('data-indexid', lastElementIndex).attr('for', `doc-spre-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        
        cloned.find('.doc-draw-inp').attr('data-indexid', lastElementIndex).attr('id', `doc-draw-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        cloned.find('.doc-draw-lab').attr('data-indexid', lastElementIndex).attr('for', `doc-draw-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);

        cloned.find('.doc-pdf-inp').attr('data-indexid', lastElementIndex).attr('id', `doc-pdf-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        cloned.find('.doc-pdf-lab').attr('data-indexid', lastElementIndex).attr('for', `doc-pdf-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);

        cloned.find('.doc-img-inp').attr('data-indexid', lastElementIndex).attr('id', `doc-img-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        cloned.find('.doc-img-lab').attr('data-indexid', lastElementIndex).attr('for', `doc-img-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);

        cloned.find('.doc-vid-inp').attr('data-indexid', lastElementIndex).attr('id', `doc-vid-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        cloned.find('.doc-vid-lab').attr('data-indexid', lastElementIndex).attr('for', `doc-vid-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);

        cloned.find('.doc-aud-inp').attr('data-indexid', lastElementIndex).attr('id', `doc-aud-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);
        cloned.find('.doc-aud-lab').attr('data-indexid', lastElementIndex).attr('for', `doc-aud-${lastElementIndex}`).attr('name', `doc_type[${lastElementIndex}][]`);

        cloned.find('label.doc-name-error').remove();
        cloned.find('label.doc-type-error').remove();

        if ($(this).hasClass('doc-copy')) {
            cloned.find('.note-placeholder').css('display', 'none');
            //copied
        } else {
            cloned.find('.doc-name').val('');
            cloned.find('.doc-desc').val('');
            cloned.find('.doc-name').next().find('.note-editable').empty();
            cloned.find('.doc-desc').next().find('.note-editable').empty();
            cloned.find('.doc-allow-only-specific-file-type').prop('checked', false);
            cloned.find('.file-type-section').addClass('d-none');
            cloned.find('.desc-container').addClass('d-none');
            cloned.find('.doc-should-show-description').html('Description');
            cloned.find('.doc-max-file-count-display').text('1');
            cloned.find('.doc-max-file-count').val(1);
            cloned.find('.doc-max-file-size-display').text('10 MB');
            cloned.find('.doc-max-file-size').val(10240);        

            if (Array.isArray(cloned.find(`.file-type-section`).children().find('input[type="checkbox"]').toArray())) {
                cloned.find(`.file-type-section`).children().find('input[type="checkbox"]').toArray().forEach(element => {
                    $(element).prop('checked', false);
                });
            }

            cloned.find('.file-count-options').not(cloned.find('.file-count-options').eq(0)).css({
                'background' : 'transparent',
                'color' : '#212529'                
            });

            cloned.find('.file-size-options').not(cloned.find('.file-count-options').eq(1)).css({
                'background' : 'transparent',
                'color' : '#212529'                
            });

            cloned.find('.file-count-options').eq(0).css({
                'background' : '#15283c',
                'color' : 'white'                
            });

            cloned.find('.file-size-options').eq(1).css({
                'background' : '#15283c',
                'color' : 'white'                
            });

            cloned.find('.doc-required').prop('checked', false);
        }

        $('.upsertable').append(cloned.get(0));
    });

    $(document).on('click', '.doc-delete', function (event) {
        if ($('.upsertable .clonable').length > 1) {
            let element = $(this).closest('.clonable');
        
            Swal.fire({
                title: 'Are you sure want to remove?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.value) {
                    $(element).remove();
                }
            });
        }
    });

    $(document).on('click', '.doc-should-show-description', function (event) {
        let index = $(this).attr('data-indexid');

        if (isNumeric(index) && index >= 0 && $(`#desc-container-${index}`).length > 0) {
            if($(`#desc-container-${index}`).find('.doc-desc').length > 0) {
                $(`#desc-container-${index}`).find('.doc-desc').summernote('code', '');
                $(`#desc-container-${index}`).find('.doc-desc').val('');
            }

            if ($(`#desc-container-${index}`).hasClass('d-none')) {
                $(this).html(`<i class="fa fa-check"> </i>  Description`);
                $(`#desc-container-${index}`).removeClass('d-none')
            } else {
                $(this).html(`Description`);
                $(`#desc-container-${index}`).addClass('d-none')
            }
        }
    });

    $(document).on('change', '.doc-allow-only-specific-file-type', function (event) {
        let index = $(this).attr('data-indexid');

        if ($(`#file-type-section-${index}`).length > 0) {

            if (Array.isArray($(`#file-type-section-${index}`).children().find('input[type="checkbox"]').toArray())) {
                $(`#file-type-section-${index}`).children().find('input[type="checkbox"]').toArray().forEach(element => {
                    $(element).prop('checked', false);
                });
            }

            if ($(this).is(':checked')) {
                $(`#file-type-section-${index}`).removeClass('d-none');
            } else {
                $(`#file-type-section-${index}`).addClass('d-none');
            }
        }
    });

    $(document).on('click', '.file-count-options', function (event) {
        let index = $(this).attr('data-indexid');
        let text = $(this).text();
        let value = $(this).attr('data-value');

        if (isNumeric(index) && index >= 0) {
            $(this).css({
                'background' : '#15283c',
                'color' : 'white'
            });
            $(this).siblings().css({
                'background' : 'transparent',
                'color' : '#212529'                
            });

            if ($(`#doc-max-file-count-display-${index}`).length > 0) {
                $(`#doc-max-file-count-display-${index}`).text(text);
            }

            if ($(`#doc-max-file-count-${index}`).length > 0) {
                $(`#doc-max-file-count-${index}`).val(value);
            }
        }
    });

    $(document).on('click', '.file-size-options', function (event) {
        let index = $(this).attr('data-indexid');
        let text = $(this).text();
        let value = $(this).attr('data-value');

        if (isNumeric(index) && index >= 0) {
            $(this).css({
                'background' : '#15283c',
                'color' : 'white'
            });
            $(this).siblings().css({
                'background' : 'transparent',
                'color' : '#212529'                
            })

            if ($(`#doc-max-file-size-display-${index}`).length > 0) {
                $(`#doc-max-file-size-display-${index}`).text(text);
            }

            if ($(`#doc-max-file-size-${index}`).length > 0) {
                $(`#doc-max-file-size-${index}`).val(value);
            }
        }
    });

    $('#RoleRequiredDocumentForm').validate({
        submitHandler: function (form, event) {
            event.preventDefault();

            let isFormDirty = false;

            if (Array.isArray($('.doc-name').toArray()) && $('.doc-name').toArray().length > 0) {
                $('.doc-name').toArray().forEach(element => {
                    let content = $(element).summernote('code');                    
                    content = content.replace(/<\/?[^>]+(>|$)/g, "").replace(/&nbsp;/g, " ").trim();

                    if (content === '' || $(element).summernote('isEmpty')) {
                        $(`<label class="error doc-name-error"> Please enter document name </label>`).insertAfter($(element).next());
                        isFormDirty = true;
                    }
                });
            }

            if (Array.isArray($('.doc-allow-only-specific-file-type').toArray()) && $('.doc-allow-only-specific-file-type').toArray().length > 0) {
                $('.doc-allow-only-specific-file-type').toArray().forEach(element => {
                    if ($(element).is(':checked')) {
                        let tempIndex = $(element).attr('data-indexid');
                        if (isNumeric(tempIndex) && tempIndex >= 0) {
                            if (Array.isArray($(`input[name="doc_type[${tempIndex}][]"]`).toArray()) && $(`input[name="doc_type[${tempIndex}][]"]`).toArray().length > 0) {
                                let isAnyChecked = false;
                                $(`input[name="doc_type[${tempIndex}][]"]`).toArray().forEach(checkboxInputs => {
                                    if ($(checkboxInputs).is(':checked')) {
                                        isAnyChecked = true;
                                    }
                                });
                                if (isAnyChecked === false) {
                                    $(`<label class="error doc-type-error"> Please check atleast an option </label>`).insertAfter($(`#file-type-section-${tempIndex}`));
                                    isFormDirty = true;
                                }
                            }
                        }
                    }
                });
            }

            if (isFormDirty === false) {
                form.submit();
            }
        }
    });

    $('.doc-name').summernote(editorForDocName);
    $('.doc-desc').summernote(editorForDocDesc);

    });
</script>
@endsection