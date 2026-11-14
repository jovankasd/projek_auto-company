<?php

namespace App\Services;

use App\Models\Service;

class ServiceCostCalculator
{
    /**
     * Hitung ulang total_cost dari semua service_items (BR-09).
     */
    public function recalculate(Service $service): float
    {
        if ($service->status->isTerminal()) {
            return (float) $service->total_cost;
        }

        $total = (float) $service->items()->sum('subtotal');

        $service->update(['total_cost' => $total]);

        return $total;
    }

    /**
     * Hitung total dari service_items tanpa mutasi model.
     */
    public function calculateTotal(Service $service): float
    {
        return (float) $service->items()->sum('subtotal');
    }

    /**
     * Hitung subtotal sebuah item (quantity × unit_price).
     */
    public function calculateSubtotal(float $quantity, float $unitPrice): float
    {
        return round($quantity * $unitPrice, 2);
    }
}
