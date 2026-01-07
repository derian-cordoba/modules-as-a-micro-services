<?php

namespace Modules\History\Tests\Unit\Bus\Command\V1\Delete;

use Modules\History\Bus\Command\V1\Delete\DeleteHistoryCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class DeleteHistoryCommandTest extends TestCase
{
    #[DataProvider(methodName: 'commandDataProvider')]
    public function test_it_can_create_command_with_properties(
        array $slugs,
        int $userId
    ): void {
        // When
        $command = new DeleteHistoryCommand(
            slugs: $slugs,
            userId: $userId
        );

        // Then
        $this->assertEquals($slugs, $command->slugs);
        $this->assertEquals($userId, $command->userId);
    }

    public function test_it_converts_to_array(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: ['slug-1', 'slug-2', 'slug-3'],
            userId: 1
        );

        // When
        $array = $command->asArray();

        // Then
        $this->assertIsArray($array);
        $this->assertArrayHasKey('slugs', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertEquals(['slug-1', 'slug-2', 'slug-3'], $array['slugs']);
        $this->assertEquals(1, $array['user_id']);
    }

    public function test_it_handles_single_slug(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: ['single-slug'],
            userId: 5
        );

        // When
        $array = $command->asArray();

        // Then
        $this->assertCount(1, $array['slugs']);
        $this->assertEquals(['single-slug'], $array['slugs']);
    }

    public function test_it_handles_multiple_slugs(): void
    {
        // Given
        $slugs = ['slug-1', 'slug-2', 'slug-3', 'slug-4', 'slug-5'];
        $command = new DeleteHistoryCommand(
            slugs: $slugs,
            userId: 10
        );

        // When
        $array = $command->asArray();

        // Then
        $this->assertCount(5, $array['slugs']);
        $this->assertEquals($slugs, $array['slugs']);
    }

    public function test_it_handles_empty_slugs_array(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: [],
            userId: 1
        );

        // When
        $array = $command->asArray();

        // Then
        $this->assertEmpty($array['slugs']);
        $this->assertEquals([], $array['slugs']);
    }

    public function test_command_is_readonly(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: ['test-slug'],
            userId: 1
        );

        // Then
        $this->assertEquals(['test-slug'], $command->slugs);
        $this->assertEquals(1, $command->userId);
        // Cannot reassign: $command->slugs = ['new-slug'];
        // Cannot reassign: $command->userId = 2;
    }

    public static function commandDataProvider(): array
    {
        return [
            'single slug' => [
                'slugs' => ['history-slug-1'],
                'userId' => 1,
            ],
            'multiple slugs' => [
                'slugs' => ['slug-1', 'slug-2', 'slug-3'],
                'userId' => 2,
            ],
            'many slugs' => [
                'slugs' => ['a', 'b', 'c', 'd', 'e', 'f', 'g'],
                'userId' => 100,
            ],
            'empty slugs' => [
                'slugs' => [],
                'userId' => 5,
            ],
        ];
    }
}
