<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Infographic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InfographicController extends Controller
{
    public function index(): View
    {
        $infographics = Infographic::ordered()->paginate(20);

        return view('admin.infographics.index', compact('infographics'));
    }

    public function create(): View
    {
        return view('admin.infographics.form', [
            'infographic' => new Infographic(['is_published' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, requireImage: true);
        $data['image'] = $request->file('image')->store('infographics', 'public');

        Infographic::create($data);

        return redirect()->route('admin.infographics.index')->with('status', 'Infografis berhasil ditambahkan.');
    }

    public function edit(Infographic $infographic): View
    {
        return view('admin.infographics.form', compact('infographic'));
    }

    public function update(Request $request, Infographic $infographic): RedirectResponse
    {
        $data = $this->validateData($request, requireImage: false);

        if ($request->hasFile('image')) {
            if ($infographic->image) {
                Storage::disk('public')->delete($infographic->image);
            }
            $data['image'] = $request->file('image')->store('infographics', 'public');
        }

        $infographic->update($data);

        return redirect()->route('admin.infographics.index')->with('status', 'Infografis berhasil diperbarui.');
    }

    public function destroy(Infographic $infographic): RedirectResponse
    {
        if ($infographic->image) {
            Storage::disk('public')->delete($infographic->image);
        }
        $infographic->delete();

        return back()->with('status', 'Infografis berhasil dihapus.');
    }

    private function validateData(Request $request, bool $requireImage): array
    {
        $data = $request->validate([
            'image' => [$requireImage ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // File diurus terpisah (store/delete) di dalam aksi.
        unset($data['image']);

        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
