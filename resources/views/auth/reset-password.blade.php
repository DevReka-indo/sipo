@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <div class="login-wrap d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-card shadow-elev">
            {{-- Header banner --}}
            <div class="login-header">
                <div class="login-banner">
                    <div class="banner-overlay">
                        <div class="logo-container">
                            <img class="login-logo" src="{{ asset('assets/img/logo-reka.png') }}" alt="REKA INKA Group">
                            <h1>RESET PASSWORD</h1>
                        </div>
                    </div>
                </div>

            </div>
            {{-- Body --}}
            <div class="login-body">
                <form method="POST" action="{{ route('password.update') }}" novalidate>
                    @csrf
                    <input type="text" name="token" value="{{ $token }}" hidden>
                    <input type="email" name="email" value="{{ request('email') }}" hidden>
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success mb-3">{{ session('status') }}</div>
                    @endif

                    {{-- Email --}}
                    {{-- <div class="form-group">
                        <div class="input-wrapper">
                            <span class="icon-chip"><i class="fas fa-user"></i></span>
                            <input type="email" class="form-control input-elev ps-5" name="email"
                                placeholder="Masukkan Email" value="" required autofocus>
                        </div>
                    </div> --}}

                    {{-- Password --}}
                    <div class="form-group mt-3">
                        <div class="input-wrapper">
                            <span class="icon-chip"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control input-elev ps-5 pe-5" name="password" id="password"
                                placeholder="Password Baru" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                        </div>
                    </div>

                    {{-- Password Confirmation --}}
                    <div class="form-group mt-3">
                        <div class="input-wrapper">
                            <span class="icon-chip"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control input-elev ps-5 pe-5" name="password_confirmation"
                                id="password_confirmation" placeholder="Konfirmasi Password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-submit mt-3 w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword(el) {
            const input = el.parentElement.querySelector("input");
            if (!input) return;

            if (input.type === "password") {
                input.type = "text";
                el.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                el.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
@endpush
