@extends('layouts.master')

@section('breadcumb')
<li class="f-14 f-400 c-7b">
    /
</li>
<li class="f-14 f-400 c-36">View </li>
@endsection

@section('content')
{{ Config::set('app.module',$moduleName) }}
<h2 class="f-24 f-700 c-36 my-2">View {{ $moduleName }}</h2>

    <div class="cards">
        <div class="cardsBody pb-0">
            <div class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Name: </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="form-control" placeholder="Enter name" readonly>
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Email: </label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control" placeholder="Enter email" readonly>
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Roles: </label>
                        <input type="text" class="form-control" readonly value="{{ implode(', ', $user->roles->pluck('name')->toArray() ?? []) ?? '' }}" >
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Country: </label>
                        <input type="text" class="form-control" readonly value="{{ $user->country->name ?? '' }}">
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">State: </label>
                        <input type="text" class="form-control" readonly value="{{ $user->state->name ?? '' }}">
                    </div>
                </div>

                <div class="col-4">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">City: </label>
                        <input type="text" class="form-control" readonly value="{{ $user->city->name ?? '' }}">
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Address Line 1: </label>
                        <textarea readonly name="address_line_1" id="address_line_1" class="form-control">{{ old('address_line_1', $user->address_line_1) }}</textarea>
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-group">
                        <label class="c-gr f-500 f-16 w-100 mb-2">Address Line 2:</label>
                        <textarea readonly name="address_line_2" id="address_line_2" class="form-control">{{ old('address_line_2', $user->address_line_2) }}</textarea>
                    </div>
                </div>

            </div>
        </div>

        <div class="cardsFooter d-flex justify-content-center">
            <a href="{{ route('users.index') }}">
                <button type="button" class="btn-default f-500 f-14">Cancel</button>
            </a>
        </div>
    </div>
@endsection
