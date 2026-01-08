<?php

namespace Modules\History\Tests\Feature\Http\Controllers\V1;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\History\Models\History;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class FetchControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $route = 'api.v1.histories.fetch';

    public function test_it_can_fetch_histories_via_api(): void
    {
        // Given
        History::factory()->count(3)->create([
            'user_id' => 1,
        ]);

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                            'type',
                            'user_id',
                            'is_scanned',
                            'metadata',
                            'created_at',
                        ],
                    ],
                ],
                'meta' => [
                    'total',
                    'per_page',
                    'current_page',
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_it_returns_empty_array_when_no_histories_exist(): void
    {
        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }

    public function test_it_paginates_histories(): void
    {
        // Given
        History::factory()->count(20)->create();

        // When - First page
        $response = $this->getJson(route($this->route) . '?page=1&per_page=10');

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 20);
    }

    public function test_it_returns_second_page_of_paginated_results(): void
    {
        // Given
        History::factory()->count(25)->create();

        // When - Second page
        $response = $this->getJson(route($this->route) . '?page=2&per_page=10');

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25);
    }

    public function test_it_returns_last_page_with_remaining_items(): void
    {
        // Given
        History::factory()->count(25)->create();

        // When - Third page (last page with 5 items)
        $response = $this->getJson(route($this->route) . '?page=3&per_page=10');

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 3)
            ->assertJsonPath('meta.total', 25);
    }

    public function test_it_includes_pagination_links(): void
    {
        // Given
        History::factory()->count(30)->create();

        // When - Second page
        $response = $this->getJson(route($this->route) . '?page=2&per_page=10');

        // Then
        $response->assertStatus(Response::HTTP_OK);

        $links = $response->json('links');
        $this->assertNotNull($links['first']);
        $this->assertNotNull($links['last']);
        $this->assertNotNull($links['prev']);
        $this->assertNotNull($links['next']);
    }

    public function test_it_returns_null_prev_link_on_first_page(): void
    {
        // Given
        History::factory()->count(20)->create();

        // When - First page
        $response = $this->getJson(route($this->route) . '?page=1&per_page=10');

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('links.prev', null);
    }

    public function test_it_returns_null_next_link_on_last_page(): void
    {
        // Given
        History::factory()->count(15)->create();

        // When - Last page
        $response = $this->getJson(route($this->route) . '?page=2&per_page=10');

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('links.next', null);
    }

    public function test_it_returns_histories_with_metadata(): void
    {
        // Given
        $metadata = [
            'action' => 'test',
            'details' => ['key' => 'value'],
        ];

        History::factory()->create([
            'name' => 'History with Metadata',
            'type' => 'test',
            'user_id' => 1,
            'metadata' => $metadata,
        ]);

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.0.attributes.metadata', $metadata);
    }

    public function test_it_returns_histories_without_metadata(): void
    {
        // Given
        History::factory()->create([
            'name' => 'History without Metadata',
            'type' => 'test',
            'user_id' => 1,
            'metadata' => null,
        ]);

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.0.attributes.metadata', null);
    }

    public function test_it_returns_histories_from_different_users(): void
    {
        // Given
        History::factory()->create(['user_id' => 1, 'name' => 'User 1 History']);
        History::factory()->create(['user_id' => 2, 'name' => 'User 2 History']);
        History::factory()->create(['user_id' => 3, 'name' => 'User 3 History']);

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data');
    }

    public function test_it_returns_histories_of_different_types(): void
    {
        // Given
        History::factory()->create(['type' => 'authentication']);
        History::factory()->create(['type' => 'order']);
        History::factory()->create(['type' => 'payment']);

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(3, 'data');

        $types = collect($response->json('data'))->pluck('attributes.type')->toArray();
        $this->assertContains('authentication', $types);
        $this->assertContains('order', $types);
        $this->assertContains('payment', $types);
    }

    public function test_it_does_not_return_soft_deleted_histories(): void
    {
        // Given
        $activeHistory = History::factory()->create([
            'name' => 'Active History',
        ]);

        $deletedHistory = History::factory()->create([
            'name' => 'Deleted History',
        ]);
        $deletedHistory->delete();

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activeHistory->slug);
    }

    public function test_it_returns_json_api_content_type(): void
    {
        // Given
        History::factory()->create();

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertHeader('Content-Type', 'application/vnd.api+json');
    }

    public function test_it_handles_large_dataset(): void
    {
        // Given
        History::factory()->count(100)->create();

        // When
        $response = $this->getJson(route($this->route) . '?page=1&per_page=15');

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('meta.total', 100)
            ->assertJsonCount(15, 'data');
    }

    public function test_it_returns_correct_resource_structure(): void
    {
        // Given
        History::factory()->create([
            'name' => 'Test History',
            'type' => 'test',
            'user_id' => 1,
            'is_scanned' => true,
            'metadata' => ['test' => 'data'],
        ]);

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'type',
                        'id',
                        'attributes' => [
                            'name',
                            'type',
                            'user_id',
                            'is_scanned',
                            'metadata',
                            'created_at',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('data.0.type', 'histories')
            ->assertJsonPath('data.0.attributes.name', 'Test History')
            ->assertJsonPath('data.0.attributes.type', 'test')
            ->assertJsonPath('data.0.attributes.user_id', 1)
            ->assertJsonPath('data.0.attributes.is_scanned', true);
    }

    public function test_it_returns_histories_with_special_characters(): void
    {
        // Given
        History::factory()->create([
            'name' => 'Test "Special" & <Characters>',
            'type' => 'test',
        ]);

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.0.attributes.name', 'Test "Special" & <Characters>');
    }

    public function test_it_returns_histories_with_unicode_characters(): void
    {
        // Given
        History::factory()->create([
            'name' => 'Usuario Accedió 用户登录 🎉',
            'type' => 'authentication',
        ]);

        // When
        $response = $this->getJson(route($this->route));

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.0.attributes.name', 'Usuario Accedió 用户登录 🎉');
    }

    public function test_pagination_meta_includes_from_and_to(): void
    {
        // Given
        History::factory()->count(25)->create();

        // When
        $response = $this->getJson(route($this->route) . '?page=2&per_page=10');

        // Then
        $response->assertStatus(Response::HTTP_OK);

        $pagination = $response->json('meta');
        $this->assertEquals(11, $pagination['from']);
        $this->assertEquals(20, $pagination['to']);
    }

    public function test_it_handles_invalid_page_number_gracefully(): void
    {
        // Given
        History::factory()->count(10)->create();

        // When - Request page beyond available pages
        $response = $this->getJson(route($this->route) . '?page=999');

        // Then
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(0, 'data');
    }
}
