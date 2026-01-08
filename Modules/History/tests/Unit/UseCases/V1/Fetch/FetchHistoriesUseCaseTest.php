<?php

namespace Modules\History\Tests\Unit\UseCases\V1\Fetch;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Mockery;
use Modules\History\Bus\Query\V1\Fetch\FetchHistoriesQuery;
use Modules\History\Models\History;
use Modules\History\UseCases\V1\Fetch\FetchHistoriesUseCase;
use Modules\History\UseCases\V1\Fetch\Output\FetchHistoriesOutput;
use Modules\Shared\Contracts\Query\QueryBusInterface;
use Tests\TestCase;
use TypeError;

final class FetchHistoriesUseCaseTest extends TestCase
{
    private QueryBusInterface $queryBus;
    private FetchHistoriesUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->queryBus = Mockery::mock(QueryBusInterface::class);
        $this->useCase = new FetchHistoriesUseCase($this->queryBus);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_can_execute_fetch_histories_use_case(): void
    {
        // Given
        $query = new FetchHistoriesQuery();

        $histories = new LengthAwarePaginator(
            items: new Collection([
                new History(['name' => 'History 1']),
                new History(['name' => 'History 2']),
            ]),
            total: 2,
            perPage: 15,
            currentPage: 1
        );

        $this->queryBus
            ->shouldReceive('ask')
            ->once()
            ->with(Mockery::type(FetchHistoriesQuery::class))
            ->andReturn($histories);

        // When
        $output = $this->useCase->execute($query);

        // Then
        $this->assertInstanceOf(FetchHistoriesOutput::class, $output);
        $this->assertInstanceOf(LengthAwarePaginator::class, $output->histories);
        $this->assertEquals(2, $output->histories->total());
    }

    public function test_it_returns_empty_paginator_when_no_histories(): void
    {
        // Given
        $query = new FetchHistoriesQuery();

        $emptyPaginator = new LengthAwarePaginator(
            items: new Collection([]),
            total: 0,
            perPage: 15,
            currentPage: 1
        );

        $this->queryBus
            ->shouldReceive('ask')
            ->once()
            ->andReturn($emptyPaginator);

        // When
        $output = $this->useCase->execute($query);

        // Then
        $this->assertInstanceOf(FetchHistoriesOutput::class, $output);
        $this->assertEquals(0, $output->histories->total());
        $this->assertEmpty($output->histories->items());
    }

    public function test_it_handles_paginated_results(): void
    {
        // Given
        $query = new FetchHistoriesQuery();

        $histories = new LengthAwarePaginator(
            items: new Collection(array_map(
                static fn ($i) => new History(['name' => "History {$i}"]),
                range(1, 15)
            )),
            total: 50,
            perPage: 15,
            currentPage: 1
        );

        $this->queryBus
            ->shouldReceive('ask')
            ->once()
            ->andReturn($histories);

        // When
        $output = $this->useCase->execute($query);

        // Then
        $this->assertEquals(50, $output->histories->total());
        $this->assertEquals(15, $output->histories->perPage());
        $this->assertEquals(1, $output->histories->currentPage());
        $this->assertCount(15, $output->histories->items());
    }

    public function test_it_dispatches_query_through_bus(): void
    {
        // Given
        $query = new FetchHistoriesQuery();

        $paginator = new LengthAwarePaginator(
            items: new Collection([]),
            total: 0,
            perPage: 15,
            currentPage: 1
        );

        $this->queryBus
            ->shouldReceive('ask')
            ->once()
            ->with($query)
            ->andReturn($paginator);

        // When
        $output = $this->useCase->execute($query);

        // Then
        $this->assertInstanceOf(FetchHistoriesOutput::class, $output);
    }

    public function test_it_cant_execute_with_invalid_input(): void
    {
        // Then
        $this->expectException(TypeError::class);

        // When
        /** @phpstan-ignore-next-line */
        $this->useCase->execute('invalid-input');
    }

    public function test_it_returns_output_with_correct_structure(): void
    {
        // Given
        $query = new FetchHistoriesQuery();

        $histories = new LengthAwarePaginator(
            items: new Collection([
                new History(['name' => 'Test']),
            ]),
            total: 1,
            perPage: 15,
            currentPage: 1
        );

        $this->queryBus
            ->shouldReceive('ask')
            ->once()
            ->andReturn($histories);

        // When
        $output = $this->useCase->execute($query);

        // Then
        $this->assertInstanceOf(FetchHistoriesOutput::class, $output);
        $this->assertObjectHasProperty('histories', $output);
        $this->assertInstanceOf(LengthAwarePaginator::class, $output->histories);
    }
}
