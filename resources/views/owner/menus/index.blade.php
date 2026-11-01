@extends('layouts.app')

@section('title', 'Manajemen Menu')

@section('content')
@php
    $sorts = [
        'latest'     => 'Urutkan: Terbaru',
        'name'       => 'Urutkan: Nama A–Z',
        'price_asc'  => 'Urutkan: Harga terendah',
        'price_desc' => 'Urutkan: Harga tertinggi',
        'stock_asc'  => 'Urutkan: Stok tersedikit',
    ];
    $activeCat = request('category');
    $outletInfo = auth()->user()->outlet;

    $init = [
        'baseUrl'   => url('owner/menus'),
        'errorForm' => $errors->any() ? old('_form') : null,
        'form' => [
            'name'         => old('name', ''),
            'category_id'  => (string) old('category_id', ''),
            'price'        => old('price', ''),
            'cost_price'   => old('cost_price', ''),
            'unit'         => old('unit', 'porsi'),
            'stock'        => old('stock', ''),
            'min_stock'    => old('min_stock', 10),
            'is_available' => old('_form') ? (bool) old('is_available') : true,
        ],
    ];
@endphp

<div x-data="menuPage(@js($init))" x-init="initPage()">

    {{-- Judul --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Manajemen Menu</h1>
            <p class="mt-2 text-slate-500">Atur menu, harga, dan ketersediaan topping dalam satu tempat.</p>
        </div>
        <button type="button" @click="openCreate()"
                class="flex h-[54px] shrink-0 items-center gap-2 rounded-xl bg-brand-700 px-6 font-semibold text-white transition hover:bg-brand-800">
            <i data-lucide="plus" class="h-5 w-5"></i> Tambah Menu
        </button>
    </div>

    {{-- Notifikasi sukses --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4500)" x-show="show" x-transition
             class="mt-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <i data-lucide="circle-check" class="h-5 w-5"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Statistik --}}
    <div class="mt-8 grid grid-cols-2 gap-6 rounded-2xl border border-slate-200 bg-white px-8 py-7 md:grid-cols-4">
        @foreach ([
            [$stats['total'],      'Total menu',     'text-brand-700'],
            [$stats['categories'], 'Kategori',       'text-brand-700'],
            [$stats['available'],  'Menu tersedia',  'text-brand-700'],
            [$stats['low'],        'Stok terbatas',  'text-amber-700'],
        ] as [$n, $label, $color])
            <div class="flex items-baseline gap-4">
                <span class="text-4xl font-bold {{ $color }}">{{ $n }}</span>
                <span class="text-slate-500">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    {{-- Pencarian & filter --}}
    <form method="GET" action="{{ route('owner.menus.index') }}" class="mt-6 flex flex-wrap gap-4">
        @if ($activeCat)
            <input type="hidden" name="category" value="{{ $activeCat }}">
        @endif

        <div class="relative min-w-[260px] flex-1">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i data-lucide="search" class="h-5 w-5"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama menu..."
                   class="h-[54px] w-full rounded-xl border border-slate-200 bg-white pl-12 pr-4 outline-none focus:border-brand-700 focus:ring-2 focus:ring-brand-700/15">
        </div>

        <select name="availability" onchange="this.form.submit()"
                class="h-[54px] w-full rounded-xl border border-slate-200 bg-white px-4 text-slate-600 outline-none focus:border-brand-700 sm:w-[260px]">
            <option value="">Semua ketersediaan</option>
            <option value="available"   @selected(request('availability') === 'available')>Tersedia</option>
            <option value="unavailable" @selected(request('availability') === 'unavailable')>Tidak tersedia</option>
            <option value="low"         @selected(request('availability') === 'low')>Stok menipis</option>
        </select>

        <select name="sort" onchange="this.form.submit()"
                class="h-[54px] w-full rounded-xl border border-slate-200 bg-white px-4 font-semibold text-slate-900 outline-none focus:border-brand-700 sm:w-[230px]">
            @foreach ($sorts as $key => $label)
                <option value="{{ $key }}" @selected(request('sort', 'latest') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    {{-- Tab kategori --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}"
           class="rounded-xl border px-4 py-2.5 text-sm font-medium transition
                  {{ ! $activeCat ? 'border-brand-700 bg-brand-700 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
            Semua Menu
        </a>
        @foreach ($categories as $c)
            <a href="{{ request()->fullUrlWithQuery(['category' => $c->id, 'page' => null]) }}"
               class="rounded-xl border px-4 py-2.5 text-sm font-medium transition
                      {{ (string) $activeCat === (string) $c->id ? 'border-brand-700 bg-brand-700 text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                {{ $c->name }}
            </a>
        @endforeach
    </div>

    {{-- Info jumlah + tombol tampilan --}}
    <div class="mt-8 flex items-center justify-between">
        <p class="text-sm text-slate-500">Menampilkan {{ $items->count() }} dari {{ $items->total() }} menu</p>
        <div class="flex items-center gap-3">
            <button type="button" @click="setView('grid')" :class="view === 'grid' ? 'text-brand-700' : 'text-slate-400 hover:text-slate-600'">
                <i data-lucide="layout-grid" class="h-6 w-6"></i>
            </button>
            <button type="button" @click="setView('list')" :class="view === 'list' ? 'text-brand-700' : 'text-slate-400 hover:text-slate-600'">
                <i data-lucide="list" class="h-6 w-6"></i>
            </button>
        </div>
    </div>

    @if ($items->isEmpty())
        <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <p class="font-semibold text-slate-900">Menu tidak ditemukan</p>
            <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter, atau tambahkan menu baru.</p>
            <a href="{{ route('owner.menus.index') }}" class="mt-4 inline-block text-sm font-semibold text-brand-700 hover:underline">Reset filter</a>
        </div>
    @else
        {{-- TAMPILAN GRID --}}
        <div x-show="view === 'grid'" class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($items as $menu)
                <div class="rounded-2xl border border-slate-200 bg-white p-3.5 {{ $menu->is_available ? '' : 'opacity-75' }}">
                    <div class="h-[170px] overflow-hidden rounded-xl bg-brand-50">
                        @if ($menu->image_url)
                            <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-brand-200">
                                <i data-lucide="utensils" class="h-10 w-10"></i>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xs text-slate-500">{{ $menu->category?->name ?? 'Tanpa kategori' }}</span>
                        @if (! $menu->is_available)
                            <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Nonaktif</span>
                        @elseif ($menu->is_low_stock)
                            <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Menipis</span>
                        @endif
                    </div>

                    <div class="mt-3 flex items-start justify-between gap-3">
                        <p class="font-medium text-slate-900">{{ $menu->name }}</p>
                        <p class="shrink-0 font-semibold text-brand-700">{{ $menu->price_formatted }}</p>
                    </div>

                    <p class="mt-3 flex items-center gap-2 text-sm text-slate-500">
                        <i data-lucide="package" class="h-4 w-4"></i>
                        Stok: {{ number_format($menu->stock, 0, ',', '.') }} {{ $menu->unit }}
                    </p>

                    <div class="mt-4 flex gap-2">
                        <button type="button" @click="openEdit(@js($menu->toForm()))"
                                class="flex h-11 flex-1 items-center justify-center gap-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-900 hover:bg-slate-50">
                            <i data-lucide="pencil" class="h-4 w-4"></i> Edit
                        </button>
                        <button type="button" @click="confirmDelete(@js($menu->id), @js($menu->name))"
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-700 hover:bg-brand-100">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- TAMPILAN LIST --}}
        <div x-show="view === 'list'" x-cloak class="mt-5 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Menu</th>
                        <th class="px-5 py-3 font-semibold">Kategori</th>
                        <th class="px-5 py-3 font-semibold">Harga</th>
                        <th class="px-5 py-3 font-semibold">Stok</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($items as $menu)
                        <tr>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-brand-50">
                                        @if ($menu->image_url)
                                            <img src="{{ $menu->image_url }}" alt="" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-brand-200"><i data-lucide="utensils" class="h-5 w-5"></i></div>
                                        @endif
                                    </div>
                                    <span class="font-medium text-slate-900">{{ $menu->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $menu->category?->name ?? '-' }}</td>
                            <td class="px-5 py-3 font-semibold text-brand-700">{{ $menu->price_formatted }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ number_format($menu->stock, 0, ',', '.') }} {{ $menu->unit }}</td>
                            <td class="px-5 py-3">
                                @if (! $menu->is_available)
                                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">Nonaktif</span>
                                @elseif ($menu->is_low_stock)
                                    <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Menipis</span>
                                @else
                                    <span class="rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Tersedia</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button" @click="openEdit(@js($menu->toForm()))"
                                            class="flex h-9 items-center gap-2 rounded-lg border border-slate-200 px-3 text-xs font-semibold hover:bg-slate-50">
                                        <i data-lucide="pencil" class="h-4 w-4"></i> Edit
                                    </button>
                                    <button type="button" @click="confirmDelete(@js($menu->id), @js($menu->name))"
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700 hover:bg-brand-100">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Footer + paginasi --}}
    <div class="mt-10 flex flex-wrap items-center justify-between gap-4">
        <p class="text-sm text-slate-500">Harga per porsi · Stok terhubung dengan bahan baku</p>

        @if ($items->hasPages())
            <nav class="flex items-center gap-2 text-sm font-semibold">
                @if ($items->onFirstPage())
                    <span class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-slate-300">Sebelumnya</span>
                @else
                    <a href="{{ $items->previousPageUrl() }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 hover:bg-slate-50">Sebelumnya</a>
                @endif

                @foreach (range(1, $items->lastPage()) as $p)
                    <a href="{{ $items->url($p) }}"
                       class="flex h-10 w-10 items-center justify-center rounded-lg border
                              {{ $p === $items->currentPage() ? 'border-brand-700 bg-brand-700 text-white' : 'border-slate-200 bg-white hover:bg-slate-50' }}">
                        {{ $p }}
                    </a>
                @endforeach

                @if ($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 hover:bg-slate-50">Berikutnya</a>
                @else
                    <span class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-slate-300">Berikutnya</span>
                @endif
            </nav>
        @endif
    </div>

    <div class="mt-10 flex flex-wrap justify-between gap-2 text-xs text-slate-500">
        <span>© {{ date('Y') }} SEPARMAN · Kelola lebih rapi, usaha lebih pasti.</span>
        <span>{{ $outletInfo?->branch }}, {{ $outletInfo?->city }}</span>
    </div>

    {{-- MODAL TAMBAH / EDIT --}}
    <div x-show="modal" x-cloak class="fixed inset-0 z-40 flex items-center justify-center p-4" @keydown.escape.window="modal = false">
        <div class="absolute inset-0 bg-slate-900/50" @click="modal = false"></div>

        <div class="relative max-h-[92vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-7 shadow-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-slate-900" x-text="mode === 'edit' ? 'Edit menu' : 'Tambah menu'"></h3>
                <button type="button" @click="modal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            @if ($errors->any())
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" :action="action" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="_form" :value="mode === 'edit' ? 'edit:' + editId : 'create'">
                <template x-if="mode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>

                {{-- Foto --}}
                <div>
                    <label class="text-sm font-semibold text-slate-900">
                        Foto menu <span class="font-normal text-slate-400">(opsional, maks. 2 MB)</span>
                    </label>
                    <div class="mt-2 flex items-center gap-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-50 text-brand-200">
                            <img x-show="imageUrl" :src="imageUrl" alt="" class="h-full w-full object-cover">
                            <span x-show="!imageUrl"><i data-lucide="image" class="h-7 w-7"></i></span>
                        </div>
                        <input type="file" name="image" x-ref="file" accept="image/png,image/jpeg,image/webp" @change="preview($event)"
                               class="text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-900">Nama menu</label>
                    <input type="text" name="name" x-model="form.name" required maxlength="255"
                           class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 outline-none focus:border-brand-700 focus:ring-2 focus:ring-brand-700/15">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-900">Kategori</label>
                        <select name="category_id" x-model="form.category_id" required
                                class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-3 outline-none focus:border-brand-700">
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-900">Satuan</label>
                        <select name="unit" x-model="form.unit" required
                                class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-white px-3 outline-none focus:border-brand-700">
                            <option value="porsi">porsi</option>
                            <option value="gelas">gelas</option>
                            <option value="pcs">pcs</option>
                            <option value="bungkus">bungkus</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-900">Harga jual (Rp)</label>
                        <input type="number" name="price" x-model="form.price" min="0" required
                               class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 outline-none focus:border-brand-700 focus:ring-2 focus:ring-brand-700/15">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-900">HPP / modal (Rp)</label>
                        <input type="number" name="cost_price" x-model="form.cost_price" min="0"
                               class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 outline-none focus:border-brand-700 focus:ring-2 focus:ring-brand-700/15">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-900">Stok</label>
                        <input type="number" name="stock" x-model="form.stock" min="0" required
                               class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 outline-none focus:border-brand-700 focus:ring-2 focus:ring-brand-700/15">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-900">Batas stok menipis</label>
                        <input type="number" name="min_stock" x-model="form.min_stock" min="0" required
                               class="mt-2 h-12 w-full rounded-xl border border-slate-200 px-4 outline-none focus:border-brand-700 focus:ring-2 focus:ring-brand-700/15">
                    </div>
                </div>

                <label class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">Tersedia untuk dijual</span>
                        <span class="block text-xs text-slate-500">Jika dimatikan, menu tidak akan muncul di kasir.</span>
                    </span>
                    <input type="checkbox" name="is_available" value="1" x-model="form.is_available" class="h-5 w-5 accent-brand-700">
                </label>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="modal = false"
                            class="h-12 rounded-xl border border-slate-200 px-6 font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit"
                            class="h-12 rounded-xl bg-brand-700 px-8 font-semibold text-white hover:bg-brand-800">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL HAPUS --}}
    <div x-show="del" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="del = null">
        <div class="absolute inset-0 bg-slate-900/50" @click="del = null"></div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-7 text-center shadow-xl">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600">
                <i data-lucide="trash-2" class="h-6 w-6"></i>
            </div>
            <h3 class="mt-4 text-lg font-bold text-slate-900">Hapus menu?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Menu <b class="text-slate-900" x-text="del ? del.name : ''"></b> akan dihapus permanen.
                Riwayat transaksi lama tetap tersimpan.
            </p>
            <form method="POST" :action="del ? baseUrl + '/' + del.id : '#'" class="mt-6 flex gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="del = null"
                        class="h-12 flex-1 rounded-xl border border-slate-200 font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="h-12 flex-1 rounded-xl bg-red-600 font-semibold text-white hover:bg-red-700">Ya, hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
    function menuPage(init) {
        return {
            baseUrl: init.baseUrl,
            view: 'grid',
            modal: false,
            mode: 'create',
            editId: null,
            imageUrl: null,
            del: null,
            form: init.form,

            initPage() {
                try { this.view = localStorage.getItem('menuView') || 'grid'; } catch (e) {}

                // buka kembali modal kalau validasi gagal
                if (init.errorForm) {
                    if (init.errorForm.indexOf('edit:') === 0) {
                        this.mode = 'edit';
                        this.editId = init.errorForm.split(':')[1];
                    }
                    this.modal = true;
                }
            },

            setView(v) {
                this.view = v;
                try { localStorage.setItem('menuView', v); } catch (e) {}
            },

            get action() {
                return this.mode === 'edit' ? this.baseUrl + '/' + this.editId : this.baseUrl;
            },

            openCreate() {
                this.mode = 'create';
                this.editId = null;
                this.imageUrl = null;
                this.form = { name: '', category_id: '', price: '', cost_price: '', unit: 'porsi', stock: '', min_stock: 10, is_available: true };
                if (this.$refs.file) this.$refs.file.value = '';
                this.modal = true;
            },

            openEdit(m) {
                this.mode = 'edit';
                this.editId = m.id;
                this.imageUrl = m.image_url;
                this.form = {
                    name: m.name,
                    category_id: m.category_id ? String(m.category_id) : '',
                    price: m.price,
                    cost_price: m.cost_price,
                    unit: m.unit,
                    stock: m.stock,
                    min_stock: m.min_stock,
                    is_available: !!m.is_available,
                };
                if (this.$refs.file) this.$refs.file.value = '';
                this.modal = true;
            },

            confirmDelete(id, name) {
                this.del = { id: id, name: name };
            },

            preview(e) {
                const f = e.target.files[0];
                if (f) this.imageUrl = URL.createObjectURL(f);
            },
        };
    }
</script>
@endsection