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

        @permission('users.view')
        <li>
            <a href="{{ route('users.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('users*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-users text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Users</span>
            </a>
        </li>
        @endpermission

        @permission('categories.view')
        <li>
            <a href="{{ route('categories.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('categories*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-list-alt text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Categories</span>
            </a>
        </li>
        @endpermission

        @permission('products.view')
        <li>
            <a href="{{ route('products.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('products*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-product-hunt text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Products</span>
            </a>
        </li>
        @endpermission

        @permission('suppliers.view')
        <li>
            <a href="{{ route('suppliers.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('suppliers*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-user text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Suppliers</span>
            </a>
        </li>
        @endpermission

        @permission('purchase-orders.view')
        <li>
            <a href="{{ route('purchase-orders.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('purchase-orders*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-shopping-bag text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Storage</span>
            </a>
        </li>
        @endpermission

        @permission('distribution.view')
        <li>
            <a href="{{ route('distribution.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('distribution*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-industry text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Distribution</span>
            </a>
        </li>
        @endpermission

        @permission('procurement-cost.view')
        <li>
            <a href="{{ route('procurement-cost.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('procurement-cost*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-money text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add"> Procurement Cost </span>
            </a>
        </li>
        @endpermission

        @permission('sales-orders.view')
        <li>
            <a href="{{ route('sales-orders.index') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('sales-orders*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-tag text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Sales Order</span>
            </a>
        </li>
        @endpermission


        @if(in_array('3', User::getUserRoles()))
        <li>
            <a href="{{ route('orders-to-deliver') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('orders-to-deliver*') ? 'active' : '' }}">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-motorcycle	text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Orders to Deliver</span>
            </a>
        </li>
        @endif


</ul>
</aside>
