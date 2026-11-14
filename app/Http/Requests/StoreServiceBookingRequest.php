<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreServiceBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $hasExplicitSlot = $this->has('slot_hour') && $this->input('slot_hour') !== null && $this->input('slot_hour') !== '';

        if (! $hasExplicitSlot && $this->filled('service_date')) {
            try {
                $rawDate = (string) $this->input('service_date');
                if (preg_match('/[T ]\d{1,2}:\d{2}/', $rawDate)) {
                    $this->merge([
                        'slot_hour' => (int) Carbon::parse($rawDate)->hour,
                    ]);
                }
            } catch (\Throwable) {
                // Ignore parse errors, let standard date validation rule handle it
            }
        }

        if ($this->has('slot_hour') && $this->input('slot_hour') !== null && $this->filled('service_date')) {
            try {
                $hour = (int) $this->input('slot_hour');
                $combined = Carbon::parse($this->input('service_date'))->setTime($hour, 0, 0);
                $this->merge([
                    'service_date' => $combined->format('Y-m-d H:i:s'),
                ]);
            } catch (\Throwable) {
                // Ignore parse errors
            }
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxYear = (int) date('Y') + 1;
        $openHour = (int) Setting::get('booking.open_hour', 8);
        $closeHour = (int) Setting::get('booking.close_hour', 17);

        return [
            'vehicle_plate_number' => ['required', 'string', 'regex:/^[A-Za-z]{1,2}\s?[0-9]{1,4}\s?[A-Za-z]{1,3}$/'],
            'vehicle_model' => ['required', 'string', 'max:100'],
            'vehicle_year' => ['required', 'integer', 'min:1990', "max:{$maxYear}"],
            'odometer_km' => ['required', 'integer', 'min:0'],
            'service_type' => ['required', 'string', 'in:regular,major,inspection,warranty'],
            'service_date' => ['required', 'date', 'after:now'],
            'slot_hour' => ['nullable', 'integer', "between:{$openHour},{$closeHour}"],
            'complaint_notes' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $openHour = (int) Setting::get('booking.open_hour', 8);
        $closeHour = (int) Setting::get('booking.close_hour', 17);

        return [
            'vehicle_plate_number.required' => 'Nomor plat kendaraan wajib diisi.',
            'vehicle_plate_number.regex' => 'Format plat nomor tidak valid (contoh: B 1234 XYZ).',
            'vehicle_model.required' => 'Model kendaraan wajib diisi.',
            'vehicle_year.required' => 'Tahun pembuatan kendaraan wajib diisi.',
            'vehicle_year.min' => 'Tahun kendaraan minimal 1990.',
            'odometer_km.required' => 'Jarak tempuh (odometer) wajib diisi.',
            'service_type.required' => 'Jenis layanan servis wajib dipilih.',
            'service_type.in' => 'Pilihan jenis servis tidak valid.',
            'service_date.required' => 'Tanggal dan jam servis wajib diisi.',
            'service_date.after' => 'Tanggal servis tidak boleh sebelum hari ini atau waktu lampau.',
            'slot_hour.between' => "Jam kedatangan harus berada di antara jam operasional ({$openHour}:00 - {$closeHour}:00 WIB).",
            'complaint_notes.required' => 'Catatan keluhan kendaraan wajib diisi.',
            'complaint_notes.min' => 'Catatan keluhan minimal 10 karakter.',
        ];
    }
}
