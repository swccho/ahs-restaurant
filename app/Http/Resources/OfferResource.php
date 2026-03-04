<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OfferResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $offer = $this->resource;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value !== null ? (float) $this->value : null,
            'min_order_amount' => $this->min_order_amount !== null ? (float) $this->min_order_amount : null,
            'coupon_code' => $this->coupon_code,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'is_active' => $this->is_active,
            'banner_path' => $this->banner_path,
            'banner_url' => $this->banner_path
                ? Storage::disk('public')->url($this->banner_path)
                : null,
            'is_valid_now' => $offer->isCurrentlyValid(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
