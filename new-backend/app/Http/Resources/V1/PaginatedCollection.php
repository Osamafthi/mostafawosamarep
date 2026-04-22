<?php

namespace App\Http\Resources\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class PaginatedCollection
{
    /**
     * Shape a LengthAwarePaginator to the legacy payload:
     * { items, total, page, limit, last_page }.
     *
     * @param  class-string<JsonResource>  $resourceClass
     */
    public static function toArray(LengthAwarePaginator $paginator, string $resourceClass): array
    {
        $items = $resourceClass::collection($paginator->getCollection())->resolve();

        return [
            'items' => $items,
            'total' => $paginator->total(),
            'page' => $paginator->currentPage(),
            'limit' => $paginator->perPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
