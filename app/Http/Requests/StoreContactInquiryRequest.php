<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactInquiryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', 'regex:/^[^\r\n]+$/'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'phone' => ['required', 'string', 'min:9', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'subject' => ['required', 'string', 'max:150', 'regex:/^[^\r\n]+$/'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'website_url' => ['nullable', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'phone.required' => 'Nomor telepon/WhatsApp wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'subject.required' => 'Subjek atau topik pesan wajib dipilih/diisi.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan minimal terdiri dari 10 karakter.',
            'website_url.max' => 'Spam terdeteksi.',
        ];
    }
}
