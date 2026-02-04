@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
    <div class="login-wrap d-flex align-items-center justify-content-center min-vh-100">
        <div class="login-card shadow-elev">
            {{-- Header banner --}}
            <div class="login-header">
                <div class="login-banner" style="background-image:url('{{ asset('assets/img/backgroundLogin.png') }}')">
                    <div class="banner-overlay">
                        <div class="logo-container">
                            <img class="login-logo" src="{{ asset('assets/img/logo-reka.png') }}" alt="REKA INKA Group">
                            <h1>LUPA PASSWORD</h1>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Body --}}
            <div class="login-body">
                <p>Anda dapat me-reset password anda dengan mengisi Email dan NIP.<br> Link untuk melakukan
                    reset
                    password
                    akan
                    dikirim ke email tujuan.<br></p>

                <form method="POST" action="{{ route('send-email') }}" novalidate>
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
                            <span class="icon-chip"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control input-elev ps-5" name="email"
                                placeholder="Masukkan Email Tujuan" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>

                    {{-- NIP --}}
                    <div class="form-group mt-3">
                        <div class="input-wrapper">
                            <span class="icon-chip"><i class="fas fa-user"></i></span>
                            <input type="nip" class="form-control input-elev ps-5" name="nip"
                                placeholder="Masukkan NIP" value="{{ old('nip') }}" required autofocus>
                        </div>
                    </div>

                    {{-- Password --}}
                    {{-- <div class="form-group mt-3">
                        <div class="input-wrapper">
                            <span class="icon-chip"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control input-elev ps-5 pe-5" name="password"
                                placeholder="Masukkan Password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePassword(this)"></i>
                        </div>
                    </div> --}}

                    {{-- Options row --}}
                    {{-- <div class="d-flex justify-content-between align-items-center mt-2">
                        {{-- <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Ingatkan Saya</label>
                        </div>

                        @if (Route::has('forgot-password'))
                            <a class="forgot-link" href="{{ route('forgot-password') }}">Lupa Password?</a>
                        @endif
                    </div> --}}

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-submit mt-3 w-100">KIRIM</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="statusModalBody">
                    <!-- Pesan akan diisi via JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
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


        // Pesan Modal
        @if (session('status'))
            let message = '';
            switch ("{{ session('status') }}") {
                case 'email_sent':
                    message = 'Link untuk melakukan reset password telah dikirim ke email Anda.';
                    break;
                case 'invalid_nip':
                    message = 'NIP yang Anda masukkan tidak valid.';
                    break;
                case 'email_not_found':
                    message = 'Email tidak ditemukan di sistem.';
                    break;
                default:
                    message = "{{ session('status') }}"; // fallback pesan dari session
            }

            // Isi modal dan tampilkan
            document.getElementById('statusModalBody').innerText = message;
            let statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
            statusModal.show();
        @endif
    </script>
@endpush
