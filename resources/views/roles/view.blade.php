@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">View {{ $moduleName }} </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">{{ $moduleName }}</h2>
    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-xl-12 col-md-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Role Name *</label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter role name here..." value="{{ $role->name }}" disabled>
                    </div>
                </div>

                <div class="col-xl-12 col-md-6">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Description</label>
                        <textarea disabled name="description" id="description" class="form-control" placeholder="Enter role description here...">{{ $role->description }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="cardsBody py-0">
            <label class="c-gr f-500 f-16 w-100 mb-2">Permissions</label>
            <div class="row">

                @foreach($permission as $key => $value)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-3 permission-listing">
                            <div class="PlBox">
                                @foreach($value as $k => $v)
                                    @if($loop->first)
                                    <li class="list-group-item inline bg-transparent border-0 p-0 mb-2">
                                        <label class="c-gr f-500 f-16 w-100 mb-2">
                                            <input type="checkbox" class="form-check-input selectDeselect" disabled>
                                            {{ $v->model }}</label>
                                    </li>
                                    @endif
                                    <li class="form-check">
                                        <input type="checkbox" class="form-check-input permission" name="permission[]" id="{{ $v->id }}" disabled value="{{ $v->id }}" aria-label="..." @if(in_array($v->id,$rolePermissions)) checked @endif>
                                        <label for="{{ $v->id }}" class="form-check-label mb-0 f-14 f-500 aside-input-checbox">{{ $v->name }}</label>
                                    </li>
                                @endforeach
                            </div>
                        </div>
                @endforeach
            </div>
        </div>
        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('roles.index') }}">
                <button type="button" class="btn-default f-500 f-14">cancel</button>
            </a>
        </div>
    </div>
@endsection
