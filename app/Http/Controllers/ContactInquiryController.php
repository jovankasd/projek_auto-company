<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreContactInquiryRequest;
use App\Models\ContactInquiry;
use App\Models\User;
use App\Notifications\ContactInquiryNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class ContactInquiryController extends Controller
{
    public function store(StoreContactInquiryRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['website_url']);

        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();
        $validated['user_id'] = $request->user()?->id;

        $inquiry = ContactInquiry::create($validated);

        // Notify staff (Super Admin & Sales)
        $recipients = User::whereIn('role', [UserRole::SuperAdmin, UserRole::Sales])
            ->where('is_active', true)
            ->get();

        Notification::send($recipients, new ContactInquiryNotification($inquiry));

        $message = 'Terima kasih, pesan Anda berhasil dikirim. Tim konsultan kami akan segera menghubungi Anda.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => $message,
                'data' => [
                    'id' => $inquiry->id,
                    'name' => $inquiry->name,
                    'subject' => $inquiry->subject,
                ],
            ], 201);
        }

        return back()->with('contact_success', $message);
    }
}
