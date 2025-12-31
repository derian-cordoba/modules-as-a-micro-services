<?php

namespace Modules\Shared\Tests\Unit\Http\Response\Fixtures\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array|null $resource
 */
final class BookResource extends JsonResource
{
    /**
     * @inheritDoc
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'order-items',
            'id' => data_get(target: $this->resource, key: 'id', default: 1),
            'attributes' => [
                'name' => data_get(target: $this->resource, key: 'name', default: 'Sample Book'),
                'price' => data_get(target: $this->resource, key: 'price', default: 9.99),
                'quantity' => data_get(target: $this->resource, key: 'quantity', default: 1),
            ],
            'relationships' => [
                'user' => [
                    'data' => [
                        'type' => 'users',
                        'id' => data_get(target: $this->resource, key: 'user_id', default: 1),
                    ],
                    'links' => [
                        'related' => data_get(
                            target: $this->resource,
                            key: 'user_link',
                            default: '/api/users',
                        ),
                    ],
                ],
            ],
            'links' => [
                'self' => data_get(
                    target: $this->resource,
                    key: 'self_link',
                    default: '/api/books',
                ),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function with(Request $request): array
    {
        return [
            'jsonapi' => [
                'version' => '1.0',
            ],
        ];
    }
}
