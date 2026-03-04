<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'delivery_type' => $this->delivery_type,
            'total' => (float) $this->total,
            'item_count' => (int) ($this->item_count ?? 0),
            'status_updated_at' => $this->status_updated_at?->toISOString(),
        ];
    }
}
