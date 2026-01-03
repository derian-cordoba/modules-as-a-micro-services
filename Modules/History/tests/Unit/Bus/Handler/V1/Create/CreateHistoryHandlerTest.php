<?php

namespace Modules\History\Tests\Unit\Bus\Handler\V1\Create;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\History\Bus\Command\V1\Create\CreateHistoryCommand;
use Modules\History\Bus\Handler\V1\Create\CreateHistoryHandler;
use Modules\History\Models\History;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CreateHistoryHandlerTest extends TestCase
{
    use RefreshDatabase;

    private CreateHistoryHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new CreateHistoryHandler();
    }

    #[DataProvider(methodName: 'createHistoryCommandProvider')]
    public function test_it_can_handle_create_history_command(
        CreateHistoryCommand $command
    ): void {
        // When
        $history = $this->handler->handle($command);

        // Then
        $this->assertInstanceOf(History::class, $history);
        $this->assertDatabaseHas('histories', [
            'name' => $command->name,
            'type' => $command->type,
            'user_id' => $command->userId,
        ]);
    }

    public function test_it_creates_history_with_metadata(): void
    {
        // Given
        $metadata = [
            'action' => 'created',
            'resource' => 'order',
        ];

        $command = new CreateHistoryCommand(
            name: 'Order Created',
            type: 'order',
            userId: 1,
            metadata: $metadata
        );

        // When
        $history = $this->handler->handle($command);

        // Then
        $this->assertNotNull($history->metadata);
        $this->assertEquals($metadata, $history->metadata);
    }

    public function test_it_creates_history_without_metadata(): void
    {
        // Given
        $command = new CreateHistoryCommand(
            name: 'Simple Action',
            type: 'action',
            userId: 2
        );

        // When
        $history = $this->handler->handle($command);

        // Then
        $this->assertNull($history->metadata);
    }

    public function test_it_executes_within_transaction(): void
    {
        // Given
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(fn ($callback) => $callback());

        $command = new CreateHistoryCommand(
            name: 'Test Transaction',
            type: 'test',
            userId: 3
        );

        // When
        $history = $this->handler->handle($command);

        // Then
        $this->assertInstanceOf(History::class, $history);
    }

    public function test_it_returns_history_with_id(): void
    {
        // Given
        $command = new CreateHistoryCommand(
            name: 'Test History',
            type: 'test',
            userId: 4
        );

        // When
        $history = $this->handler->handle($command);

        // Then
        $this->assertNotNull($history->id);
        $this->assertIsInt($history->id);
    }

    public static function createHistoryCommandProvider(): array
    {
        return [
            'basic history' => [
                'command' => new CreateHistoryCommand(
                    name: 'User Login',
                    type: 'authentication',
                    userId: 1
                ),
            ],
            'history with metadata' => [
                'command' => new CreateHistoryCommand(
                    name: 'Profile Update',
                    type: 'user',
                    userId: 2,
                    metadata: ['field' => 'email', 'old' => 'old@test.com', 'new' => 'new@test.com']
                ),
            ],
        ];
    }
}
