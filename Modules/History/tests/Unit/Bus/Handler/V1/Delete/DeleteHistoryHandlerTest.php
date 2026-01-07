<?php

namespace Modules\History\Tests\Unit\Bus\Handler\V1\Delete;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\History\Bus\Command\V1\Delete\DeleteHistoryCommand;
use Modules\History\Bus\Handler\V1\Delete\DeleteHistoryHandler;
use Modules\History\Models\History;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class DeleteHistoryHandlerTest extends TestCase
{
    use LazilyRefreshDatabase;

    private DeleteHistoryHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new DeleteHistoryHandler();
    }

    #[DataProvider(methodName: 'deleteHistoryCommandProvider')]
    public function test_it_can_handle_delete_history_command(
        DeleteHistoryCommand $command,
        int $userId
    ): void {
        // Given
        History::factory()->count(3)->create(['user_id' => $userId]);

        // When
        $result = $this->handler->handle($command);

        // Then
        $this->assertTrue($result);
    }

    public function test_it_deletes_single_history(): void
    {
        // Given
        /** @var History $history */
        $history = History::factory()->create([
            'name' => 'Test History',
            'type' => 'test',
            'user_id' => 1,
        ]);

        $command = new DeleteHistoryCommand(
            slugs: [$history->slug],
            userId: 1
        );

        // When
        $result = $this->handler->handle($command);

        // Then
        $this->assertTrue($result);
        $this->assertSoftDeleted('histories', ['slug' => $history->slug]);
    }

    public function test_it_deletes_multiple_histories(): void
    {
        // Given
        /** @var History $history1 */
        $history1 = History::factory()->create(['user_id' => 1]);

        /** @var History $history2 */
        $history2 = History::factory()->create(['user_id' => 1]);

        /** @var History $history3 */
        $history3 = History::factory()->create(['user_id' => 1]);

        $command = new DeleteHistoryCommand(
            slugs: [$history1->slug, $history2->slug, $history3->slug],
            userId: 1
        );

        // When
        $result = $this->handler->handle($command);

        // Then
        $this->assertTrue($result);
        $this->assertSoftDeleted('histories', ['slug' => $history1->slug]);
        $this->assertSoftDeleted('histories', ['slug' => $history2->slug]);
        $this->assertSoftDeleted('histories', ['slug' => $history3->slug]);
    }

    public function test_it_only_deletes_histories_for_specific_user(): void
    {
        // Given
        /** @var History $user1History */
        $user1History = History::factory()->create(['user_id' => 1]);

        /** @var History $user2History */
        $user2History = History::factory()->create(['user_id' => 2]);

        $command = new DeleteHistoryCommand(
            slugs: [$user1History->slug, $user2History->slug],
            userId: 1
        );

        // When
        $result = $this->handler->handle($command);

        // Then
        $this->assertTrue($result);
        $this->assertSoftDeleted('histories', ['slug' => $user1History->slug]);
        $this->assertDatabaseHas('histories', [
            'slug' => $user2History->slug,
            'deleted_at' => null,
        ]);
    }

    public function test_it_handles_non_existent_slugs(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: ['non-existent-slug'],
            userId: 1
        );

        // When
        $result = $this->handler->handle($command);

        // Then
        $this->assertTrue($result);
    }

    public function test_it_handles_empty_slugs_array(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: [],
            userId: 1
        );

        // When
        $result = $this->handler->handle($command);

        // Then
        $this->assertTrue($result);
    }

    public static function deleteHistoryCommandProvider(): array
    {
        return [
            'user 1 with single slug' => [
                'command' => new DeleteHistoryCommand(
                    slugs: ['test-slug'],
                    userId: 1
                ),
                'userId' => 1,
            ],
            'user 2 with multiple slugs' => [
                'command' => new DeleteHistoryCommand(
                    slugs: ['slug-1', 'slug-2'],
                    userId: 2
                ),
                'userId' => 2,
            ],
        ];
    }
}
