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

                        <li class="dropdown middleContent p-0 userMenu">
                            <form method="POST" action="{{ route('logout') }}"> @csrf
                                <button class="btn btn-primary" type="submit" style="color: white;background:#ffffff47!important;">Logout</button>
                            </form>
                        </li>

                    </ul>
                </header>
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
                            <li class="f-14 f-400 c-36">{{ isset($moduleName) ? $moduleName : 'Module' }}</li>
                            @yield('breadcumb')
                        </ul>
                        <div>@yield('deletedRecBtn')</div>
                    </div>
                    <div class="devider"></div>
                </div>
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
