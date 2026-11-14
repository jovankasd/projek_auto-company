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
            'booking_open_hour' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
                'max:23',
                function ($attribute, $value, $fail) use ($request) {
                    $closeHour = $request->input('booking_close_hour', Setting::get('booking.close_hour', 17));
                    if ((int) $value >= (int) $closeHour) {
                        $fail('Jam buka bengkel harus lebih kecil dari jam tutup.');
                    }
                },
            ],
            'booking_close_hour' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
                'max:23',
                function ($attribute, $value, $fail) use ($request) {
                    $openHour = $request->input('booking_open_hour', Setting::get('booking.open_hour', 8));
                    if ((int) $value <= (int) $openHour) {
                        $fail('Jam tutup bengkel harus lebih besar dari jam buka.');
                    }
                },
            ],
            'booking_max_per_slot' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
            'contact_whatsapp' => ['sometimes', 'required', 'string', 'max:25', 'regex:/^[0-9+]+$/'],
            'contact_email' => ['sometimes', 'required', 'email', 'max:100'],
            'contact_address' => ['sometimes', 'required', 'string', 'max:500'],
            'seo_site_name' => ['sometimes', 'required', 'string', 'max:100'],
            'seo_tagline' => ['sometimes', 'required', 'string', 'max:200'],
        ], [
            'booking_max_per_slot.min' => 'Kuota per slot minimal 1 kendaraan.',
            'contact_whatsapp.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda plus (+).',
            'contact_email.email' => 'Format email kontak tidak valid.',
        ]);

        foreach ($validated as $key => $value) {
            $normalizedKey = preg_replace('/^([a-z]+)_/', '$1.', $key);
            $group = explode('.', $normalizedKey)[0] ?? 'general';
            $type = is_numeric($value) && in_array($normalizedKey, ['booking.open_hour', 'booking.close_hour', 'booking.max_per_slot'], true)
                ? 'integer'
                : 'string';

            Setting::set($normalizedKey, $value, $group, $type);
        }

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
