<?php

namespace App\Http\Resources\V1;

use App\Support\AssetUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => AssetUrl::absolutize($this->url),
            'sort_order' => (int) $this->sort_order,
        ];
    }
}
