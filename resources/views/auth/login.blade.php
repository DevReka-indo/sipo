@extends('layouts.login')

@section('content')
    <section class="login-page relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">

        {{-- Animated background --}}
        <div class="login-bg-orb absolute -left-24 -top-24 h-72 w-72 rounded-full bg-blue-200/60 blur-3xl"></div>
        <div class="login-bg-orb absolute bottom-0 -right-20 h-80 w-80 rounded-full bg-cyan-200/60 blur-3xl"></div>

        <div
            class="login-card relative z-10 grid w-full max-w-6xl overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl lg:grid-cols-2">

            {{-- LEFT --}}
            <div class="relative hidden flex-col justify-between overflow-hidden bg-white p-12 lg:flex">

                <div class="absolute inset-0">
                    <img src="{{ asset('assets/img/backgroundLogin.png') }}" alt="Background SIPO"
                        class="h-full w-full object-cover opacity-10">

                    <div class="absolute inset-0 bg-gradient-to-br from-white via-blue-50 to-slate-100"></div>
                </div>

                <div class="relative z-10 space-y-8 text-center">
                    <img src="{{ asset('assets/img/Logo-SIPO-Text.png') }}" alt="Logo SIPO"
                        class="login-left-animate mx-auto mb-10 h-20 w-auto">

                    <div class="space-y-4">
                        <span
                            class="login-left-animate inline-block rounded-full border border-blue-100 bg-blue-50 px-4 py-1.5 text-sm text-blue-700">
                            PT. REKAINDO GLOBAL JASA - {{ date('Y') }}
                        </span>

                        <h1 class="login-left-animate text-4xl font-extrabold leading-tight text-slate-900">
                            Kelola Surat Lebih Cepat & Terstruktur
                        </h1>

                        <p class="login-left-animate mx-auto max-w-md leading-relaxed text-slate-500">
                            SIPO membantu proses persuratan internal menjadi lebih efisien,
                            terdokumentasi, dan mudah dipantau.
                        </p>
                    </div>
                </div>

                <div class="relative z-10 mt-10 grid grid-cols-3 gap-4">
                    <div class="login-stat-card rounded-xl border bg-white p-4 shadow-sm transition hover:-translate-y-1">
                        <p class="text-lg font-bold text-blue-600">01</p>
                        <p class="mt-1 text-sm text-slate-500">Buat Surat</p>
                    </div>

                    <div class="login-stat-card rounded-xl border bg-white p-4 shadow-sm transition hover:-translate-y-1">
                        <p class="text-lg font-bold text-blue-600">02</p>
                        <p class="mt-1 text-sm text-slate-500">Approval</p>
                    </div>

                    <div class="login-stat-card rounded-xl border bg-white p-4 shadow-sm transition hover:-translate-y-1">
                        <p class="text-lg font-bold text-blue-600">03</p>
                        <p class="mt-1 text-sm text-slate-500">Monitoring</p>
                    </div>
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="flex items-center bg-white px-6 py-10 sm:px-12 lg:px-14">
                <div class="mx-auto w-full max-w-md">

                    {{-- Mobile logo --}}
                    <div class="login-form-item mb-8 text-center lg:hidden">
                        <img src="{{ asset('assets/img/Logo-SIPO-Text.png') }}" alt="Logo SIPO" class="mx-auto mb-4 h-14">
                    </div>

                    {{-- Header --}}
                    <div class="login-form-item mb-8">
                        <p class="mb-2 text-sm font-semibold text-blue-600">
                            Selamat datang kembali
                        </p>

                        <h2 class="text-3xl font-extrabold text-slate-900">
                            Masuk ke SIPO
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            Masukkan NIP atau email dan password untuk melanjutkan.
                        </p>
                    </div>

                    {{-- Validation errors --}}
                    @if ($errors->any())
                        <div
                            class="login-form-item mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-circle-exclamation mt-0.5"></i>

                                <ul class="list-inside list-disc space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Callback/login error --}}
                    @if (session('error'))
                        <div
                            class="login-form-item mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-circle-exclamation mt-0.5"></i>

                                <p>
                                    {{ session('error') }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Success/status message --}}
                    @if (session('status'))
                        <div
                            class="login-form-item mb-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-circle-check mt-0.5"></i>

                                <p>
                                    {{ session('status') }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Manual login --}}
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        {{-- Credential --}}
                        <div class="login-form-item">
                            <label for="credential" class="mb-2 block text-sm font-semibold text-slate-700">
                                NIP atau Email
                            </label>

                            <div class="group relative">
                                <i
                                    class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition group-focus-within:text-blue-600"></i>

                                <input id="credential" type="text" name="credential" value="{{ old('credential') }}"
                                    required autofocus autocomplete="username" placeholder="Masukkan NIP atau email"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="login-form-item">
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                                Password
                            </label>

                            <div class="group relative">
                                <i
                                    class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition group-focus-within:text-blue-600"></i>

                                <input id="password" type="password" name="password" required
                                    autocomplete="current-password" placeholder="Masukkan password"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-12 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">

                                <button type="button" onclick="togglePassword(this)"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-blue-600 focus:outline-none"
                                    aria-label="Tampilkan password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Remember and forgot password --}}
                        <div class="login-form-item flex items-center justify-between gap-4 text-sm">
                            <label class="flex cursor-pointer items-center gap-2 text-slate-600">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember'))
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                <span>
                                    Ingat saya
                                </span>
                            </label>

                            <a href="{{ route('forgot-password') }}"
                                class="page-transition-link font-semibold text-blue-600 transition hover:text-blue-700">
                                Lupa Password?
                            </a>
                        </div>

                        {{-- Submit manual login --}}
                        <button type="submit"
                            class="login-form-item inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-3 font-bold text-white shadow-lg shadow-slate-900/15 transition hover:-translate-y-0.5 hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200">
                            <i class="fas fa-arrow-right-to-bracket"></i>
                            Masuk ke SIPO
                        </button>
                    </form>

                    {{-- NEXID SSO --}}
                    @if (Route::has('sso.login'))
                        <div class="login-form-item my-7 flex items-center gap-4">
                            <div class="h-px flex-1 bg-slate-200"></div>

                            <span class="whitespace-nowrap text-xs font-semibold uppercase tracking-wider text-slate-400">
                                atau
                            </span>

                            <div class="h-px flex-1 bg-slate-200"></div>
                        </div>

                        <div class="login-form-item">
                            <a href="{{ route('sso.login') }}"
                                class="group inline-flex w-full items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 font-bold text-blue-700 transition hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-white p-1.5 shadow-sm">
                                    <img src="{{ asset('assets/img/Logo-NEXID.png') }}" alt="Logo NEXID"
                                        class="h-full w-full object-contain">
                                </span>

                                <span class="flex-1 text-center">
                                    Login dengan NEXID
                                </span>

                                <i class="fas fa-arrow-right text-sm opacity-70 transition group-hover:translate-x-1"></i>
                            </a>

                            <p class="mt-3 text-center text-xs leading-5 text-slate-400">
                                Gunakan akun karyawan terpusat yang terdaftar di NEXID.
                            </p>
                        </div>
                    @endif

                    {{-- Footer --}}
                    <div class="login-form-item mt-8 border-t border-slate-100 pt-6">
                        <p class="text-center text-xs leading-5 text-slate-400">
                            © {{ date('Y') }} SIPO - Sistem Informasi Persuratan Online
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function togglePassword(button) {
            const container = button.parentElement;
            const input = container.querySelector('input');
            const icon = button.querySelector('i');

            if (!input || !icon) {
                return;
            }

            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';

            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);

            button.setAttribute(
                'aria-label',
                isPassword ? 'Sembunyikan password' : 'Tampilkan password'
            );
        }
    </script>
@endpush
