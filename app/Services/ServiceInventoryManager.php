<?php

namespace App\Services;

use App\Enums\ServiceItemType;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\SparePart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceInventoryManager
{
    public function __construct(
        private ServiceCostCalculator $calculator,
    ) {}

    /**
     * Tambahkan item onderdil atau jasa ke work order servis secara transaksional.
     * Untuk part: nama, no part, dan harga satuan selalu diambil resmi dari server (SparePart),
     * serta stok dikurangi secara atomik dengan database lock.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function addItem(Service $service, array $data, int $userId): ServiceItem
    {
        if ($service->status->isTerminal()) {
            throw ValidationException::withMessages([
                'status' => 'Tidak dapat menambahkan item pada tiket servis yang telah selesai atau dibatalkan.',
            ]);
        }

        /** @var ServiceItem $item */
        $item = DB::transaction(function () use ($service, $data, $userId) {
            $isPart = ($data['item_type'] ?? '') === ServiceItemType::Part->value || ! empty($data['spare_part_id']);

            if ($isPart) {
                $sparePart = null;

                if (! empty($data['spare_part_id'])) {
                    $sparePart = SparePart::where('id', $data['spare_part_id'])->lockForUpdate()->first();
                } elseif (! empty($data['part_number'])) {
                    $sparePart = SparePart::where('part_number', $data['part_number'])->lockForUpdate()->first();
                }

                if (! $sparePart) {
                    throw ValidationException::withMessages([
                        'spare_part_id' => 'Suku cadang wajib dipilih dari katalog master inventaris resmi.',
                    ]);
                }

                if (! $sparePart->is_available) {
                    throw ValidationException::withMessages([
                        'spare_part_id' => 'Suku cadang ini sedang ditandai tidak tersedia untuk perbaikan.',
                    ]);
                }

                $qty = (int) ($data['quantity'] ?? 1);
                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Jumlah suku cadang minimal 1.',
                    ]);
                }

                if ($sparePart->stock_quantity < $qty) {
                    throw ValidationException::withMessages([
                        'quantity' => "Stok suku cadang tidak mencukupi (tersedia: {$sparePart->stock_quantity}, diminta: {$qty}).",
                    ]);
                }

                $sparePart->decrement('stock_quantity', $qty);

                $unitPrice = (float) $sparePart->price;
                $subtotal = $this->calculator->calculateSubtotal($qty, $unitPrice);

                $item = $service->items()->create([
                    'spare_part_id' => $sparePart->id,
                    'item_type' => ServiceItemType::Part,
                    'name' => $sparePart->name,
                    'part_number' => $sparePart->part_number,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'added_by' => $userId,
                ]);
            } else {
                $itemType = ServiceItemType::tryFrom((string) ($data['item_type'] ?? '')) ?? ServiceItemType::Labor;
                $qty = (float) ($data['quantity'] ?? 1);
                $unitPrice = (float) ($data['unit_price'] ?? 0);
                $subtotal = $this->calculator->calculateSubtotal($qty, $unitPrice);

                $item = $service->items()->create([
                    'spare_part_id' => null,
                    'item_type' => $itemType,
                    'name' => $data['name'],
                    'part_number' => $data['part_number'] ?? null,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'added_by' => $userId,
                ]);
            }

            $this->calculator->recalculate($service);

            return $item;
        });

        return $item;
    }

    /**
     * Hapus item dari work order dan pulihkan stok suku cadang jika relevan.
     *
     * @throws ValidationException
     */
    public function removeItem(Service $service, ServiceItem $item): void
    {
        if ($service->status->isTerminal()) {
            throw ValidationException::withMessages([
                'status' => 'Tidak dapat menghapus item pada tiket servis yang telah selesai atau dibatalkan.',
            ]);
        }

        if ($item->service_id !== $service->id) {
            abort(404, 'Item tidak ditemukan pada tiket servis ini.');
        }

        DB::transaction(function () use ($service, $item) {
            if ($item->spare_part_id) {
                $sparePart = SparePart::where('id', $item->spare_part_id)->lockForUpdate()->first();
                if ($sparePart) {
                    $sparePart->increment('stock_quantity', (int) $item->quantity);
                }
            }

            $item->delete();

            $this->calculator->recalculate($service);
        });
    }
}
