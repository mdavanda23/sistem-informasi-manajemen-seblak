@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Sapaan --}}
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">{{ $greeting }}, {{ $firstName }} </h1>
            <p class="mt-2 text-slate-500">Berikut ringkasan usaha Anda hari ini. Semoga makin laris!</p>
        </div>
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-slate-900">
            <i data-lucide="calendar" class="h-5 w-5"></i>
            <span class="font-semibold">{{ $today->translatedFormat('j F Y') }}</span>
        </div>
    </div>

    {{-- Kartu statistik --}}
    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($cards as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $card['iconBg'] }}">
                        <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
                    </span>
                </div>
                <p class="mt-5 text-4xl font-bold text-slate-900">{{ $card['value'] }}</p>
                <p class="mt-4 text-sm {{ $card['class'] }}">{{ $card['note'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-5 grid gap-5 xl:grid-cols-[1fr_400px]">

        {{-- Tren penjualan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-7">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Tren penjualan</h2>
                    <p class="mt-1 text-sm text-slate-500">Omzet usaha dalam 7 hari terakhir</p>
                </div>
                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs text-slate-600">Minggu ini</span>
            </div>

            <div class="mt-6 flex items-start justify-between">
                <div>
                    <p class="text-4xl font-bold text-slate-900">{{ $weekTotal }}</p>
                    <p class="mt-2 text-sm {{ $weekClass }}">{{ $weekNote }}</p>
                </div>
                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs text-slate-600">
                    {{ $start->translatedFormat('j') }}–{{ $today->translatedFormat('j F Y') }}
                </span>
            </div>

            <div class="mt-6 flex gap-4">
                <div class="flex h-[200px] flex-col justify-between pb-0 text-[11px] text-slate-400">
                    @foreach ($ticks as $t)
                        <span>{{ $t }}</span>
                    @endforeach
                </div>
                <div class="flex-1">
                    <div class="flex h-[200px] items-end gap-5 border-b border-slate-100">
                        @foreach ($days as $d)
                            @php $h = $axisMax > 0 ? max(2, round($d['value'] / $axisMax * 100)) : 2; @endphp
                            <div class="flex-1 rounded-t-md {{ $d['is_today'] ? 'bg-brand-700' : 'bg-brand-200' }}"
                                 style="height: {{ $d['value'] > 0 ? $h : 1 }}%"
                                 title="Rp{{ number_format($d['value'], 0, ',', '.') }}"></div>
                        @endforeach
                    </div>
                    <div class="mt-2 flex gap-5 text-center text-[11px] text-slate-400">
                        @foreach ($days as $d)
                            <span class="flex-1">{{ $d['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Menu paling laris --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-7">
            <h2 class="text-lg font-semibold text-slate-900">Menu paling laris</h2>
            <p class="mt-1 text-sm text-slate-500">Favorit pelanggan minggu ini</p>

            <div class="mt-6 space-y-6">
                @forelse ($topMenus as $m)
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-slate-900">{{ $m->menu_name }}</span>
                            <span class="text-sm font-semibold text-slate-900">Rp{{ number_format($m->total, 0, ',', '.') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ number_format($m->qty, 0, ',', '.') }} porsi</p>
                        <div class="mt-3 h-1.5 rounded-full bg-brand-100">
                            <div class="h-1.5 rounded-full bg-brand-700" style="width: {{ round($m->qty / $topMax * 100) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                        Belum ada penjualan minggu ini.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
@endsection