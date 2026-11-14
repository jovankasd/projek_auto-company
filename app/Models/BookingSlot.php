<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slot_key
 * @property Carbon $slot_date
 * @property int $slot_hour
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BookingSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'slot_key',
        'slot_date',
        'slot_hour',
    ];

    protected function casts(): array
    {
        return [
            'slot_date' => 'date',
            'slot_hour' => 'integer',
        ];
    }
}
