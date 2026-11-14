<?php

namespace App\Services;

use App\Enums\ServiceStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TestDrive;
use App\Models\User;
use App\Notifications\ExecutiveReportNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class ExecutiveReportService
{
    /**
     * Verifikasi data operasional dan kirim notifikasi laporan ke akun Owner.
     *
     * @return array{success: bool, status: string, message: string}
     */
    public function submit(User $user, ?string $note = null): array
    {
        $note = $note ?: 'Laporan operasional 3 divisi (Sales, Admin Mekanik, Customer) telah diverifikasi lengkap.';

        $summary = [
            'total_services' => Service::where('status', ServiceStatus::Completed)->count(),
            'available_cars' => Car::available()->count(),
            'total_test_drives' => TestDrive::count(),
            'total_revenue' => (float) Service::where('status', ServiceStatus::Completed)->sum('total_cost'),
        ];

        $owners = User::where('role', UserRole::Owner)->where('is_active', true)->get();

        if ($owners->isEmpty()) {
            $errorMsg = 'Tidak ada akun Owner aktif yang terdaftar untuk menerima laporan.';
            Log::error("Gagal mengirim laporan eksekutif ke Owner: {$errorMsg}");

            Setting::set('last_report_status', 'failed', 'reports');
            Setting::set('last_report_error', $errorMsg, 'reports');
            Setting::set('last_report_failed_at', now()->toIso8601String(), 'reports');
            Setting::set('last_report_submitted_by', $user->name, 'reports');
            Setting::set('last_report_note', $note, 'reports');

            return [
                'success' => false,
                'status' => 'failed',
                'message' => $errorMsg,
            ];
        }

        try {
            Setting::set('last_report_status', 'pending', 'reports');
            Setting::set('last_report_submitted_by', $user->name, 'reports');
            Setting::set('last_report_note', $note, 'reports');

            Notification::send($owners, new ExecutiveReportNotification(
                title: 'Laporan Eksekutif Terpadu Dikirim oleh Super Admin',
                message: "Super Admin ({$user->name}) telah memverifikasi dan mengirimkan laporan keseluruhan (Sales, Admin Mekanik, Customer).",
                url: route('dashboard'),
                summary: $summary
            ));

            Setting::set('last_report_status', 'sent', 'reports');
            Setting::set('last_report_submitted_at', now()->toIso8601String(), 'reports');
            Setting::set('last_report_error', null, 'reports');

            return [
                'success' => true,
                'status' => 'sent',
                'message' => 'Laporan operasional 3 divisi berhasil diverifikasi & dikirimkan ke Owner!',
            ];
        } catch (Throwable $e) {
            Log::error("Gagal mengirim laporan eksekutif ke Owner: {$e->getMessage()}", [
                'exception' => $e,
                'submitted_by' => $user->name,
            ]);

            Setting::set('last_report_status', 'failed', 'reports');
            Setting::set('last_report_error', $e->getMessage(), 'reports');
            Setting::set('last_report_failed_at', now()->toIso8601String(), 'reports');

            return [
                'success' => false,
                'status' => 'failed',
                'message' => "Gagal mengirimkan laporan ke Owner: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Retry pengiriman laporan secara idempoten:
     * Jika sudah berstatus 'sent', cegah pengiriman ganda.
     *
     * @return array{success: bool, status: string, message: string}
     */
    public function retry(?User $user = null): array
    {
        $status = Setting::get('last_report_status');

        if ($status === 'sent') {
            return [
                'success' => true,
                'status' => 'already_sent',
                'message' => 'Laporan sudah berstatus terkirim (sent). Tidak ada pengiriman ulang yang diproses.',
            ];
        }

        /** @var User $actor */
        $actor = $user ?? auth()->user();
        $note = Setting::get('last_report_note', 'Laporan operasional 3 divisi telah diverifikasi lengkap.');

        return $this->submit($actor, $note);
    }

    /**
     * Mengambil status pengiriman laporan terakhir.
     *
     * @return array{status: string|null, submitted_at: string|null, submitted_by: string|null, note: string|null, error: string|null, failed_at: string|null}
     */
    public function getStatus(): array
    {
        return [
            'status' => Setting::get('last_report_status'),
            'submitted_at' => Setting::get('last_report_submitted_at'),
            'submitted_by' => Setting::get('last_report_submitted_by'),
            'note' => Setting::get('last_report_note'),
            'error' => Setting::get('last_report_error'),
            'failed_at' => Setting::get('last_report_failed_at'),
        ];
    }
}
