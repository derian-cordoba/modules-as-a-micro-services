<?php

namespace Modules\History\Tests\Feature\Http\Controllers\V1;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\History\Models\History;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class DeleteControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private string $route = 'api.v1.histories.delete';

    public function test_it_can_delete_single_history_via_api(): void
    {
        // Given
        $history = History::factory()->create([
            'name' => 'Test History',
            'type' => 'test',
            'user_id' => 1,
        ]);

        $payload = [
            'slugs' => [$history->slug],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('histories', [
            'slug' => $history->slug,
            'user_id' => 1,
        ]);
    }

    public function test_it_can_delete_multiple_histories_via_api(): void
    {
        // Given
        /** @var History $history1 */
        $history1 = History::factory()->create([
            'name' => 'History 1',
            'type' => 'test',
            'user_id' => 1,
        ]);

        /** @var History $history2 */
        $history2 = History::factory()->create([
            'name' => 'History 2',
            'type' => 'test',
            'user_id' => 1,
        ]);

        /** @var History $history3 */
        $history3 = History::factory()->create([
            'name' => 'History 3',
            'type' => 'test',
            'user_id' => 1,
        ]);

        $payload = [
            'slugs' => [$history1->slug, $history2->slug, $history3->slug],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('histories', ['slug' => $history1->slug]);
        $this->assertSoftDeleted('histories', ['slug' => $history2->slug]);
        $this->assertSoftDeleted('histories', ['slug' => $history3->slug]);
    }

    public function test_it_only_deletes_histories_belonging_to_user(): void
    {
        // Given
        /** @var History $userHistory */
        $userHistory = History::factory()->create([
            'name' => 'User 1 History',
            'type' => 'test',
            'user_id' => 1,
        ]);

        /** @var History $otherUserHistory */
        $otherUserHistory = History::factory()->create([
            'name' => 'User 2 History',
            'type' => 'test',
            'user_id' => 2,
        ]);

        $payload = [
            'slugs' => [$userHistory->slug, $otherUserHistory->slug],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('histories', ['slug' => $userHistory->slug]);
        $this->assertDatabaseHas('histories', [
            'slug' => $otherUserHistory->slug,
            'user_id' => 2,
            'deleted_at' => null,
        ]);
    }

    public function test_it_handles_non_existent_slugs_gracefully(): void
    {
        // Given
        $payload = [
            'slugs' => ['non-existent-slug-1', 'non-existent-slug-2'],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_it_handles_mix_of_existing_and_non_existing_slugs(): void
    {
        // Given
        /** @var History $existingHistory */
        $existingHistory = History::factory()->create([
            'name' => 'Existing History',
            'type' => 'test',
            'user_id' => 1,
        ]);

        $payload = [
            'slugs' => [$existingHistory->slug, 'non-existent-slug'],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('histories', ['slug' => $existingHistory->slug]);
    }

    #[DataProvider(methodName: 'validationErrorProvider')]
    public function test_it_validates_required_fields(
        array $payload,
        string $expectedErrorField
    ): void {
        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor($expectedErrorField);
    }

    public function test_it_validates_slugs_is_array(): void
    {
        // Given
        $payload = [
            'slugs' => 'not-an-array',
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('slugs');
    }

    public function test_it_validates_slugs_array_contains_strings(): void
    {
        // Given
        $payload = [
            'slugs' => [123, 456], // integers instead of strings
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('slugs.0');
    }

    public function test_it_validates_user_id_is_integer(): void
    {
        // Given
        $payload = [
            'slugs' => ['test-slug'],
            'user_id' => 'not-an-integer',
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('user_id');
    }

    public function test_it_validates_empty_slugs_array(): void
    {
        // Given
        $payload = [
            'slugs' => [],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrorFor('slugs');
    }

    public function test_it_uses_soft_delete(): void
    {
        // Given
        /** @var History $history */
        $history = History::factory()->create([
            'name' => 'Soft Delete Test',
            'type' => 'test',
            'user_id' => 1,
        ]);

        $payload = [
            'slugs' => [$history->slug],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        // Verify it's soft deleted
        $this->assertDatabaseHas('histories', [
            'slug' => $history->slug,
        ]);

        $this->assertSoftDeleted('histories', [
            'slug' => $history->slug,
        ]);

        // Verify deleted_at is not null
        $deletedHistory = History::withTrashed()->where('slug', $history->slug)->first();
        $this->assertNotNull($deletedHistory->deleted_at);
    }

    public function test_it_deletes_histories_with_metadata(): void
    {
        // Given
        /** @var History $history */
        $history = History::factory()->create([
            'name' => 'History with Metadata',
            'type' => 'test',
            'user_id' => 1,
            'metadata' => [
                'key1' => 'value1',
                'key2' => 'value2',
            ],
        ]);

        $payload = [
            'slugs' => [$history->slug],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('histories', ['slug' => $history->slug]);
    }

    public function test_it_handles_special_characters_in_slugs(): void
    {
        // Given
        /** @var History $history */
        $history = History::factory()->create([
            'name' => 'Test "Special" & <Characters>',
            'type' => 'test',
            'user_id' => 1,
        ]);

        $payload = [
            'slugs' => [$history->slug],
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted('histories', ['slug' => $history->slug]);
    }

    public function test_it_handles_large_number_of_slugs(): void
    {
        // Given
        $histories = History::factory()->count(50)->create([
            'user_id' => 1,
        ]);

        $slugs = $histories->pluck('slug')->toArray();

        $payload = [
            'slugs' => $slugs,
            'user_id' => 1,
        ];

        // When
        $response = $this->deleteJson(route($this->route), $payload);

        // Then
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        foreach ($slugs as $slug) {
            $this->assertSoftDeleted('histories', ['slug' => $slug]);
        }
    }

    public static function validationErrorProvider(): array
    {
        return [
            'missing slugs' => [
                'payload' => [
                    'user_id' => 1,
                ],
                'expectedErrorField' => 'slugs',
            ],
            'missing user_id' => [
                'payload' => [
                    'slugs' => ['test-slug'],
                ],
                'expectedErrorField' => 'user_id',
            ],
        ];
    }
}
