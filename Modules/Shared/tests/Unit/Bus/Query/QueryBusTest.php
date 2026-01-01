<?php

namespace Query;

use Modules\Shared\Bus\Query\QueryBus;
use Modules\Shared\Contracts\Query\QueryBusInterface;
use Modules\Shared\Contracts\Query\QueryInterface;
use Modules\Shared\Exceptions\UnregisteredQueryException;
use Modules\Shared\Tests\Unit\Bus\Query\Fixtures\GetBookHandler;
use Modules\Shared\Tests\Unit\Bus\Query\Fixtures\GetBookQuery;
use Modules\Shared\Tests\Unit\Bus\Query\Fixtures\GetBooksHandler;
use Modules\Shared\Tests\Unit\Bus\Query\Fixtures\GetBooksQuery;
use Modules\Shared\Tests\Unit\Bus\Query\Fixtures\GetBookStatisticsHandler;
use Modules\Shared\Tests\Unit\Bus\Query\Fixtures\GetBookStatisticsQuery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use TypeError;

final class QueryBusTest extends TestCase
{
    private QueryBusInterface $queryBus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queryBus = new QueryBus();
    }

    public function test_it_can_register_a_query_handler(): void
    {
        // When
        $this->queryBus->register(
            queryClass: GetBookQuery::class,
            handlerClass: GetBookHandler::class
        );

        // Then
        $this->assertTrue(condition: true); // If no exception, registration succeeded
    }

    public function test_it_can_ask_a_registered_query(): void
    {
        // Given
        $this->queryBus->register(
            queryClass: GetBookQuery::class,
            handlerClass: GetBookHandler::class
        );

        $query = new GetBookQuery(bookId: '123');

        // When
        try {
            $result = $this->queryBus->ask($query);
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
        $this->assertArrayHasKey(key: 'id', array: $result);
        $this->assertEquals(expected: '123', actual: $result['id']);
    }

    #[DataProvider(methodName: 'multipleQueriesProvider')]
    public function test_it_can_handle_multiple_registered_queries(
        string $queryClass,
        string $handlerClass,
        QueryInterface $query,
        array $expectedKeys
    ): void {
        // Given
        $this->queryBus->register(
            queryClass: $queryClass,
            handlerClass: $handlerClass
        );

        // When
        try {
            $result = $this->queryBus->ask($query);
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey(key: $key, array: $result);
        }
    }

    public function test_it_throws_exception_when_asking_unregistered_query(): void
    {
        // Given
        $query = new GetBookQuery(bookId: '123');
        $exception = new UnregisteredQueryException(queryClass: GetBookQuery::class);

        // Then
        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());

        // When
        $this->queryBus->ask($query);
    }

    public function test_it_resolves_handler_from_container(): void
    {
        // Given
        $this->queryBus->register(
            queryClass: GetBooksQuery::class,
            handlerClass: GetBooksHandler::class
        );

        $query = new GetBooksQuery(
            page: 1,
            perPage: 10
        );

        // When
        try {
            $result = $this->queryBus->ask($query);
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
        $this->assertArrayHasKey(key: 'data', array: $result);
        $this->assertArrayHasKey(key: 'pagination', array: $result);
    }

    public function test_it_can_override_registered_handler(): void
    {
        // Given
        $this->queryBus->register(
            queryClass: GetBookQuery::class,
            handlerClass: GetBookHandler::class
        );

        // When - Re-register with same query
        $this->queryBus->register(
            queryClass: GetBookQuery::class,
            handlerClass: GetBookHandler::class
        );

        $query = new GetBookQuery(bookId: '456');

        try {
            $result = $this->queryBus->ask($query);
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
    }

    public function test_it_handles_query_with_optional_parameters(): void
    {
        // Given
        $this->queryBus->register(
            queryClass: GetBooksQuery::class,
            handlerClass: GetBooksHandler::class
        );

        $query = new GetBooksQuery(
            page: 1,
            perPage: 10,
            search: 'clean',
            sortBy: 'title'
        );

        // When
        try {
            $result = $this->queryBus->ask($query);
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
        $this->assertNotEmpty(actual: $result['data']);
    }

    public function test_it_handles_query_that_returns_null(): void
    {
        // Given
        $this->queryBus->register(
            queryClass: GetBookQuery::class,
            handlerClass: GetBookHandler::class
        );

        $query = new GetBookQuery(bookId: 'non-existent');

        // When
        try {
            $result = $this->queryBus->ask($query);
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertNull(actual: $result);
    }

    public function test_it_handles_query_that_returns_empty_collection(): void
    {
        // Given
        $this->queryBus->register(
            queryClass: GetBooksQuery::class,
            handlerClass: GetBooksHandler::class
        );

        $query = new GetBooksQuery(
            page: 999,
            perPage: 10
        );

        // When
        try {
            $result = $this->queryBus->ask($query);
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
        $this->assertEmpty(actual: $result['data']);
    }

    public function test_it_cant_ask_invalid_query(): void
    {
        // Then
        $this->expectException(TypeError::class);

        // When
        /** @phpstan-ignore-next-line */
        $this->queryBus->ask('not-a-query');
    }

    #[DataProvider(methodName: 'queryChainProvider')]
    public function test_it_can_ask_multiple_queries_in_sequence(array $queries): void
    {
        // Given
        $this->queryBus->register(
            queryClass: GetBookQuery::class,
            handlerClass: GetBookHandler::class
        );
        $this->queryBus->register(
            queryClass: GetBooksQuery::class,
            handlerClass: GetBooksHandler::class
        );

        $results = [];

        // When
        try {
            foreach ($queries as $query) {
                $results[] = $this->queryBus->ask($query);
            }
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertCount(expectedCount: count($queries), haystack: $results);

        foreach ($results as $result) {
            $this->assertIsArray(actual: $result);
        }
    }

    public function test_it_handles_complex_query_results(): void
    {
        // Given
        $this->queryBus->register(
            queryClass: GetBookStatisticsQuery::class,
            handlerClass: GetBookStatisticsHandler::class
        );

        $query = new GetBookStatisticsQuery(
            fromDate: '2024-01-01',
            toDate: '2024-12-31'
        );

        // When
        try {
            $result = $this->queryBus->ask($query);
        } catch (UnregisteredQueryException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
        $this->assertArrayHasKey(key: 'total_books', array: $result);
        $this->assertArrayHasKey(key: 'total_authors', array: $result);
        $this->assertArrayHasKey(key: 'books_by_category', array: $result);
    }

    public static function multipleQueriesProvider(): array
    {
        return [
            'get single book' => [
                'queryClass' => GetBookQuery::class,
                'handlerClass' => GetBookHandler::class,
                'query' => new GetBookQuery(bookId: '123'),
                'expectedKeys' => ['id', 'title', 'author', 'isbn'],
            ],
            'get books collection' => [
                'queryClass' => GetBooksQuery::class,
                'handlerClass' => GetBooksHandler::class,
                'query' => new GetBooksQuery(page: 1, perPage: 10),
                'expectedKeys' => ['data', 'pagination'],
            ],
            'get statistics' => [
                'queryClass' => GetBookStatisticsQuery::class,
                'handlerClass' => GetBookStatisticsHandler::class,
                'query' => new GetBookStatisticsQuery(
                    fromDate: '2024-01-01',
                    toDate: '2024-12-31'
                ),
                'expectedKeys' => ['total_books', 'total_authors', 'books_by_category'],
            ],
        ];
    }

    public static function queryChainProvider(): array
    {
        return [
            'get book and list books' => [
                'queries' => [
                    new GetBookQuery(bookId: '123'),
                    new GetBooksQuery(page: 1, perPage: 10),
                ],
            ],
        ];
    }
}
