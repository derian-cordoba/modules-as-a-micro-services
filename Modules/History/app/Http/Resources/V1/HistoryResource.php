<?php

namespace Modules\History\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\History\Models\History;

/**
 * @property-read History $resource
 */
final class HistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'type' => 'histories',
            'id' => $this->resource->slug,
            'attributes' => [
                'id' => $this->resource->slug,
                'name' => $this->resource->name,
                'type' => $this->resource->type,
                'user_id' => $this->resource->user_id,
                'metadata' => $this->resource->metadata,
                'created_at' => $this->resource->created_at,
                'updated_at' => $this->resource->updated_at,
                'deleted_at' => $this->resource->deleted_at,
            ],
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
}
