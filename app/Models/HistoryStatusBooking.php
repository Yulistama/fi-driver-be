<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryStatusBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_history_id',
        'booking_id',
        'date_time',
        'image',
        'is_read',
        'description',
        'location',
    ];

    public function status_history(): BelongsTo
    {
        return $this->belongsTo(StatusHistory::class, "status_history_id", "id");
    }
}
