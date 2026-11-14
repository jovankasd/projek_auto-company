<?php

namespace App\Services;

use App\Models\Service;

class ServiceCostCalculator
{
    /**
     * Hitung ulang total_cost dari semua service_items (BR-09) dan terapkan diskon voucher jika ada.
     */
    public function recalculate(Service $service): float
    {
        $grossTotal = (float) $service->items()->sum('subtotal');
        $discountAmount = 0.0;

        if ($service->promo_id) {
            $promo = $service->promo;
            if ($promo) {
                $discountAmount = $promo->calculateDiscount($grossTotal);
            }
        }

        $netTotal = max(0.0, round($grossTotal - $discountAmount, 2));

        $service->update([
            'discount_amount' => $discountAmount,
            'total_cost' => $netTotal,
        ]);

        return $netTotal;
    }

    /**
     * Hitung subtotal sebuah item (quantity × unit_price).
     */
    public function calculateSubtotal(float $quantity, float $unitPrice): float
    {
        return round($quantity * $unitPrice, 2);
    }
}
