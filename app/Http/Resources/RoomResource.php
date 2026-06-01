<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * @param  Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'district' => $this->district,
            'city' => $this->city,
            'price_monthly' => $this->price_monthly,
            'deposit_amount' => $this->deposit_amount,
            'area_sqm' => $this->area_sqm,
            'max_occupants' => $this->max_occupants,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'images' => RoomImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
