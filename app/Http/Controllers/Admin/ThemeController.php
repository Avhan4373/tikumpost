<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ThemeController extends Controller
{
    public function edit(): View
    {
        return view('admin.theme.edit', [
            'groups' => SiteSettings::themeSchema(),
            'values' => SiteSettings::values(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $groups = [SiteSettings::THEME_GROUP];
        $data = $request->validate(SiteSettings::rulesFor($groups));

        $values = [];

        foreach (SiteSettings::fieldsOf($groups) as $key => $field) {
            // ponytail: grup tema cuma select, tanpa boolean/image; tambah cabang bila field baru muncul.
            $values[$key] = (string) ($data[$key] ?? '');
        }

        Setting::setMany($values);

        return back()->with('status', 'Tema berhasil disimpan.');
    }
}
