<?php

use App\Enums\CarCategory;
use App\Enums\DiscountType;
use App\Enums\ServiceItemType;
use App\Enums\ServiceStatus;
use App\Enums\TestDriveStatus;
use App\Enums\UserRole;

test('all backed enums provide valid non-empty label and color', function () {
    $enumClasses = [
        UserRole::class,
        CarCategory::class,
        ServiceStatus::class,
        TestDriveStatus::class,
        DiscountType::class,
        ServiceItemType::class,
    ];

    foreach ($enumClasses as $enumClass) {
        foreach ($enumClass::cases() as $case) {
            expect($case->label())->toBeString()->not->toBeEmpty();
            expect($case->color())->toBeString()->not->toBeEmpty();
        }
    }
});

test('ServiceStatus state machine strictly enforces valid transitions', function () {
    // Pending transitions
    expect(ServiceStatus::Pending->canTransitionTo(ServiceStatus::Confirmed))->toBeTrue();
    expect(ServiceStatus::Pending->canTransitionTo(ServiceStatus::Cancelled))->toBeTrue();
    expect(ServiceStatus::Pending->canTransitionTo(ServiceStatus::Completed))->toBeFalse();

    // Confirmed transitions
    expect(ServiceStatus::Confirmed->canTransitionTo(ServiceStatus::InProgress))->toBeTrue();
    expect(ServiceStatus::Confirmed->canTransitionTo(ServiceStatus::Cancelled))->toBeTrue();
    expect(ServiceStatus::Confirmed->canTransitionTo(ServiceStatus::Pending))->toBeFalse();

    // In Progress transitions
    expect(ServiceStatus::InProgress->canTransitionTo(ServiceStatus::Completed))->toBeTrue();
    expect(ServiceStatus::InProgress->canTransitionTo(ServiceStatus::Cancelled))->toBeFalse();

    // Terminal states cannot transition
    expect(ServiceStatus::Completed->canTransitionTo(ServiceStatus::Pending))->toBeFalse();
    expect(ServiceStatus::Cancelled->canTransitionTo(ServiceStatus::Confirmed))->toBeFalse();

    // Terminal and cancellation predicates
    expect(ServiceStatus::Completed->isTerminal())->toBeTrue();
    expect(ServiceStatus::Cancelled->isTerminal())->toBeTrue();
    expect(ServiceStatus::Pending->isTerminal())->toBeFalse();
    expect(ServiceStatus::Confirmed->isTerminal())->toBeFalse();
    expect(ServiceStatus::InProgress->isTerminal())->toBeFalse();

    expect(ServiceStatus::Pending->canBeCancelled())->toBeTrue();
    expect(ServiceStatus::Confirmed->canBeCancelled())->toBeTrue();
    expect(ServiceStatus::InProgress->canBeCancelled())->toBeFalse();
    expect(ServiceStatus::Completed->canBeCancelled())->toBeFalse();
    expect(ServiceStatus::Cancelled->canBeCancelled())->toBeFalse();
});
