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
        $total = (float) $service->items()->sum('subtotal');

        $service->update(['total_cost' => $total]);

        return $total;
    }

    /**
     * Hitung subtotal sebuah item (quantity × unit_price).
     */
    public function calculateSubtotal(float $quantity, float $unitPrice): float
    {
        return round($quantity * $unitPrice, 2);
    }
}
