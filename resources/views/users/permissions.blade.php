<div class="row">
    @foreach ($permission as $key => $value)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-3 permission-listing" data-permissionlabel="{{ $key }}">
            <div class="PlBox">
                @foreach ($value as $k => $v)
                    @if ($loop->first)
                        <li class="list-group-item inline bg-transparent border-0 p-0 mb-2">
                            <label class="c-gr w-100 mb-2 f-14">
                                @if($roleId != 1)
                                <input type="checkbox" class="form-check-input selectDeselect" @if($roleId == 1) checked disabled @endif >
                                @endif
                                <span class="c-primary f-700">{{ Helper::spaceBeforeCap($v->model) }}</span>
                            </label>
                        </li>
                    @endif
                    <li class="form-check" >
                            @if($roleId == 1)
                                <input type="checkbox" class="form-check-input permission" name="permission[]" id="{{ $v->id }}" value="{{ $v->id }}" checked disabled >
                                <input type="hidden" name="permission[]" value="{{ $v->id }}" checked>
                            @else
                                <input type="checkbox" class="form-check-input permission" name="permission[]" id="{{ $v->id }}" value="{{ $v->id }}" aria-label="..." @if(in_array($v->id, $userPermissions)) checked @endif >
                            @endif
                        <label for="{{ $v->id }}"
                            class="form-check-label mb-0 f-14 f-500 aside-input-checbox">{{ $v->name }}</label>
                    </li>
                @endforeach
            </div>
        </div>
    @endforeach
</div>