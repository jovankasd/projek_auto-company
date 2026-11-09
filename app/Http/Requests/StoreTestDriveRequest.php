<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTestDriveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'car_id' => ['required', 'integer', 'exists:cars,id,is_available,1'],
            'preferred_date' => ['required', 'date', 'after:now', 'before:+90 days'],
            'driver_license_id' => ['required', 'string', 'min:5', 'max:50'],
            'driver_license_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'car_id.required' => 'Pilihan mobil wajib dipilih.',
            'car_id.exists' => 'Mobil yang dipilih tidak valid atau sedang tidak tersedia.',
            'preferred_date.required' => 'Tanggal jadwal test drive wajib diisi.',
            'preferred_date.after' => 'Jadwal test drive tidak boleh sebelum waktu saat ini.',
            'preferred_date.before' => 'Jadwal test drive maksimal 90 hari ke depan.',
            'driver_license_id.required' => 'Nomor Surat Izin Mengemudi (SIM) wajib diisi.',
            'driver_license_id.min' => 'Nomor SIM minimal 5 karakter.',
            'driver_license_image.image' => 'Berkas foto SIM harus berupa gambar.',
            'driver_license_image.max' => 'Ukuran foto SIM maksimal 5MB.',
        ];
    }
}
