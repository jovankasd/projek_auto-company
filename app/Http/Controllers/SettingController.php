<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat mengakses pengaturan.');
        }

        $allSettings = Setting::all()->groupBy('group');

        return view('dashboard.settings.edit', compact('allSettings'));
    }

    public function update(Request $request): RedirectResponse
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat mengubah pengaturan.');
        }

        $settings = $request->except(['_token', '_method']);

        foreach ($settings as $key => $value) {
            $key = preg_replace('/^([a-z]+)_/', '$1.', $key);

            $existing = Setting::where('key', $key)->first();
            if ($existing) {
                $existing->update(['value' => $value]);
            } else {
                Setting::create([
                    'key' => $key,
                    'value' => $value,
                    'group' => explode('.', $key)[0] ?? 'general',
                    'type' => is_numeric($value) ? 'integer' : 'string',
                ]);
            }
        }

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
