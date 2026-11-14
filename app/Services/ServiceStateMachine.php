<?php

namespace App\Services;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ServiceStateMachine
{
    public function __construct(
        private ServiceCostCalculator $costCalculator,
    ) {}

    /**
     * Eksekusi transisi status servis dengan guard bisnis PRD §5.1.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws DomainException
     */
    public function transition(
        Service $service,
        ServiceStatus $nextStatus,
        array $attributes = [],
        ?User $actor = null
    ): Service {
        return DB::transaction(function () use ($service, $nextStatus, $attributes, $actor) {
            $lockedService = Service::where('id', $service->id)->lockForUpdate()->firstOrFail();
            $currentStatus = $lockedService->status;

            if ($currentStatus->isTerminal()) {
                throw new DomainException("Work order servis dengan status {$currentStatus->label()} telah mencapai tahap akhir (terminal) dan tidak dapat diubah lagi.");
            }

            if (! $currentStatus->canTransitionTo($nextStatus)) {
                throw new DomainException("Transisi dari status {$currentStatus->label()} ke {$nextStatus->label()} tidak diizinkan.");
            }

            if ($nextStatus === ServiceStatus::Confirmed) {
                if (! empty($attributes['assigned_mechanic_id'])) {
                    $lockedService->assigned_mechanic_id = (int) $attributes['assigned_mechanic_id'];
                }
                $lockedService->confirmed_at = now();
            } elseif ($nextStatus === ServiceStatus::InProgress) {
                if (empty($lockedService->assigned_mechanic_id)) {
                    throw new DomainException('Tidak dapat memulai pengerjaan servis tanpa mekanik yang ditugaskan.');
                }
                $lockedService->started_at = now();
            } elseif ($nextStatus === ServiceStatus::Completed) {
                if ($lockedService->items()->count() === 0) {
                    throw new DomainException('Tidak dapat menyelesaikan servis tanpa rincian item suku cadang atau jasa pengerjaan.');
                }

                $finalCost = $this->costCalculator->calculateTotal($lockedService);
                $lockedService->total_cost = (string) $finalCost;
                $lockedService->completed_at = now();
            } elseif ($nextStatus === ServiceStatus::Cancelled) {
                $reason = trim((string) ($attributes['cancellation_reason'] ?? ''));
                if (empty($reason)) {
                    throw new DomainException('Alasan pembatalan wajib diisi.');
                }
                $lockedService->cancelled_at = now();
                $lockedService->cancellation_reason = $reason;
            }

            if (! empty($attributes['mechanic_notes'])) {
                $lockedService->mechanic_notes = $attributes['mechanic_notes'];
            }

            if (! empty($attributes['recommendation'])) {
                $lockedService->recommendation = $attributes['recommendation'];
            }

            $actorName = $actor !== null ? $actor->name : (auth()->check() ? auth()->user()->name : 'Sistem');
            $lockedService->status_change_notes = $attributes['notes'] ?? "Status diubah menjadi {$nextStatus->label()} oleh {$actorName}.";
            $lockedService->status = $nextStatus;
            $lockedService->save();

            return $lockedService;
        });
    }

    /**
     * Pembatalan servis oleh customer pemilik atau Super Admin.
     *
     * @throws DomainException
     */
    public function cancel(Service $service, string $reason, ?User $actor = null): Service
    {
        return $this->transition(
            $service,
            ServiceStatus::Cancelled,
            [
                'cancellation_reason' => $reason,
                'notes' => 'Servis dibatalkan dengan alasan: '.$reason,
            ],
            $actor
        );
    }
}
