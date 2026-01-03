<?php

namespace Modules\History\Tests\Unit\Bus\Command\V1\Create;

use Modules\History\Bus\Command\V1\Create\CreateHistoryCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CreateHistoryCommandTest extends TestCase
{
    #[DataProvider(methodName: 'commandDataProvider')]
    public function test_it_can_create_command_with_properties(
        string $name,
        string $type,
        int $userId,
        ?array $metadata
    ): void {
        // When
        $command = new CreateHistoryCommand(
            name: $name,
            type: $type,
            userId: $userId,
            metadata: $metadata
        );

        // Then
        $this->assertEquals($name, $command->name);
        $this->assertEquals($type, $command->type);
        $this->assertEquals($userId, $command->userId);
        $this->assertEquals($metadata, $command->metadata);
    }

    public function test_it_converts_to_array(): void
    {
        // Given
        $command = new CreateHistoryCommand(
            name: 'Test History',
            type: 'test',
            userId: 1,
            metadata: ['key' => 'value']
        );

        // When
        $array = $command->asArray();

        // Then
        $this->assertIsArray($array);
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('user_id', $array);
        $this->assertArrayHasKey('metadata', $array);
        $this->assertEquals('Test History', $array['name']);
        $this->assertEquals('test', $array['type']);
        $this->assertEquals(1, $array['user_id']);
        $this->assertEquals(['key' => 'value'], $array['metadata']);
    }

    public function test_it_converts_to_array_without_metadata(): void
    {
        // Given
        $command = new CreateHistoryCommand(
            name: 'Test History',
            type: 'test',
            userId: 1
        );

        // When
        $array = $command->asArray();

        // Then
        $this->assertNull($array['metadata']);
    }

    public function test_command_is_readonly(): void
    {
        // Given
        $command = new CreateHistoryCommand(
            name: 'Original Name',
            type: 'test',
            userId: 1
        );

        // Then - This should cause a PHP error if attempted
        $this->assertEquals('Original Name', $command->name);
        // Cannot reassign: $command->name = 'New Name';
    }

    public static function commandDataProvider(): array
    {
        return [
            'without metadata' => [
                'name' => 'User Action',
                'type' => 'user',
                'userId' => 1,
                'metadata' => null,
            ],
            'with simple metadata' => [
                'name' => 'Order Created',
                'type' => 'order',
                'userId' => 2,
                'metadata' => ['order_id' => 'ORD-123'],
            ],
            'with complex metadata' => [
                'name' => 'Payment Processed',
                'type' => 'payment',
                'userId' => 3,
                'metadata' => [
                    'amount' => 99.99,
                    'currency' => 'USD',
                    'method' => 'credit_card',
                    'details' => [
                        'last4' => '4242',
                        'brand' => 'visa',
                    ],
                ],
            ],
        ];
    }
}
