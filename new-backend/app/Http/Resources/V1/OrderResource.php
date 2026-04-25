<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id !== null ? (int) $this->customer_id : null,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'shipping_address' => $this->shipping_address,
            'customer_location' => $this->customerLocationPayload(),
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'delivery_person' => $this->deliveryPersonPayload(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }

    /**
     * Always return either { lat, lng, maps_url } when GPS is captured,
     * or { address, maps_url } when only the text address exists, so the
     * frontend has a single shape to render.
     */
    private function customerLocationPayload(): ?array
    {
        $mapsUrl = $this->resource->mapsUrl();

        if ($this->customer_latitude !== null && $this->customer_longitude !== null) {
            return [
                'lat' => (float) $this->customer_latitude,
                'lng' => (float) $this->customer_longitude,
                'maps_url' => $mapsUrl,
                'source' => 'gps',
            ];
        }

        if ($mapsUrl !== null) {
            return [
                'lat' => null,
                'lng' => null,
                'maps_url' => $mapsUrl,
                'source' => 'address',
            ];
        }

        return null;
    }

    /**
     * Compact courier embed — only the bits the order screens need.
     * Returns null when no courier is assigned (the unassigned pool case).
     */
    private function deliveryPersonPayload(): ?array
    {
        if (! $this->relationLoaded('deliveryPerson') || $this->deliveryPerson === null) {
            return null;
        }

        return [
            'id' => (int) $this->deliveryPerson->id,
            'name' => $this->deliveryPerson->name,
            'phone' => $this->deliveryPerson->phone,
        ];
    }
}
