<style>
    .ui-autocomplete {
        z-index: 99999;
    }
</style>
<aside class="asideLeft">
    <div class="logoSec d-flex align-items-center">
        <img src="{{ asset(Helper::getAppLogo()) }}" alt="logo" width="100%" height="60" class="m-auto d-table">
    </div>
    <ul class="p-0 menuList" id="accordionExample">
        <li>
            <a href="{{ route('dashboard') }}"
                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('dashboard') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-dashboard text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Dashboard</span>
            </a>
        </li>
        @php
            $authUser = auth()->user();
        @endphp

        {{-- @permission('users.view')
        <li>
            <a href="{{ route('users.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('users*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-users text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Users</span>
            </a>
        </li>
        @endpermission --}}

        @permission('roles.view')
        <li>
            <a href="{{ route('roles.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('roles*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-lock text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Roles</span>
            </a>
        </li>
        @endpermission




</ul>
</aside>
