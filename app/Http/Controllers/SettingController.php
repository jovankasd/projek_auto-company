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

        $validated = $request->validate([
            'booking_open_hour' => ['nullable', 'integer', 'between:0,23'],
            'booking_close_hour' => ['nullable', 'integer', 'between:0,23'],
            'booking_max_per_slot' => ['nullable', 'integer', 'between:1,50'],
            'contact_whatsapp' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_address' => ['nullable', 'string', 'max:255'],
            'seo_site_name' => ['nullable', 'string', 'max:100'],
            'seo_tagline' => ['nullable', 'string', 'max:200'],
        ]);

        if (
            isset($validated['booking_open_hour'], $validated['booking_close_hour'])
            && (int) $validated['booking_close_hour'] < (int) $validated['booking_open_hour']
        ) {
            return back()
                ->withInput()
                ->withErrors(['booking_close_hour' => 'Jam tutup bengkel tidak boleh lebih awal dari jam buka.']);
        }

        foreach ($validated as $key => $value) {
            $normalizedKey = (string) preg_replace('/^([a-z]+)_/', '$1.', $key);
            $group = explode('.', $normalizedKey)[0] ?? 'general';
            $type = str_starts_with($normalizedKey, 'booking.') ? 'integer' : 'string';

            if ($type === 'integer' && ($value === null || $value === '')) {
                continue;
            }

            Setting::set($normalizedKey, $value, $group, $type);
        }

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
