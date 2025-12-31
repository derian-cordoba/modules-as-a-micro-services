<?php

namespace Modules\Shared\Http\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Shared\Http\Concerns\ResponsableTrait;
use Symfony\Component\HttpFoundation\Response;

final readonly class ModelResponse implements Responsable
{
    use ResponsableTrait;

    public function __construct(
        public JsonResource $data,
        public int $status = Response::HTTP_OK,
        public array $meta = [],
        public array $headers = []
    ) {
        //
    }

    /**
     * Create a response for a newly created resource.
     */
    public static function created(JsonResource $data, array $meta = []): self
    {
        return new self(
            data: $data,
            status: Response::HTTP_CREATED,
            meta: ['message' => 'Resource created successfully', ...$meta]
        );
    }

    /**
     * Create a response for a successful operation.
     */
    public static function success(JsonResource $data, array $meta = []): self
    {
        return new self(
            data: $data,
            status: Response::HTTP_OK,
            meta: $meta
        );
    }
}
