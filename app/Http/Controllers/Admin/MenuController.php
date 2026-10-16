<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function index(): View
    {
        return view('admin.menus.index', [
            'menus' => Menu::roots()->ordered()->with('children')->get(),
            'parents' => Menu::roots()->ordered()->get(),
            'nextOrder' => (int) Menu::max('sort_order') + 1,
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.form', [
            'menu' => new Menu(),
            'parents' => Menu::roots()->ordered()->get(),
            'nextOrder' => (int) Menu::max('sort_order') + 1,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Menu::create($this->validateData($request));

        return redirect()->route('admin.menus.index')->with('status', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu): View
    {
        return view('admin.menus.form', [
            'menu' => $menu,
            'parents' => Menu::roots()->whereKeyNot($menu->id)->ordered()->get(),
            'nextOrder' => $menu->sort_order,
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $menu->update($this->validateData($request, $menu));

        return redirect()->route('admin.menus.index')->with('status', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete(); // anak-anak menu ikut terhapus (cascade).

        return back()->with('status', 'Menu berhasil dihapus.');
    }

    /**
     * Simpan urutan & struktur menu hasil drag & drop.
     */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['present', 'array'],
            'items.*.id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:menus,id'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // Menu dibatasi maksimal dua tingkat: hanya item induk (tanpa parent)
        // yang boleh menjadi parent. Sisanya dipromosikan ke tingkat atas.
        $rootIds = collect($data['items'])
            ->filter(fn (array $item) => empty($item['parent_id']))
            ->pluck('id');

        DB::transaction(function () use ($data, $rootIds) {
            foreach ($data['items'] as $row) {
                $parentId = $row['parent_id'] ?? null;

                if ($parentId === $row['id'] || ($parentId && ! $rootIds->contains($parentId))) {
                    $parentId = null;
                }

                Menu::whereKey($row['id'])->update([
                    'parent_id' => $parentId,
                    'sort_order' => $row['sort_order'],
                ]);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    /**
     * Tambahkan semua kategori sebagai item menu (sekali klik).
     */
    public function importCategories(): RedirectResponse
    {
        $existing = Menu::pluck('url');
        $order = (int) Menu::max('sort_order');
        $created = 0;

        foreach (Category::ordered()->get() as $category) {
            $url = '/kategori/'.$category->slug;

            if ($existing->contains($url)) {
                continue;
            }

            Menu::create([
                'label' => $category->name,
                'url' => $url,
                'sort_order' => ++$order,
                'is_active' => true,
            ]);
            $created++;
        }

        return back()->with('status', $created > 0
            ? "{$created} kategori ditambahkan ke menu."
            : 'Semua kategori sudah ada di menu.');
    }

    private function validateData(Request $request, ?Menu $menu = null): array
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'url' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('menus', 'id')->whereNull('parent_id'),
                Rule::notIn([$menu?->id]),
            ],
            'target' => ['required', Rule::in(['_self', '_blank'])],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['parent_id'] = $data['parent_id'] ?? null;
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
