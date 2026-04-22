<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => (int) $this->order_id,
            'product_id' => $this->product_id !== null ? (int) $this->product_id : null,
            'product_name' => $this->product_name,
            'quantity' => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal' => (float) $this->subtotal,
            'image_url' => $this->whenLoaded('product', fn () => $this->product?->image_url),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
