<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status_id' => $this->status_id,
            'staff_id' => $this->staff_id,
            'driver_id' => $this->driver_id,
            'pickup_city_id' => $this->pickup_city_id,
            'destination_city_id' => $this->destination_city_id,
            'pickup_address' => $this->pickup_address,
            'destination_address' => $this->destination_address,
            'estimated_pickup_time' => $this->estimated_pickup_time,
            'estimated_finish_time' => $this->estimated_finish_time,
            'note' => $this->note,
        ];
    }
}
