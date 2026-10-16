<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reel;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReelController extends Controller
{
    public function index(): View
    {
        $reels = Reel::ordered()->paginate(20);

        return view('admin.reels.index', compact('reels'));
    }

    public function create(): View
    {
        return view('admin.reels.form', [
            'reel' => new Reel(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Reel::create($this->validateData($request));

        return redirect()->route('admin.reels.index')->with('status', 'Reel berhasil ditambahkan.');
    }

    public function edit(Reel $reel): View
    {
        return view('admin.reels.form', compact('reel'));
    }

    public function update(Request $request, Reel $reel): RedirectResponse
    {
        $reel->update($this->validateData($request));

        return redirect()->route('admin.reels.index')->with('status', 'Reel berhasil diperbarui.');
    }

    public function destroy(Reel $reel): RedirectResponse
    {
        $reel->delete();

        return back()->with('status', 'Reel berhasil dihapus.');
    }

    private function validateData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'url' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! Reel::isValidInstagramUrl($value)) {
                        $fail('URL harus berupa tautan Reel/Post Instagram yang valid, contoh: https://www.instagram.com/reel/xxxxxxxxx/');
                    }
                },
            ],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
