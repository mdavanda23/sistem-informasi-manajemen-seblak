<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · SEPARMAN</title>
    @include('partials.head')
    <style>[x-cloak]{display:none !important}</style>
</head>
<body class="bg-[#F7F7F8] font-sans text-slate-800 antialiased">
@php
    $user   = auth()->user();
    $outlet = $user->outlet;

    $menus = [
        ['Dashboard',          'layout-dashboard', 'owner.dashboard',   'owner.dashboard'],
        ['Manajemen Menu',     'utensils',         'owner.menus.index', 'owner.menus.*'],
        ['Bahan Baku',         'wheat',            null, null],
        ['Pembelian Stok',     'shopping-bag',     null, null],
        ['Stok Opname',        'clipboard-check',  null, null],
        ['Laporan Penjualan',  'trending-up',      null, null],
        ['Riwayat Transaksi',  'file-text',        null, null],
        ['Notifikasi',         'bell',             null, null],
        ['Manajemen Kasir',    'users',            null, null],
    ];
@endphp

<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    <aside class="sticky top-0 hidden h-screen w-[295px] shrink-0 flex-col overflow-y-auto border-r border-slate-200 bg-white px-7 py-7 lg:flex">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-700 text-white">
                <i data-lucide="soup" class="h-6 w-6"></i>
            </div>
            <div>
                <p class="text-xl font-bold leading-none tracking-wide text-brand-700">SEPARMAN</p>
                <p class="mt-1 text-[11px] tracking-wide text-slate-500">SEBLAK PRASMANAN</p>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between rounded-xl bg-slate-100 px-4 py-3.5">
            <div>
                <p class="text-sm font-semibold text-slate-900">{{ $outlet?->name }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $outlet?->branch }}</p>
            </div>
            <i data-lucide="chevrons-up-down" class="h-4 w-4 text-slate-500"></i>
        </div>

        <p class="mt-8 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Ruang kerja owner</p>

        <nav class="mt-3 space-y-1">
            @foreach ($menus as [$label, $icon, $route, $pattern])
                @php $active = $pattern && request()->routeIs($pattern); @endphp
                <a href="{{ $route ? route($route) : '#' }}"
                   class="flex items-center gap-3 rounded-xl px-4 py-3 text-[15px] font-medium transition
                          {{ $active ? 'bg-brand-50 font-semibold text-brand-700' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
                    {{ $label }}
                </a>
            @endforeach
        </nav>
        <div class="mt-auto space-y-2 pt-8">
            <a href="#" class="flex items-center gap-3 rounded-xl bg-brand-50 px-4 py-3.5 text-sm font-semibold text-brand-700">
                <i data-lucide="circle-help" class="h-5 w-5"></i> Panduan operasional →
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-[15px] text-slate-600 hover:bg-slate-50">
                    <i data-lucide="log-out" class="h-5 w-5"></i> Keluar akun
                </button>
            </form>
        </div>
    </aside>

    {{-- KONTEN --}}
    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex h-[100px] items-center justify-between border-b border-slate-200 bg-white px-10">
            <div class="flex items-center gap-3 text-sm">
                <span class="text-slate-500">Ruang Owner</span>
                <i data-lucide="chevron-right" class="h-4 w-4 text-slate-400"></i>
                <span class="font-medium text-slate-900">@yield('title')</span>
            </div>

            <div class="flex items-center gap-5">
                @if ($outlet?->is_open)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700">Outlet buka</span>
                @else
                    <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600">Outlet tutup</span>
                @endif

                <button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="h-5 w-5"></i></button>
                <span class="h-8 w-px bg-slate-200"></span>

                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-100 text-sm font-semibold text-brand-700">
                            {{ $user->initials }}
                        </span>
                        <span class="text-left">
                            <span class="block text-sm font-semibold text-slate-900">{{ $user->name }}</span>
                            <span class="block text-xs capitalize text-slate-500">{{ $user->role }}</span>
                        </span>
                        <i data-lucide="chevron-down" class="h-4 w-4 text-slate-500"></i>
                    </button>

                    <div x-show="open" x-cloak x-transition
                         class="absolute right-0 z-20 mt-3 w-48 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i data-lucide="log-out" class="h-4 w-4"></i> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-10 py-10">
            @yield('content')
        </main>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>