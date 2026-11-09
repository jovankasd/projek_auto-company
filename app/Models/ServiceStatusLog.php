<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $service_id
 * @property string|null $from_status
 * @property string $to_status
 * @property int|null $changed_by
 * @property string|null $notes
 * @property Carbon $created_at
 * @property-read Service $service
 * @property-read User|null $changedBy
 */
class ServiceStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id', 'from_status', 'to_status',
        'changed_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    // --- Relasi ---

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
