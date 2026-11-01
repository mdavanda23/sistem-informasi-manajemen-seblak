<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · SEPARMAN</title>
    @include('partials.head')
</head>
<body class="font-sans antialiased bg-white text-slate-800">
<div class="min-h-screen grid lg:grid-cols-[45%_55%]">

    {{-- PANEL KIRI --}}
    <section class="hidden lg:flex flex-col bg-brand-900 text-white px-16 py-14">
        <div class="flex items-center gap-3">
            <div class="h-11 w-11 rounded-xl bg-white/90 text-brand-700 flex items-center justify-center">
                <i data-lucide="soup" class="h-6 w-6"></i>
            </div>
            <div>
                <p class="text-xl font-bold leading-none tracking-wide">SEPARMAN</p>
                <p class="mt-1 text-[11px] tracking-wider text-white/70">SEBLAK PRASMANAN</p>
            </div>
        </div>

        <div class="mt-24">
            <p class="flex items-center gap-3 text-xs font-medium tracking-wider text-white/80">
                <span class="h-px w-8 bg-pink-300/70"></span> TEMAN OPERASIONAL UMKM
            </p>
            <h1 class="mt-8 text-5xl font-bold leading-[1.25]">Racik rasa.<br>Rapikan usaha.</h1>
            <p class="mt-8 max-w-md text-[17px] leading-relaxed text-white/75">
                Dari satu mangkuk seblak, menuju usaha yang lebih tertata.
                Semua operasional ada dalam satu tempat.
            </p>
        </div>

        <img src="{{ asset('images/seblak.jpg') }}" alt="Seblak prasmanan"
             class="mt-10 h-72 w-full max-w-[675px] rounded-3xl object-cover">

        <div class="mt-6 flex flex-wrap gap-8 text-sm text-white/85">
            <span class="flex items-center gap-2"><i data-lucide="shopping-cart" class="h-4 w-4"></i> Transaksi praktis</span>
            <span class="flex items-center gap-2"><i data-lucide="package" class="h-4 w-4"></i> Stok terkendali</span>
            <span class="flex items-center gap-2"><i data-lucide="trending-up" class="h-4 w-4"></i> Laporan jelas</span>
        </div>

        <p class="mt-auto pt-10 text-xs text-white/60">
            SEPARMAN · Sistem Informasi Manajemen UMKM Seblak Prasmanan
        </p>
    </section>

    {{-- PANEL KANAN --}}
    <section class="flex flex-col px-6 py-8 sm:px-16 lg:px-[140px]">
        <div class="flex items-center justify-between text-sm">
            <span class="text-slate-500">
                {{ $outlet?->branch ?? 'Cabang' }} · {{ $outlet?->city ?? 'Bandung' }}
            </span>
            <a href="#" class="font-medium text-brand-700 hover:underline">Pusat bantuan ↗</a>
        </div>

        <div class="my-auto w-full max-w-[710px] py-10"
             x-data="{ role: '{{ old('role', 'owner') }}', show: false }">

            <p class="text-sm font-semibold uppercase tracking-wide text-brand-700">Selamat datang kembali</p>
            <h2 class="mt-3 text-4xl font-bold text-slate-900">Masuk ke SEPARMAN</h2>
            <p class="mt-4 text-slate-500">Pilih peran dan masuk untuk mulai mengelola usaha Anda.</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-10">
                @csrf

                <p class="text-sm font-semibold text-slate-900">Masuk sebagai</p>
                <div class="mt-3 grid grid-cols-2 gap-4">
                    @foreach ([
                        ['owner', 'Owner', 'Pantau & kelola usaha', 'trending-up'],
                        ['kasir', 'Kasir', 'Layani setiap pesanan', 'shopping-cart'],
                    ] as [$val, $title, $desc, $icon])
                        <label class="relative cursor-pointer rounded-xl border p-5 transition"
                               :class="role === '{{ $val }}' ? 'border-brand-700 bg-brand-50' : 'border-slate-200 bg-white hover:border-slate-300'">
                            <input type="radio" name="role" value="{{ $val }}" x-model="role" class="sr-only">
                            <span class="absolute right-4 top-4 flex h-5 w-5 items-center justify-center rounded-full border"
                                  :class="role === '{{ $val }}' ? 'border-brand-700' : 'border-slate-300'">
                                <span class="h-2 w-2 rounded-full bg-brand-700" x-show="role === '{{ $val }}'"></span>
                            </span>
                            <span :class="role === '{{ $val }}' ? 'text-brand-700' : 'text-slate-500'">
                                <i data-lucide="{{ $icon }}" class="h-6 w-6"></i>
                            </span>
                            <p class="mt-3 font-semibold" :class="role === '{{ $val }}' ? 'text-brand-700' : 'text-slate-900'">{{ $title }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $desc }}</p>
                        </label>
                    @endforeach
                </div>

                @error('email')
                    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
                @enderror

                <label for="email" class="mt-7 block text-sm font-semibold text-slate-900">Alamat email</label>
                <div class="relative mt-2">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="mail" class="h-5 w-5"></i></span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           placeholder="nama@separman.id"
                           class="h-[60px] w-full rounded-xl border border-slate-200 pl-12 pr-4 text-[15px] outline-none focus:border-brand-700 focus:ring-2 focus:ring-brand-700/15">
                </div>

                <label for="password" class="mt-6 block text-sm font-semibold text-slate-900">Kata sandi</label>
                <div class="relative mt-2">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="lock" class="h-5 w-5"></i></span>
                    <input id="password" name="password" required placeholder="Masukkan kata sandi"
                           :type="show ? 'text' : 'password'"
                           class="h-[60px] w-full rounded-xl border border-slate-200 pl-12 pr-12 text-[15px] outline-none focus:border-brand-700 focus:ring-2 focus:ring-brand-700/15">
                    <button type="button" @click="show = !show"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                        <span x-show="!show"><i data-lucide="eye-off" class="h-5 w-5"></i></span>
                        <span x-show="show" x-cloak><i data-lucide="eye" class="h-5 w-5"></i></span>
                    </button>
                </div>

                <div class="mt-5 flex items-center justify-between text-sm">
                    <label class="flex cursor-pointer items-center gap-2 text-slate-500">
                        <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 accent-brand-700" checked>
                        Ingat saya
                    </label>
                    <a href="#" class="font-semibold text-brand-700 hover:underline">Lupa kata sandi?</a>
                </div>

                <button type="submit"
                        class="mt-7 flex h-[56px] w-full items-center justify-center gap-3 rounded-xl bg-brand-700 font-semibold text-white transition hover:bg-brand-800">
                    <i data-lucide="arrow-right" class="h-5 w-5"></i>
                    <span x-text="role === 'owner' ? 'Masuk sebagai Owner' : 'Masuk sebagai Kasir'"></span>
                </button>
            </form>

            <p class="mt-10 flex items-center justify-center gap-2 text-xs text-slate-500">
                <i data-lucide="shield-check" class="h-4 w-4"></i> Akses aman sesuai peran pengguna
            </p>
        </div>

        <p class="text-center text-xs text-slate-500">© {{ date('Y') }} SEPARMAN. Semua hak dilindungi.</p>
    </section>
</div>

<style>[x-cloak]{display:none !important}</style>
<script>lucide.createIcons();</script>
</body>
</html>