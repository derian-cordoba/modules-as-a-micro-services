<?php

namespace Modules\Shared\Http\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Shared\Http\Concerns\ResponsableTrait;
use Symfony\Component\HttpFoundation\Response;

final readonly class CollectionResponse implements Responsable
{
    use ResponsableTrait;

    public function __construct(
        public ResourceCollection $data,
        public int $status = Response::HTTP_OK,
        public array $meta = [],
        public array $headers = []
    ) {
        //
    }

    public static function success(ResourceCollection $data, array $meta = []): self
    {
        return new self(
            data: $data,
            status: Response::HTTP_OK,
            meta: $meta
        );
    }
}
