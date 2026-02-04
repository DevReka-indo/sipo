<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - SIPO</title>

    <!-- Kaiadmin CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body class="login">
    <div class="wrapper wrapper-login">
        <div class="container container-login animated fadeIn">
            <h3 class="text-center">SISTEM INFORMASI PERSURATAN ONLINE</h3>

            <div class="login-form">
                <div class="form-group form-floating-label">
                    <input id="email" name="email" type="email" class="form-control input-border-bottom"
                        required />
                    <label for="email" class="placeholder"><i class="fa fa-user"></i> Email</label>
                </div>

                <div class="form-group form-floating-label">
                    <input id="password" name="password" type="password" class="form-control input-border-bottom"
                        required />
                    <label for="password" class="placeholder"><i class="fa fa-lock"></i> Password</label>
                    <div class="show-password">
                        <i class="fa fa-eye"></i>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <!-- Remember me -->
                    <div class="form-group form-action-d-flex">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                            <label class="custom-control-label" for="remember">Ingatkan Saya</label>
                        </div>
                        {{-- <a href="{{ route('forgot-password') }}" class="link float-right">Lupa Password?</a> --}}
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-rounded btn-login">Masuk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Kaiadmin JS -->
    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>
</body>

</html>
