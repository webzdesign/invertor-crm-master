<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ config('app.name', 'Module') }} </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/main.css?time=') . time() }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <style>
        label.error {
            color: #ff0a22;
            font-size: 15px;
            font-family: 'Roboto Regular' !important;
        }
        .btn-primary{
            height: 40px !important;
        }
    </style>
</head>
<body id="loginAnimation">
    <div class="main">
        <section class="loginCard d-flex align-items-center justify-content-center">
            <div class="loginCardBody w-100-500">
                <div class="loginCardHead d-flex align-items-center justify-content-center position-relative">
                    <a href="{{ route('login') }}">
                        <h3 class="text-white f-700 f-22 m-0"> {{ Helper::getAppTitle() }} </h3>
                    </a>
                </div>
                <form method="POST" action="{{ route('login') }}" id="loginForm"> @csrf
                    <div class="form-group">
                        <label class="f-500 f-12 mb-2 d-flex align-items-center text-white">
                            Email
                        </label>
                        <input id="email" type="email" class="form-control f-400 f-14 text-dark @error('email') is-invalid @enderror" placeholder="Enter Email" name="email" value="{{ old('email') }}"  autocomplete="email" autofocus>
                        <span class="text-danger f-400 f-14">
                            @error('email')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <div class="form-group mb-0">
                        <label class="f-500 f-12 mb-2 d-flex align-items-center text-white">
                            Password
                        </label>
                        <input id="password" type="password" class="form-control f-400 f-14 text-dark @error('password') is-invalid @enderror" name="password" placeholder="Enter password" autocomplete="current-password">
                        <span class="text-danger f-400 f-14">
                            @error('password')
                                {{ $message }}
                            @enderror
                        </span>
                    </div>
                    <button type="submit" class="btn-primary text-uppercase w-100 mt-4">sign in</button>
                </form>
            </div>
        </section>
    </div>

</body>
</html>

<script src="{{ asset('assets/js/jquery3-6-0.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-validate.min.js') }}"></script>
<script src="{{ asset('assets/js/jqueryAdditional.js') }}"></script>
<script src="{{ asset('assets/js/three.r134.min.js') }}"></script>
<script src="{{ asset('assets/js/vanta.net.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert.js') }}"></script>
<script>

VANTA.NET({
  el: "#loginAnimation",
  mouseControls: true,
  touchControls: true,
  gyroControls: false,
  minHeight: 200.00,
  minWidth: 200.00,
  scale: 1.00,
  scaleMobile: 1.00
})

var hasSessionError = "{{session()->has('error') ? true : false}}";

function fireErrMessage(message) {
    Swal.fire('Error', message, 'error');
}

if (hasSessionError) {
    fireErrMessage("{{ session('error') }}");
}

    $(document).ready(function(){

        $("#loginForm").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true
                }
            },
            messages: {
                email: {
                    required: "Email is required.",
                    email: "Email format is not valid."
                },
                password: {
                    required: "Password is required."
                }
            }
        });
    });

    $(document).ready(function() {
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () {
        window.history.go(1);
      };
    });
</script>