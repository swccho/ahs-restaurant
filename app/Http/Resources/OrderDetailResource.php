<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'order_note' => $this->order_note,
            'delivery_type' => $this->delivery_type,
            'subtotal' => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'discount_amount' => (float) $this->discount_amount,
            'total' => (float) $this->total,
            'cancel_reason' => $this->cancel_reason,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'items' => $this->whenLoaded('orderItems', fn () => $this->orderItems->map(fn ($item) => [
                'id' => $item->id,
                'item_name_snapshot' => $item->item_name_snapshot,
                'unit_price_snapshot' => (float) $item->unit_price_snapshot,
                'qty' => (int) $item->qty,
                'line_total' => (float) $item->line_total,
            ])),
            'status_audit' => [
                'status_updated_at' => $this->status_updated_at?->toISOString(),
                'status_updated_by' => $this->whenLoaded('statusUpdatedBy', fn () => $this->statusUpdatedBy ? [
                    'id' => $this->statusUpdatedBy->id,
                    'name' => $this->statusUpdatedBy->name,
                ] : null),
            ],
        ];
    }
}
