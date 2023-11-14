<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status_id',
        'staff_id',
        'driver_id',
        'pickup_city_id',
        'destination_city_id',
        'pickup_address',
        'destination_address',
        'estimated_pickup_time',
        'estimated_finish_time',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "staff_id", "id");
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, "driver_id", "id");
    }

    public function pickup_city(): BelongsTo
    {
        return $this->belongsTo(City::class, "pickup_city_id", "id");
    }

    public function destination_city(): BelongsTo
    {
        return $this->belongsTo(City::class, "destination_city_id", "id");
    }

    public function status_booking(): BelongsTo
    {
        return $this->belongsTo(StatusBooking::class, "status_id", "id");
    }

    public function history(): HasMany
    {
        return $this->hasMany(HistoryStatusBooking::class, 'booking_id', 'id');
    }
}
