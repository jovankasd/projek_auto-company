<?php

namespace App\Models;

use App\Enums\ServiceItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id', 'item_type', 'name', 'part_number',
        'quantity', 'unit_price', 'subtotal', 'added_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'item_type' => ServiceItemType::class,
        ];
    }

    // --- Relasi ---

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
