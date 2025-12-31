<?php

namespace Modules\Shared\Http\Concerns;

use Illuminate\Http\JsonResponse;
use JsonException;

trait ResponsableTrait
{
    /**
     * @throws JsonException
     */
    public function toResponse($request): JsonResponse
    {
        $response = $this->data->toResponse($request);

        // Add meta information if provided
        if (!empty($this->meta)) {
            $content = json_decode(json: $response->getContent(), associative: true, flags: JSON_THROW_ON_ERROR);
            $content['meta'] = [
                ...($content['meta'] ?? []),
                ...$this->meta,
            ];

            // Re-encode the content with the new meta information
            $response->setContent(json_encode($content, flags: JSON_THROW_ON_ERROR));
        }

        // Set custom status code
        $response->setStatusCode($this->status);

        // Add custom headers
        foreach ($this->headers as $key => $value) {
            $response->header($key, $value);
        }

        // Ensure JSON:API content type
        $response->header('Content-Type', 'application/vnd.api+json');

        return $response;
    }
}
