@forelse($documents as $doc)
<div class="col-4">
    <div class="form-group">
        <label class="c-gr f-500 f-16 w-100 mb-2">{!! $doc->name !!} 
            @if(!empty($doc->description))
            <a data-toggle="tooltip" class="deleteBtn" data-html="true" title="{!! $doc->description !!}">
                <i class='fa fa-info-circle' aria-hidden='true' style="color: black;margin-left:5px;"></i>
            </a>
            @endif
            : @if($doc->is_required) <span class="text-danger">*</span> @endif
        </label>
        <input name="document[{{ $doc->id }}][]" id="doc-{{ $doc->id }}" type="file" @if($doc->maximum_upload_count != '1') multiple @endif class="form-control">
        <span class="text-danger f-400 f-14">
            @error("document[{{ $doc->id }}]")
                <span class="text-danger d-block">{{ $message }}</span>
            @enderror
        </span>
    </div>
</div>
@empty
@endif