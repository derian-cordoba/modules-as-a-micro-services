<?php

namespace Modules\Shared\Tests\Unit\Http\Response;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use JsonException;
use Modules\Shared\Http\Response\CollectionResponse;
use Modules\Shared\Tests\Unit\Http\Response\Concerns\CollectionResponseProviderTrait;
use Modules\Shared\Tests\Unit\Http\Response\Fixtures\Resources\BookCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Throwable;
use TypeError;

final class CollectionResponseTest extends TestCase
{
    use CollectionResponseProviderTrait;

    #[DataProvider(methodName: 'bookCollectionProvider')]
    public function test_it_can_create_a_collection_response(?array $collection): void
    {
        // Given
        $bookCollection = new BookCollection(resource: $collection);

        // When
        $response = CollectionResponse::success(data: $bookCollection);

        // Then
        $this->assertEquals(expected: Response::HTTP_OK, actual: $response->status);
        $this->assertEmpty(actual: $response->meta);
        $this->assertSame(expected: $bookCollection, actual: $response->data);
    }

    #[DataProvider(methodName: 'bookCollectionWithMetaProvider')]
    public function test_it_can_create_a_collection_response_with_meta(?array $collection, array $meta): void
    {
        // Given
        $bookCollection = new BookCollection(resource: $collection);

        // When
        $response = CollectionResponse::success(data: $bookCollection, meta: $meta);

        // Then
        $this->assertEquals(expected: Response::HTTP_OK, actual: $response->status);
        $this->assertNotEmpty(actual: $response->meta);
        $this->assertArrayHasKey(key: 'total', array: $response->meta);
        $this->assertEquals(expected: $meta['total'], actual: $response->meta['total']);
        $this->assertSame(expected: $bookCollection, actual: $response->data);
    }

    #[DataProvider(methodName: 'bookCollectionWithHeadersProvider')]
    public function test_it_can_create_a_collection_response_with_custom_headers(
        ?array $collection,
        array $headers
    ): void {
        // Given
        $bookCollection = new BookCollection(resource: $collection);

        // When
        $response = new CollectionResponse(
            data: $bookCollection,
            headers: $headers
        );

        // Then
        $this->assertEquals(expected: Response::HTTP_OK, actual: $response->status);
        $this->assertEquals(expected: $headers, actual: $response->headers);
    }

    #[DataProvider(methodName: 'toResponseProvider')]
    public function test_it_can_create_a_collection_response_using_to_response_method(
        ?array $collection,
        ?Request $request,
    ): void {
        // Given
        $bookCollection = new BookCollection(resource: $collection);
        $collectionResponse = CollectionResponse::success(data: $bookCollection);

        try {
            // When
            $response = $collectionResponse->toResponse($request);
            $responseData = json_decode(
                json: $response->getContent(),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            // Then
            $this->assertEquals(expected: Response::HTTP_OK, actual: $response->getStatusCode());
            $this->assertEquals(
                expected: 'application/vnd.api+json',
                actual: $response->headers->get('Content-Type'),
            );
            $this->assertArrayHasKey(key: 'data', array: $responseData);
            $this->assertIsArray(actual: $responseData['data']);
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        } catch (Throwable $e) {
            $this->fail(message: "An unexpected error occurred: {$e->getMessage()}");
        }
    }

    #[DataProvider(methodName: 'paginatedCollectionProvider')]
    public function test_it_can_create_a_collection_response_with_pagination(
        array|LengthAwarePaginator|null $collection,
        ?Request $request
    ): void {
        // Given
        $bookCollection = new BookCollection(resource: $collection);
        $collectionResponse = CollectionResponse::success(data: $bookCollection);

        try {
            // When
            $response = $collectionResponse->toResponse($request);
            $responseData = json_decode(
                json: $response->getContent(),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            // Then
            $this->assertArrayHasKey(key: 'meta', array: $responseData);
            $this->assertArrayHasKey(key: 'links', array: $responseData);

            if (isset($responseData['meta']['pagination'])) {
                $this->assertArrayHasKey(key: 'total', array: $responseData['meta']['pagination']);
                $this->assertArrayHasKey(key: 'current_page', array: $responseData['meta']['pagination']);
                $this->assertArrayHasKey(key: 'per_page', array: $responseData['meta']['pagination']);
            }
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        } catch (Throwable $e) {
            $this->fail(message: "An unexpected error occurred: {$e->getMessage()}");
        }
    }

    #[DataProvider(methodName: 'emptyCollectionProvider')]
    public function test_it_can_create_a_collection_response_with_empty_collection(
        array|Collection $collection,
        ?Request $request
    ): void {
        // Given
        $bookCollection = new BookCollection(resource: $collection);
        $collectionResponse = CollectionResponse::success(data: $bookCollection);

        try {
            // When
            $response = $collectionResponse->toResponse($request);
            $responseData = json_decode(
                json: $response->getContent(),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            // Then
            $this->assertEquals(expected: Response::HTTP_OK, actual: $response->getStatusCode());
            $this->assertArrayHasKey(key: 'data', array: $responseData);
            $this->assertIsArray(actual: $responseData['data']);
            $this->assertEmpty(actual: $responseData['data']);
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        } catch (Throwable $e) {
            $this->fail(message: "An unexpected error occurred: {$e->getMessage()}");
        }
    }

    #[DataProvider(methodName: 'collectionWithMetaMergeProvider')]
    public function test_it_merges_meta_information_correctly(
        ?array $collection,
        array $initialMeta,
        ?Request $request
    ): void {
        // Given
        $bookCollection = new BookCollection(resource: $collection);
        $collectionResponse = new CollectionResponse(
            data: $bookCollection,
            meta: $initialMeta
        );

        try {
            // When
            $response = $collectionResponse->toResponse($request);
            $responseData = json_decode(
                json: $response->getContent(),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            // Then
            $this->assertArrayHasKey(key: 'meta', array: $responseData);

            foreach ($initialMeta as $key => $value) {
                $this->assertArrayHasKey(key: $key, array: $responseData['meta']);
                $this->assertEquals(expected: $value, actual: $responseData['meta'][$key]);
            }
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        } catch (Throwable $e) {
            $this->fail(message: "An unexpected error occurred: {$e->getMessage()}");
        }
    }

    public function test_it_cant_create_a_collection_response_with_invalid_resource(): void
    {
        // Given
        $this->expectException(TypeError::class);

        // When
        /** @phpstan-ignore-next-line */
        CollectionResponse::success(data: 'invalid-resource');
    }

    public function test_it_cant_create_a_collection_response_with_invalid_meta(): void
    {
        // Given
        $this->expectException(TypeError::class);

        // When
        $bookCollection = new BookCollection(resource: []);
        /** @phpstan-ignore-next-line */
        CollectionResponse::success(data: $bookCollection, meta: 'invalid-meta');
    }

    public function test_it_cant_create_a_collection_response_with_invalid_headers(): void
    {
        // Given
        $this->expectException(TypeError::class);

        // When
        $bookCollection = new BookCollection(resource: []);
        /** @phpstan-ignore-next-line */
        new CollectionResponse(data: $bookCollection, headers: 'invalid-headers');
    }

    public function test_it_cant_create_a_collection_response_with_invalid_status(): void
    {
        // Given
        $this->expectException(TypeError::class);

        // When
        $bookCollection = new BookCollection(resource: []);
        /** @phpstan-ignore-next-line */
        new CollectionResponse(data: $bookCollection, status: 'invalid-status');
    }

    #[DataProvider(methodName: 'customHeadersProvider')]
    public function test_it_applies_custom_headers_correctly(
        ?array $collection,
        array $headers,
        ?Request $request
    ): void {
        // Given
        $bookCollection = new BookCollection(resource: $collection);
        $collectionResponse = new CollectionResponse(
            data: $bookCollection,
            headers: $headers
        );

        // When
        try {
            $response = $collectionResponse->toResponse($request);
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        }

        // Then
        foreach ($headers as $key => $value) {
            $this->assertEquals(expected: $value, actual: $response->headers->get($key));
        }
    }

    #[DataProvider(methodName: 'statusCodeProvider')]
    public function test_it_applies_custom_status_code_correctly(
        ?array $collection,
        int $statusCode,
        ?Request $request
    ): void {
        // Given
        $bookCollection = new BookCollection(resource: $collection);
        $collectionResponse = new CollectionResponse(
            data: $bookCollection,
            status: $statusCode
        );

        // When
        try {
            $jsonResponse = $collectionResponse->toResponse(new Request());
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        }

        // Then
        $this->assertEquals(expected: $statusCode, actual: $jsonResponse->getStatusCode());
    }

    public function test_it_handles_large_collection(): void
    {
        // Given
        $largeCollection = Collection::times(
            number: 1000,
            callback: static fn ($number) => [
                'id' => (string) $number,
                'title' => "Book {$number}",
            ],
        );

        $bookCollection = new BookCollection(resource: $largeCollection);

        // When
        $response = CollectionResponse::success(data: $bookCollection);

        try {
            $jsonResponse = $response->toResponse(new Request());
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        }

        // Then
        $this->assertEquals(expected: Response::HTTP_OK, actual: $jsonResponse->getStatusCode());
    }

    public function test_it_handles_nested_collections(): void
    {
        // Given
        $nestedCollection = [
            [
                'id' => '1',
                'title' => 'Book 1',
                'chapters' => [
                    ['number' => 1, 'title' => 'Chapter 1'],
                    ['number' => 2, 'title' => 'Chapter 2'],
                ],
            ],
        ];

        $bookCollection = new BookCollection(resource: $nestedCollection);

        // When
        $response = CollectionResponse::success(data: $bookCollection);

        try {
            $jsonResponse = $response->toResponse(new Request());
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        }

        // Then
        $this->assertEquals(expected: Response::HTTP_OK, actual: $jsonResponse->getStatusCode());
    }

    public function test_it_preserves_collection_order(): void
    {
        // Given
        $orderedCollection = [
            ['id' => '3', 'title' => 'Book C'],
            ['id' => '1', 'title' => 'Book A'],
            ['id' => '2', 'title' => 'Book B'],
        ];

        $bookCollection = new BookCollection(resource: $orderedCollection);
        $response = CollectionResponse::success(data: $bookCollection);

        // When
        try {
            $jsonResponse = $response->toResponse(new Request());
            $content = json_decode(json: $jsonResponse->getContent(), associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail(message: "JSON decoding failed: {$e->getMessage()}");
        }

        // Then
        $this->assertEquals(expected: '3', actual: $content['data'][0]['id']);
        $this->assertEquals(expected: '1', actual: $content['data'][1]['id']);
        $this->assertEquals(expected: '2', actual: $content['data'][2]['id']);
    }
}
