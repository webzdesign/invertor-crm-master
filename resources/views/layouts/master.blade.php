<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset(Helper::getAppFavicon()) }}" type="image/x-icon">
    <title> {{ Helper::getAppTitle() . ' - ' . config('app.module', 'Module') }} </title>
    @include('layouts.partials.header')
    @yield('css')
</head>

<body>
    @php
        $authUser = auth()->user();
    @endphp
    <div class="main">
        <div class="LoaderSec d-none">
            <span class="loader"></span>
        </div>
        <div class="d-flex">
            @include('layouts.partials.sidebar')
            <div class="d-none d-block-992">
                <div class="sidebarOverlay d-none"></div>
            </div>
            <section class="w-100 rightSection">
                <header class="d-flex align-items-center justify-content-between pl284">
                    <div class="cursor-pointer menuicn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 6H21V8H3V6ZM3 11H21V13H3V11ZM3 16H21V18H3V16Z" fill="#fff" />
                            <defs>
                                <linearGradient id="paint0_linear_1622_4657" x1="12" y1="6"
                                    x2="12" y2="18" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#3F189F" />
                                    <stop offset="1" stop-color="#4C26AA" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <ul class="p-0 m-0">

                        @if(auth()->user()->id == 1)
                        <li class="dropdown middleContent p-0 userMenu">
                            <div class="commission-btn text-white f-700"> Balance : {{ Helper::getAdminBalance() }} </div>
                        </li>
                        @elseif(User::isSellerManager() || User::isSeller())
                        <li class="dropdown middleContent p-0 userMenu">
                            <div class="commission-btn text-white f-700"> Commission : {{ Helper::getSellerCommission() }} </div>
                        </li>
                        @elseif(User::isDriver())
                        <li class="dropdown middleContent p-0 userMenu">
                            <div class="commission-btn text-white f-700"> Balance : {{ Helper::getDriverBalance() }} </div>
                        </li>
                        @endif

                        <li class="dropdown middleContent p-0 userMenu">
                            <a href="javascript:;" data-bs-toggle="dropdown">
                                <svg width="30" height="30" viewBox="0 0 16 16" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1622_4660)">
                                    <path
                                        d="M8.00298 0.000347708C6.38264 0.000196848 4.80046 0.492015 3.46558 1.4107C2.1307 2.32934 1.10627 3.6315 0.527705 5.14505C-0.0510175 6.6586 -0.156496 8.31211 0.224911 9.88676C0.606461 11.4615 1.45705 12.8835 2.66417 13.9642C2.86271 14.1429 3.07075 14.3109 3.28727 14.4671C4.6561 15.4647 6.30633 16.0021 8.00001 16.0021C9.69381 16.0021 11.3439 15.4647 12.7128 14.4671C12.9294 14.3108 13.1373 14.1429 13.3357 13.9642C14.5425 12.8836 15.393 11.4623 15.7747 9.88792C16.1564 8.31373 16.0513 6.66064 15.4734 5.14737C14.8954 3.63397 13.8719 2.33185 12.538 1.41274C11.204 0.493637 9.62264 0.00104281 8.00257 0L8.00298 0.000347708ZM8.00298 2.85827V2.85843C8.60947 2.85843 9.19094 3.09922 9.61969 3.52799C10.0485 3.95678 10.2894 4.53836 10.2894 5.1447C10.2894 5.75104 10.0485 6.33265 9.61969 6.76141C9.1909 7.1902 8.60947 7.43112 8.00298 7.43112C7.39664 7.43112 6.81502 7.19018 6.38627 6.76141C5.95748 6.33262 5.71671 5.75104 5.71671 5.1447C5.71671 4.53836 5.9575 3.95674 6.38627 3.52799C6.81506 3.0992 7.39664 2.85843 8.00298 2.85843V2.85827ZM8.00298 14.2902C6.12723 14.2895 4.34966 13.4509 3.15583 12.0039C3.62683 10.6503 4.64114 9.55472 5.95447 8.98083C7.2678 8.40693 8.76095 8.40693 10.074 8.98083C11.3873 9.55472 12.4016 10.6504 12.8728 12.0039C11.674 13.4571 9.88661 14.2963 8.00283 14.2902H8.00298Z"
                                        fill="#fff" />
                                </g>
                                <defs>
                                    <linearGradient id="paint0_linear_1622_4660" x1="8" y1="0"
                                        x2="8" y2="16.0021" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#3F189F" />
                                        <stop offset="1" stop-color="#4C26AA" />
                                    </linearGradient>
                                    <clipPath id="clip0_1622_4660">
                                        <rect width="16" height="16" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                            </a>
                            <ul class="dropdown-menu notificationBody settingWrpr filterDropdownBx py-0">
                                <div class="cardsBody settingWrpr px-0 py-0">
                                    <li class="m-0 w-100">
                                        <a href="javascript:;"
                                            style="background-image: linear-gradient(to right bottom, #a53692, #cb367d, #e34765, #ee634c, #ec8335);border-radius: 3px 3px 0 0;">
                                            <h4 class="f-16 mb-0 f-700 text-white">
                                                {{ auth()->user()->name }}
                                            </h4>
                                        </a>
                                    </li>

                                    <li class="m-0 w-100">
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();">
                                            <h4 class="f-16 mb-0 f-700 c-19">
                                            <svg class="me-2 position-relative bottom-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><script xmlns=""/>
                                                <path d="M9 14H2L2 2H9" stroke="#000" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M7 8H15" stroke="#000" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round"/>
                                                <path d="M12 11L15 8L12 5" stroke="#000" stroke-width="1.75" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                                Logout
                                            </h4>
                                        </a>
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                        </form>
                                    </li>
                                </div>
                            </ul>
                        </li>

                    </ul>
                </header>
                @if(Helper::shouldHideBreadcumb())
                <div class="breadcrumb pl284">
                    <div class="d-flex justify-content-between w-100">
                        <ul class="p-0 m-0 w-100">
                            <li>
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.99996 0L11.3333 4V12H7.99996V7.33333H3.99996V12H0.666626V4L5.99996 0Z" fill="#7B809A"></path>
                                </svg>
                            </li>
                            <li>
                                <a href="{{ route('dashboard') }}" class="f-14 f-400 c-7b">Home</a>
                            </li>
                            <li class="f-14 f-400 c-7b">
                                /
                            </li>
                            @if(isset($moduleLink) && $moduleLink !="")
                                <li>{!! isset($moduleName) ? '<a href="'.$moduleLink.'" class="f-14 f-400 c-7b">'.$moduleName.'</a>' : 'Module' !!}</li>
                            @else
                                <li class="f-14 f-400 c-36">{{ isset($moduleName) ? $moduleName : 'Module' }}</li>
                            @endif

                            @yield('breadcumb')
                        </ul>
                        <div>@yield('deletedRecBtn')</div>
                    </div>
                    <div class="devider"></div>
                </div>
                @endif
                <div class="middleContent settingWrpr pl284">
                    @yield('create_button')
                    <div class="importWrpr">
                        @yield('content')
                    </div>
                </div>
                @include('layouts.partials.footer')
            </section>
        </div>
    </div>
    @include('layouts.partials.scripts')
    @yield('script')
</body>

</html>
<script>
    $(document).on('show.bs.modal', '.modal', function() {
        const zIndex = 1040 + 10 * $('.modal:visible').length;
        $(this).css('z-index', zIndex);
        setTimeout(() => $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack'));
    });
</script>
