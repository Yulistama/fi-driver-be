<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return[
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role_id' => $this->role_id,
            'phone' => $this->phone,
            'position' => $this->position,
            'image' => $this->image,
            'is_status' => $this->is_status,
            'is_ready' => $this->is_ready,
            'number_vehicle' => $this->number_vehicle,
            'tranpostation_type' => $this->tranpostation_type,
            'gender_id' => $this->gender_id,
        ];
    }
}
