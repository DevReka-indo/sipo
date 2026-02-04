@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <div class="login-wrap d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-card shadow-elev">
            {{-- Header banner --}}
            <div class="login-header">
                <div class="login-banner" style="background-image:url('{{ asset('assets/img/backgroundLogin.png') }}')">
                    <div class="banner-overlay">
                        <div class="logo-container">
                            <img class="login-logo" src="{{ asset('assets/img/logo-reka.png') }}" alt="REKA INKA Group">
                            <h1>SISTEM INFORMASI PERSURATAN ONLINE</h1>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Body --}}
            <div class="login-body">
                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

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
                    <div class="form-group">
                        <div class="input-wrapper">
                            <span class="icon-chip"><i class="fas fa-user"></i></span>
                            <input type="credential" class="form-control input-elev ps-5" name="credential"
                                placeholder="Masukkan NIP" value="{{ old('credential') }}" required autofocus>
                        </div>
                    </div>

                    {{-- Password --}}
                    <div class="form-group mt-3">
                        <div class="input-wrapper">
                            <span class="icon-chip"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control input-elev ps-5 pe-5" name="password"
                                placeholder="Masukkan Password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                        </div>
                    </div>

                    {{-- Options row --}}
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Ingatkan Saya</label>
                        </div>

                        <a class="forgot-link" href="{{ route('forgot-password') }}">Lupa Password?</a>

                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-submit mt-3 w-100">MASUK</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword(el) {
            const input = el.parentElement.querySelector('input[type="password"], input[type="text"]');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                el.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                el.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function showNotification(message, type) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: type === 'success' ? 'Berhasil!' : 'Error!',
                    text: message,
                    icon: type,
                    showConfirmButton: true,
                    customClass: {
                        confirmButton: 'btn btn-success px-4 py-2',
                    },
                });
            } else {
                // Fallback alert
                alert(message);
            }
        }
        @if (session('success') === 'Memo berhasil diubah.')
            showNotification("Memo berhasil diubah dan disimpan.", "success");
        @endif
        @if (session('error'))
            showNotification("Gagal membuat memo. Silahkan periksa kembali data yang dimasukkan", "error");
        @endif
    </script>
@endpush
