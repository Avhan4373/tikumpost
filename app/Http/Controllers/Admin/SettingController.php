<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'groups' => SiteSettings::formSchema(),
            'values' => SiteSettings::values(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $groups = array_keys(SiteSettings::formSchema());
        $data = $request->validate(SiteSettings::rulesFor($groups));

        $values = [];

        foreach (SiteSettings::fieldsOf($groups) as $key => $field) {
            if ($field['type'] === 'boolean') {
                $values[$key] = $request->boolean($key) ? '1' : '0';

                continue;
            }

            if ($field['type'] === 'image') {
                if ($request->hasFile($key)) {
                    $this->deleteFile($key);
                    $values[$key] = $request->file($key)->store('settings', 'public');
                } elseif ($request->boolean($key.'_remove')) {
                    $this->deleteFile($key);
                    $values[$key] = '';
                }

                continue;
            }

            $values[$key] = (string) ($data[$key] ?? '');
        }

        Setting::setMany($values);

        return back()->with('status', 'Pengaturan berhasil disimpan.');
    }

    private function deleteFile(string $key): void
    {
        $path = Setting::raw($key);

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
