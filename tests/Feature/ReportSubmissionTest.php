<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\ExecutiveReportNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReportSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->owner = User::factory()->create([
            'role' => UserRole::Owner,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_super_admin_successfully_submits_report_and_status_is_sent(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->superAdmin)->post(route('reports.submit-to-owner'), [
            'note' => 'Laporan mingguan 3 divisi lengkap.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('sent', Setting::get('last_report_status'));
        $this->assertNotNull(Setting::get('last_report_submitted_at'));
        $this->assertEquals($this->superAdmin->name, Setting::get('last_report_submitted_by'));
        $this->assertEquals('Laporan mingguan 3 divisi lengkap.', Setting::get('last_report_note'));
        $this->assertNull(Setting::get('last_report_error'));

        Notification::assertSentTo($this->owner, ExecutiveReportNotification::class);
    }

    public function test_submitting_report_fails_gracefully_when_notification_throws_exception(): void
    {
        // Mock notification to throw an exception
        Notification::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('Koneksi mail/database notification terputus.'));

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function (string $message) {
                return str_contains($message, 'Gagal mengirim laporan eksekutif ke Owner');
            });

        $response = $this->actingAs($this->superAdmin)->post(route('reports.submit-to-owner'), [
            'note' => 'Laporan gagal kirim.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $response->assertSessionMissing('success');

        $this->assertEquals('failed', Setting::get('last_report_status'));
        $this->assertNotNull(Setting::get('last_report_error'));
        $this->assertStringContainsString('Koneksi mail/database notification terputus', (string) Setting::get('last_report_error'));
        $this->assertNotNull(Setting::get('last_report_failed_at'));
        $this->assertNull(Setting::get('last_report_submitted_at'));
    }

    public function test_submitting_report_fails_when_no_active_owner_exists(): void
    {
        // Deactivate the owner
        $this->owner->update(['is_active' => false]);

        $response = $this->actingAs($this->superAdmin)->post(route('reports.submit-to-owner'), [
            'note' => 'Cek tanpa owner.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertEquals('failed', Setting::get('last_report_status'));
        $this->assertStringContainsString('Tidak ada akun Owner aktif', (string) Setting::get('last_report_error'));
    }

    public function test_retry_mechanism_is_idempotent_and_does_not_duplicate_already_sent_reports(): void
    {
        Notification::fake();

        // Mark report as already sent
        Setting::set('last_report_status', 'sent', 'reports');
        Setting::set('last_report_submitted_at', now()->toIso8601String(), 'reports');
        Setting::set('last_report_submitted_by', $this->superAdmin->name, 'reports');

        $response = $this->actingAs($this->superAdmin)->post(route('reports.retry-submission'));

        $response->assertRedirect();
        $response->assertSessionHas('info');

        Notification::assertNothingSent();
    }

    public function test_retry_succeeds_when_retrying_failed_report(): void
    {
        Notification::fake();

        // Setup failed report
        Setting::set('last_report_status', 'failed', 'reports');
        Setting::set('last_report_error', 'Gagal sebelumnya', 'reports');
        Setting::set('last_report_note', 'Catatan laporan retry.', 'reports');

        $response = $this->actingAs($this->superAdmin)->post(route('reports.retry-submission'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('sent', Setting::get('last_report_status'));
        $this->assertNull(Setting::get('last_report_error'));
        $this->assertNotNull(Setting::get('last_report_submitted_at'));

        Notification::assertSentTo($this->owner, ExecutiveReportNotification::class);
    }

    public function test_owner_dashboard_only_displays_verified_status_when_sent(): void
    {
        // When failed
        Setting::set('last_report_status', 'failed', 'reports');
        Setting::set('last_report_submitted_at', null, 'reports');

        $response = $this->actingAs($this->owner)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('Resmi Terkirim');
        $response->assertSee('Sinkronisasi Otomatis');

        // When sent
        Setting::set('last_report_status', 'sent', 'reports');
        Setting::set('last_report_submitted_at', '2026-11-14 12:30:00', 'reports');

        $response = $this->actingAs($this->owner)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Resmi Terkirim');
        $response->assertSee('19:30 WIB');
    }
}
