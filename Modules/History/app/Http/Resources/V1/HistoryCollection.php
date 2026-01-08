<?php

namespace Modules\History\Http\Resources\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\History\Models\History;

/**
 * @property-read LengthAwarePaginator<History> $resource
 */
final class HistoryCollection extends ResourceCollection
{
    public $collects = HistoryResource::class;

   public function toArray($request): array
   {
         return [
             'data' => $this->collection,
         ];
   }
}
