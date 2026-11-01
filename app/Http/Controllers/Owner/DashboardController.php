<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $outletId = $user->outlet_id;

        $today     = now()->startOfDay();
        $yesterday = $today->copy()->subDay();

        $now  = $this->dailyStats($outletId, $today);
        $prev = $this->dailyStats($outletId, $yesterday);

        $lowStock = Ingredient::where('outlet_id', $outletId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        // ---- Kartu statistik ----
        [$omzetNote, $omzetClass] = $this->diffNote($this->pct($now['omzet'], $prev['omzet']), 'dari kemarin');

        $diffCount = $now['count'] - $prev['count'];
        $countNote = $diffCount >= 0
            ? "↑ {$diffCount} transaksi dari kemarin"
            : '↓ ' . abs($diffCount) . ' transaksi dari kemarin';
        $countClass = $diffCount >= 0 ? 'text-emerald-700' : 'text-red-600';

        $margin = $now['omzet'] > 0 ? round($now['laba'] / $now['omzet'] * 100) : 0;

        $cards = [
            [
                'label' => 'Omzet hari ini', 'icon' => 'wallet',
                'value' => $this->rupiah($now['omzet']),
                'note'  => $omzetNote, 'class' => $omzetClass, 'iconBg' => 'bg-brand-100 text-brand-700',
            ],
            [
                'label' => 'Total transaksi', 'icon' => 'file-text',
                'value' => number_format($now['count'], 0, ',', '.'),
                'note'  => $countNote, 'class' => $countClass, 'iconBg' => 'bg-brand-100 text-brand-700',
            ],
            [
                'label' => 'Estimasi laba', 'icon' => 'trending-up',
                'value' => $this->rupiah($now['laba']),
                'note'  => "Margin {$margin}% dari omzet", 'class' => 'text-emerald-700', 'iconBg' => 'bg-brand-100 text-brand-700',
            ],
            [
                'label' => 'Stok menipis', 'icon' => 'package',
                'value' => $lowStock . ' bahan',
                'note'  => $lowStock > 0 ? 'Perlu segera dibeli kembali' : 'Semua stok masih aman',
                'class' => $lowStock > 0 ? 'text-amber-700' : 'text-emerald-700',
                'iconBg' => 'bg-amber-100 text-amber-700',
            ],
        ];

        // ---- Tren penjualan 7 hari terakhir ----
        $start = $today->copy()->subDays(6);

        $perDay = Transaction::where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, now()->endOfDay()])
            ->selectRaw('DATE(paid_at) as d, SUM(total) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date   = $start->copy()->addDays($i);
            $days[] = [
                'label'    => $date->translatedFormat('D'),
                'value'    => (int) ($perDay[$date->toDateString()] ?? 0),
                'is_today' => $date->isSameDay($today),
            ];
        }

        $weekTotal = collect($days)->sum('value');

        $prevWeekTotal = (int) Transaction::where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start->copy()->subDays(7), $start->copy()->subSecond()])
            ->sum('total');

        [$weekNote, $weekClass] = $this->diffNote($this->pct($weekTotal, $prevWeekTotal), 'dari minggu lalu');

        $maxVal = max(array_column($days, 'value'));
        $step   = max(500000, (int) ceil($maxVal / 3 / 500000) * 500000);
        $axisMax = $step * 3;
        $ticks  = [];
        for ($i = 3; $i >= 0; $i--) {
            $v = $step * $i;
            $ticks[] = $v === 0 ? '0' : number_format($v / 1000000, $v % 1000000 ? 1 : 0, ',', '.') . ' jt';
        }

        // ---- Menu paling laris (7 hari terakhir) ----
        $topMenus = TransactionItem::select(
                'menu_name',
                DB::raw('SUM(quantity) as qty'),
                DB::raw('SUM(subtotal) as total')
            )
            ->whereHas('transaction', fn ($q) => $q
                ->where('outlet_id', $outletId)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$start, now()->endOfDay()]))
            ->groupBy('menu_name')
            ->orderByDesc('qty')
            ->limit(4)
            ->get();

        $topMax = max(1, (int) $topMenus->max('qty'));

        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default    => 'Selamat malam',
        };

        return view('owner.dashboard', [
            'greeting'  => $greeting,
            'firstName' => explode(' ', $user->name)[0],
            'today'     => $today,
            'cards'     => $cards,
            'days'      => $days,
            'axisMax'   => $axisMax,
            'ticks'     => $ticks,
            'weekTotal' => $this->rupiah($weekTotal),
            'weekNote'  => $weekNote,
            'weekClass' => $weekClass,
            'start'     => $start,
            'topMenus'  => $topMenus,
            'topMax'    => $topMax,
        ]);
    }

    private function dailyStats(int $outletId, Carbon $date): array
    {
        $base = Transaction::where('outlet_id', $outletId)
            ->where('status', 'paid')
            ->whereDate('paid_at', $date);

        $laba = TransactionItem::whereHas('transaction', fn ($q) => $q
                ->where('outlet_id', $outletId)
                ->where('status', 'paid')
                ->whereDate('paid_at', $date))
            ->selectRaw('COALESCE(SUM(subtotal - (cost_price * quantity)), 0) as laba')
            ->value('laba');

        return [
            'omzet' => (int) (clone $base)->sum('total'),
            'count' => (int) (clone $base)->count(),
            'laba'  => (int) $laba,
        ];
    }

    private function pct(int|float $now, int|float $prev): ?float
    {
        return $prev > 0 ? round(($now - $prev) / $prev * 100, 1) : null;
    }

    private function diffNote(?float $pct, string $suffix): array
    {
        if ($pct === null) {
            return ['Belum ada data pembanding', 'text-slate-500'];
        }

        $arrow = $pct >= 0 ? '↑' : '↓';
        $class = $pct >= 0 ? 'text-emerald-700' : 'text-red-600';

        return ["{$arrow} " . number_format(abs($pct), 1, ',', '.') . "% {$suffix}", $class];
    }

    private function rupiah(int|float $n): string
    {
        return 'Rp' . number_format($n, 0, ',', '.');
    }
}