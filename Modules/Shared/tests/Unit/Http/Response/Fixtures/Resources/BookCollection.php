<?php

namespace Modules\Shared\Tests\Unit\Http\Response\Fixtures\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class BookCollection extends ResourceCollection
{
    public $collects = BookResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];
    }

    public function paginationInformation($request, $paginated, $default): array
    {
        return [
            'meta' => [
                'pagination' => [
                    'total' => $default['meta']['total'] ?? 0,
                    'count' => $default['meta']['to'] ?? 0,
                    'per_page' => $default['meta']['per_page'] ?? 0,
                    'current_page' => $default['meta']['current_page'] ?? 0,
                    'total_pages' => $default['meta']['last_page'] ?? 0,
                ],
            ],
            'links' => [
                'first' => $default['links']['first'] ?? null,
                'last' => $default['links']['last'] ?? null,
                'prev' => $default['links']['prev'] ?? null,
                'next' => $default['links']['next'] ?? null,
            ],
        ];
    }
}
