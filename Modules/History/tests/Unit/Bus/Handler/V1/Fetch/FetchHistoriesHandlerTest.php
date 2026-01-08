<?php

namespace Modules\History\Tests\Unit\Bus\Handler\V1\Fetch;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\History\Bus\Handler\V1\Fetch\FetchHistoriesHandler;
use Modules\History\Bus\Query\V1\Fetch\FetchHistoriesQuery;
use Modules\History\Models\History;
use Tests\TestCase;

final class FetchHistoriesHandlerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private FetchHistoriesHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new FetchHistoriesHandler();
    }

    public function test_it_can_handle_fetch_histories_query(): void
    {
        // Given
        History::factory()->count(5)->create();
        $query = new FetchHistoriesQuery();

        // When
        $result = $this->handler->handle($query);

        // Then
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }

    public function test_it_returns_empty_paginator_when_no_histories(): void
    {
        // Given
        $query = new FetchHistoriesQuery();

        // When
        $result = $this->handler->handle($query);

        // Then
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(0, $result->total());
        $this->assertEmpty($result->items());
    }

    public function test_it_paginates_results(): void
    {
        // Given
        History::factory()->count(30)->create();
        $query = new FetchHistoriesQuery();

        // When
        $result = $this->handler->handle($query);

        // Then
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(30, $result->total());
        $this->assertEquals(15, $result->perPage()); // Default Laravel pagination
    }

    public function test_it_returns_histories_with_all_attributes(): void
    {
        // Given
        History::factory()->create([
            'name' => 'Test History',
            'type' => 'test',
            'user_id' => 1,
            'is_scanned' => true,
            'metadata' => ['key' => 'value'],
        ]);
        $query = new FetchHistoriesQuery();

        // When
        $result = $this->handler->handle($query);

        // Then
        $history = $result->items()[0];
        $this->assertEquals('Test History', $history->name);
        $this->assertEquals('test', $history->type);
        $this->assertEquals(1, $history->user_id);
        $this->assertTrue($history->is_scanned);
        $this->assertEquals(['key' => 'value'], $history->metadata);
    }

    public function test_it_does_not_return_soft_deleted_histories(): void
    {
        // Given
        $activeHistory = History::factory()->create(['name' => 'Active']);
        $deletedHistory = History::factory()->create(['name' => 'Deleted']);
        $deletedHistory->delete();

        $query = new FetchHistoriesQuery();

        // When
        $result = $this->handler->handle($query);

        // Then
        $this->assertEquals(1, $result->total());
        $this->assertEquals($activeHistory->id, $result->items()[0]->id);
    }

    public function test_it_returns_histories_from_all_users(): void
    {
        // Given
        History::factory()->create(['user_id' => 1]);
        History::factory()->create(['user_id' => 2]);
        History::factory()->create(['user_id' => 3]);
        $query = new FetchHistoriesQuery();

        // When
        $result = $this->handler->handle($query);

        // Then
        $this->assertEquals(3, $result->total());
    }

    public function test_it_returns_paginator_with_correct_structure(): void
    {
        // Given
        History::factory()->count(25)->create();
        $query = new FetchHistoriesQuery();

        // When
        $result = $this->handler->handle($query);

        // Then
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertIsInt($result->total());
        $this->assertIsInt($result->perPage());
        $this->assertIsInt($result->currentPage());
        $this->assertIsInt($result->lastPage());
        $this->assertIsArray($result->items());
    }
}
