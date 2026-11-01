<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $outletId = (int) auth()->user()->outlet_id;

        $stats = [
            'total'      => Menu::where('outlet_id', $outletId)->count(),
            'categories' => Category::where('outlet_id', $outletId)->count(),
            'available'  => Menu::where('outlet_id', $outletId)->where('is_available', true)->count(),
            'low'        => Menu::where('outlet_id', $outletId)->lowStock()->count(),
        ];

        $query = Menu::with('category')->where('outlet_id', $outletId);

        if ($search = trim((string) $request->query('q'))) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', (int) $request->query('category'));
        }

        match ($request->query('availability')) {
            'available'   => $query->where('is_available', true),
            'unavailable' => $query->where('is_available', false),
            'low'         => $query->lowStock(),
            default       => null,
        };

        match ($request->query('sort')) {
            'name'       => $query->orderBy('name'),
            'price_asc'  => $query->orderBy('price')->orderBy('name'),
            'price_desc' => $query->orderByDesc('price')->orderBy('name'),
            'stock_asc'  => $query->orderBy('stock')->orderBy('name'),
            default      => $query->orderByDesc('id'),
        };

        $items      = $query->paginate(8)->withQueryString();
        $categories = Category::where('outlet_id', $outletId)->orderBy('id')->get();

        return view('owner.menus.index', compact('items', 'categories', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['outlet_id'] = auth()->user()->outlet_id;
        $data['image']     = $this->storeImage($request);

        Menu::create($data);

        return back()->with('success', 'Menu "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Menu $menu)
    {
        $this->authorizeMenu($menu);

        $data = $this->validated($request, $menu);

        if ($path = $this->storeImage($request)) {
            $this->deleteImage($menu->image);
            $data['image'] = $path;
        }

        $menu->update($data);

        return back()->with('success', 'Menu "' . $menu->name . '" berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $this->authorizeMenu($menu);

        $name = $menu->name;
        $this->deleteImage($menu->image);
        $menu->delete();

        return back()->with('success', 'Menu "' . $name . '" berhasil dihapus.');
    }

    // ---------- helper ----------

    private function authorizeMenu(Menu $menu): void
    {
        abort_unless((int) $menu->outlet_id === (int) auth()->user()->outlet_id, 404);
    }

    private function validated(Request $request, ?Menu $menu = null): array
    {
        $outletId = auth()->user()->outlet_id;

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('menus', 'name')->where('outlet_id', $outletId)->ignore($menu?->id),
            ],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('outlet_id', $outletId)],
            'price'       => ['required', 'integer', 'min:0', 'max:100000000'],
            'cost_price'  => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'unit'        => ['required', Rule::in(['porsi', 'gelas', 'pcs', 'bungkus'])],
            'stock'       => ['required', 'integer', 'min:0', 'max:1000000'],
            'min_stock'   => ['required', 'integer', 'min:0', 'max:1000000'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'required'  => ':attribute wajib diisi.',
            'string'    => ':attribute tidak valid.',
            'integer'   => ':attribute harus berupa angka bulat.',
            'min'       => ':attribute minimal :min.',
            'max'       => ':attribute maksimal :max.',
            'exists'    => ':attribute tidak valid.',
            'in'        => ':attribute tidak valid.',
            'unique'    => ':attribute sudah ada, gunakan nama lain.',
            'image'     => 'File harus berupa gambar.',
            'mimes'     => 'Format foto harus JPG, PNG, atau WebP.',
            'image.max' => 'Ukuran foto maksimal 2 MB.',
        ], [
            'name'        => 'Nama menu',
            'category_id' => 'Kategori',
            'price'       => 'Harga jual',
            'cost_price'  => 'HPP',
            'unit'        => 'Satuan',
            'stock'       => 'Stok',
            'min_stock'   => 'Batas stok menipis',
            'image'       => 'Foto',
        ]);

        unset($data['image']);
        $data['cost_price']   = $data['cost_price'] ?? 0;
        $data['is_available'] = $request->boolean('is_available');

        return $data;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $name = Str::uuid() . '.' . $file->extension();
        $file->move(public_path('uploads/menus'), $name);

        return 'uploads/menus/' . $name;
    }

    private function deleteImage(?string $path): void
    {
        if ($path && Str::startsWith($path, 'uploads/menus/')) {
            File::delete(public_path($path));
        }
    }
}