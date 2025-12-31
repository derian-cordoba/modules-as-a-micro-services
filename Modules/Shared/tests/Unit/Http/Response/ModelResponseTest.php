<?php

namespace Modules\Shared\Tests\Unit\Http\Response;

use Illuminate\Http\Request;
use JsonException;
use Modules\Shared\Http\Response\ModelResponse;
use Modules\Shared\Tests\Unit\Http\Response\Concerns\ModelResponseProviderTrait;
use Modules\Shared\Tests\Unit\Http\Response\Fixtures\Resources\BookResource;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Throwable;
use TypeError;

final class ModelResponseTest extends TestCase
{
    use ModelResponseProviderTrait;

    #[DataProvider(methodName: 'bookResourceProvider')]
    public function test_it_can_create_a_model_response_for_created_resource(?array $resource): void
    {
        // Given
        $bookResource = new BookResource(resource: $resource);

        // When
        $response = ModelResponse::created(data: $bookResource);

        // Then
        $this->assertEquals(expected: Response::HTTP_CREATED, actual: $response->status);
        $this->assertArrayHasKey(key: 'message', array: $response->meta);
        $this->assertEquals(expected: 'Resource created successfully', actual: $response->meta['message']);
        $this->assertSame(expected: $bookResource, actual: $response->data);
    }

    #[DataProvider(methodName: 'successResourceProvider')]
    public function test_it_can_create_a_model_response_for_success_resource(?array $resource): void
    {
        // Given
        $bookResource = new BookResource(resource: $resource);

        // When
        $response = ModelResponse::success(data: $bookResource);

        // Then
        $this->assertEquals(expected: Response::HTTP_OK, actual: $response->status);
        $this->assertEmpty(actual: $response->meta);
        $this->assertSame(expected: $bookResource, actual: $response->data);
    }

    #[DataProvider(methodName: 'toResponseProvider')]
    public function test_it_can_create_a_model_response_using_to_response_method(
        ?array $resource,
        ?Request $request,
    ): void {
        // Given
        $bookResource = new BookResource(resource: $resource);
        $modelResponse = ModelResponse::success(data: $bookResource);

        try {
            // When
            $response = $modelResponse->toResponse($request);
            $responseData = json_decode(json: $response->getContent(), associative: true, flags: JSON_THROW_ON_ERROR);

            // Then
            $this->assertEquals(expected: Response::HTTP_OK, actual: $response->getStatusCode());
            $this->assertEquals(
                expected: 'application/vnd.api+json',
                actual: $response->headers->get('Content-Type'),
            );
            $this->assertArrayHasKey(key: 'data', array: $responseData);
            $this->assertEquals(expected: $bookResource->toArray($request), actual: $responseData['data']);
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        } catch (Throwable $e) {
            $this->fail(message: "An unexpected error occurred: {$e->getMessage()}");
        }
    }

    public function test_it_cant_create_a_model_response_with_invalid_resource(): void
    {
        // Given
        $this->expectException(TypeError::class);

        // When
        /** @phpstan-ignore-next-line */
        ModelResponse::success(data: 'invalid-resource');
    }

    public function test_it_cant_create_a_model_response_with_invalid_meta(): void
    {
        // Given
        $this->expectException(TypeError::class);

        // When
        $bookResource = new BookResource(resource: []);
        /** @phpstan-ignore-next-line */
        ModelResponse::success(data: $bookResource, meta: 'invalid-status');
    }
}
