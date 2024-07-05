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

        @if(auth()->user()->hasPermission('roles.view') || auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('suppliers.view'))
        <li>
            <a data-bs-toggle="collapse" data-bs-target="#collapseUM"
                aria-expanded="{{ request()->is('users*') || request()->is('roles*') || request()->is('suppliers*') ? 'true' : 'false' }}"
                aria-controls="collapseUM"
                class="f-400 f-14 text-white cursor-pointer d-flex align-items-center justify-content-between"
                href="javascript:;">
                <div class="d-flex align-items-center">
                    <div class="icnBx d-flex align-items-center justify-content-center">
                        <i class="fa fa-users text-white" aria-hidden="true"></i>
                    </div>
                    <span class="d-none-add">User Management</span>
                </div>

                <svg class="arrowDown d-none-add me-0" width="10" height="8" viewBox="0 0 10 8" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.612793 1.58594L4.85543 5.82858L9.09807 1.58594" stroke="white"></path>
                </svg>
            </a>
            <div id="collapseUM" data-bs-parent="#accordionExample"
                class="collapseMenu collapseWeb collapse {{ request()->is('users*') || request()->is('roles*') || request()->is('suppliers*') ? 'show' : '' }}">
                <ul class="p-0 menuList">
                    @permission('roles.view')
                        <li>
                            <a href="{{ route('roles.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('roles*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-lock text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Roles</span>
                            </a>
                        </li>
                    @endpermission

                    @permission('users.view')
                        <li>
                            <a href="{{ route('users.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('users*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-users text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Users</span>
                            </a>
                        </li>
                    @endpermission

                    @permission('suppliers.view')
                        <li>
                            <a href="{{ route('suppliers.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('suppliers*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-user text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Suppliers</span>
                            </a>
                        </li>
                    @endpermission
                </ul>
            </div>
        </li>
        @endif




        @if(auth()->user()->hasPermission('categories.view') || auth()->user()->hasPermission('products.view') || auth()->user()->hasPermission('purchase-orders.view') || auth()->user()->hasPermission('distribution.view'))
        <li>
            <a data-bs-toggle="collapse" data-bs-target="#collapsePSM"
                aria-expanded="{{ request()->is('categories*') || request()->is('products*') || request()->is('purchase-orders*') || request()->is('distribution*')  ? 'true' : 'false' }}"
                aria-controls="collapsePSM"
                class="f-400 f-14 text-white cursor-pointer d-flex align-items-center justify-content-between"
                href="javascript:;">
                <div class="d-flex align-items-center">
                    <div class="icnBx d-flex align-items-center justify-content-center">
                        <i class="fa fa-industry text-white" aria-hidden="true"></i>
                    </div>
                    <span class="d-none-add">Product & Stock Management</span>
                </div>

                <svg class="arrowDown d-none-add me-0" width="10" height="8" viewBox="0 0 10 8" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.612793 1.58594L4.85543 5.82858L9.09807 1.58594" stroke="white"></path>
                </svg>
            </a>
            <div id="collapsePSM" data-bs-parent="#accordionExample"
                class="collapseMenu collapseWeb collapse {{ request()->is('categories*') || request()->is('products*') || request()->is('purchase-orders*') || request()->is('distribution*') ? 'show' : '' }}">
                <ul class="p-0 menuList">


                    @permission('categories.view')
                        <li>
                            <a href="{{ route('categories.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('categories*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-list-alt text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Categories</span>
                            </a>
                        </li>
                    @endpermission

                    @permission('products.view')
                        <li>
                            <a href="{{ route('products.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('products*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-product-hunt text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Products</span>
                            </a>
                        </li>
                    @endpermission

                    @permission('purchase-orders.view')
                        <li>
                            <a href="{{ route('purchase-orders.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('purchase-orders*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-shopping-bag text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Storage</span>
                            </a>
                        </li>
                    @endpermission

                    @permission('distribution.view')
                        <li>
                            <a href="{{ route('distribution.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('distribution*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-industry text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Distribution</span>
                            </a>
                        </li>
                    @endpermission

                </ul>
            </div>
        </li>
        @endif



        @if(auth()->user()->hasPermission('procurement-cost.view') || auth()->user()->hasPermission('payment-for-delivery.view'))
        <li>
            <a data-bs-toggle="collapse" data-bs-target="#collapsePM"
                aria-expanded="{{ request()->is('procurement-cost*') || request()->is('payment-for-delivery*') ? 'true' : 'false' }}"
                aria-controls="collapsePM"
                class="f-400 f-14 text-white cursor-pointer d-flex align-items-center justify-content-between"
                href="javascript:;">
                <div class="d-flex align-items-center">
                    <div class="icnBx d-flex align-items-center justify-content-center">
                        <i class="fa fa-gbp text-white" aria-hidden="true"></i>
                    </div>
                    <span class="d-none-add">Price Management</span>
                </div>

                <svg class="arrowDown d-none-add me-0" width="10" height="8" viewBox="0 0 10 8"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.612793 1.58594L4.85543 5.82858L9.09807 1.58594" stroke="white"></path>
                </svg>
            </a>
            <div id="collapsePM" data-bs-parent="#accordionExample"
                class="collapseMenu collapseWeb collapse {{ request()->is('procurement-cost*') || request()->is('payment-for-delivery*') ? 'show' : '' }}">
                <ul class="p-0 menuList">

                    @permission('procurement-cost.view')
                        <li>
                            <a href="{{ route('procurement-cost.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('procurement-cost*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-money text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add"> Procurement Cost </span>
                            </a>
                        </li>
                    @endpermission

                    @permission('payment-for-delivery.view')
                        <li>
                            <a href="{{ route('payment-for-delivery') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('payment-for-delivery*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-gbp text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Payment for Delivery</span>
                            </a>
                        </li>
                    @endpermission

                </ul>
            </div>
        </li>
        @endif


        @if(auth()->user()->hasPermission('sales-orders.view') || auth()->user()->hasPermission('sales-order-status.view') || auth()->user()->hasPermission('orders-to-deliver.view'))
        <li>
            <a data-bs-toggle="collapse" data-bs-target="#collapseSLM"
                aria-expanded="{{ request()->is('sales-orders*') || request()->is('sales-order-status*') || request()->is('orders-to-deliver*') ? 'true' : 'false' }}"
                aria-controls="collapseSLM"
                class="f-400 f-14 text-white cursor-pointer d-flex align-items-center justify-content-between"
                href="javascript:;">
                <div class="d-flex align-items-center">
                    <div class="icnBx d-flex align-items-center justify-content-center">
                        <i class="fa fa-cog text-white" aria-hidden="true"></i>
                    </div>
                    <span class="d-none-add">Sales & Leads Management</span>
                </div>

                <svg class="arrowDown d-none-add me-0" width="10" height="8" viewBox="0 0 10 8"
                    fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0.612793 1.58594L4.85543 5.82858L9.09807 1.58594" stroke="white"></path>
                </svg>
            </a>
            <div id="collapseSLM" data-bs-parent="#accordionExample"
                class="collapseMenu collapseWeb collapse {{ request()->is('sales-orders*') || request()->is('sales-order-status*') || request()->is('orders-to-deliver*') ? 'show' : '' }}">
                <ul class="p-0 menuList">

                    @if (auth()->user()->hasPermission('orders-to-deliver.view') && !in_array(1, User::getUserRoles()))
                        <li>
                            <a href="{{ route('orders-to-deliver') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('orders-to-deliver*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-motorcycle	text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Orders to Deliver</span>
                            </a>
                        </li>
                    @endif

                    @permission('sales-orders.view')
                        <li>
                            <a href="{{ route('sales-orders.index') }}"
                                class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('sales-orders*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-tag text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add">Sales Order</span>
                            </a>
                        </li>
                    @endpermission

                    @if(auth()->user()->hasPermission('sales-order-status.view') && !in_array(3, User::getUserRoles()))
                        <li>
                            <a href="{{ route('sales-order-status') }}" class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('sales-order-status*') ? 'active' : '' }}">
                                <div class="icnBx d-flex align-items-center justify-content-center">
                                    <i class="fa fa-plus-square text-white" aria-hidden="true"></i>
                                </div>
                                <span class="d-none-add"> Leads </span>
                            </a>
                        </li>
                    @endif

            </ul>
            </div>
        </li>
        @endif



    @if(auth()->user()->hasPermission('stock-report.view') || auth()->user()->hasPermission('ledger-report.view'))
    <li>
        <a data-bs-toggle="collapse" data-bs-target="#collapseR"
            aria-expanded="{{ request()->is('stock-report*') || request()->is('ledger-report*') ? 'true' : 'false' }}"
            aria-controls="collapseR"
            class="f-400 f-14 text-white cursor-pointer d-flex align-items-center justify-content-between"
            href="javascript:;">
            <div class="d-flex align-items-center">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-file text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Reports</span>
            </div>

            <svg class="arrowDown d-none-add me-0" width="10" height="8" viewBox="0 0 10 8"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0.612793 1.58594L4.85543 5.82858L9.09807 1.58594" stroke="white"></path>
            </svg>
        </a>
        <div id="collapseR" data-bs-parent="#accordionExample"
            class="collapseMenu collapseWeb collapse {{ request()->is('stock-report*') || request()->is('ledger-report*') ? 'show' : '' }}">
            <ul class="p-0 menuList">

                @permission('stock-report.view')
                    <li>
                        <a href="{{ route('stock-report') }}"
                            class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('stock-report*') ? 'active' : '' }}">
                            <div class="icnBx d-flex align-items-center justify-content-center">
                                <i class="fa fa-product-hunt text-white" aria-hidden="true"></i>
                            </div>
                            <span class="d-none-add">Stock Report</span>
                        </a>
                    </li>
                @endpermission

                {{-- @permission('ledger-report.view')
                    <li>
                        <a href="{{ route('ledger-report') }}"
                            class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('ledger-report*') ? 'active' : '' }}">
                            <div class="icnBx d-flex align-items-center justify-content-center">
                                <i class="fa fa-gbp text-white" aria-hidden="true"></i>
                            </div>
                            <span class="d-none-add">Ledger Report</span>
                        </a>
                    </li>
                @endpermission --}}

            </ul>
        </div>
    </li>
    @endif



    @if(User::isAdmin())
    <li>
        <a data-bs-toggle="collapse" data-bs-target="#collapseCom"
            aria-expanded="{{ request()->is('financial-report*') ? 'true' : 'false' }}"
            aria-controls="collapseCom"
            class="f-400 f-14 text-white cursor-pointer d-flex align-items-center justify-content-between"
            href="javascript:;">
            <div class="d-flex align-items-center">
                <div class="icnBx d-flex align-items-center justify-content-center">
                    <i class="fa fa-file text-white" aria-hidden="true"></i>
                </div>
                <span class="d-none-add">Financial Reports</span>
            </div>

            <svg class="arrowDown d-none-add me-0" width="10" height="8" viewBox="0 0 10 8"
                fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0.612793 1.58594L4.85543 5.82858L9.09807 1.58594" stroke="white"></path>
            </svg>
        </a>
        <div id="collapseCom" data-bs-parent="#accordionExample"
            class="collapseMenu collapseWeb collapse {{ request()->is('financial-report*') ? 'show' : '' }}">
            <ul class="p-0 menuList">

                @if(User::isAdmin() || User::isDriver())
                    <li>
                        <a href="{{ route('driver-commission') }}"
                            class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('financial-report/driver') ? 'active' : '' }}">
                            <div class="icnBx d-flex align-items-center justify-content-center">
                                <i class="fa fa-car text-white" aria-hidden="true"></i>
                            </div>
                            <span class="d-none-add">Driver Report</span>
                        </a>
                    </li>
                @endif

                @if(auth()->user()->hasPermission('financial-seller-report.view'))
                <li>
                    <a href="{{ route('seller-commission') }}"
                        class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('financial-report/seller*') ? 'active' : '' }}">
                        <div class="icnBx d-flex align-items-center justify-content-center">
                            <i class="fa fa-user text-white" aria-hidden="true"></i>
                        </div>
                        <span class="d-none-add">Seller Report</span>
                    </a>
                </li>
                @endif

            </ul>
        </div>
    </li>
    @elseif(User::isDriver() || auth()->user()->hasPermission('financial-seller-report.view'))

    @if(User::isAdmin() || User::isDriver())
    <li>
        <a href="{{ route('driver-commission') }}"
            class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('financial-report/driver') ? 'active' : '' }}">
            <div class="icnBx d-flex align-items-center justify-content-center">
                <i class="fa fa-car text-white" aria-hidden="true"></i>
            </div>
            <span class="d-none-add">Financial Report</span>
        </a>
    </li>
    @endif

    @if(auth()->user()->hasPermission('financial-seller-report.view'))
    <li>
        <a href="{{ route('seller-commission') }}"
            class="d-flex align-items-center text-white f-400 f-14 {{ request()->is('financial-report/seller') ? 'active' : '' }}">
            <div class="icnBx d-flex align-items-center justify-content-center">
                <i class="fa fa-user text-white" aria-hidden="true"></i>
            </div>
            <span class="d-none-add">Financial Report</span>
        </a>
    </li>
    @endif
    
    @endif


</ul>
</aside>
