<?php

namespace Modules\History\Tests\Unit\UseCases\V1\Create;

use Mockery;
use Modules\History\Bus\Command\V1\Create\CreateHistoryCommand;
use Modules\History\Models\History;
use Modules\History\UseCases\V1\Create\CreateHistoryUseCase;
use Modules\History\UseCases\V1\Create\Output\CreateHistoryOutput;
use Modules\Shared\Contracts\Command\CommandBusInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use TypeError;

final class CreateHistoryUseCaseTest extends TestCase
{
    private CommandBusInterface $commandBus;
    private CreateHistoryUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = Mockery::mock(CommandBusInterface::class);
        $this->useCase = new CreateHistoryUseCase($this->commandBus);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[DataProvider(methodName: 'createHistoryCommandProvider')]
    public function test_it_can_execute_create_history_use_case(
        CreateHistoryCommand $command,
        History $expectedHistory
    ): void {
        // Given
        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(CreateHistoryCommand::class))
            ->andReturn($expectedHistory);

        // When
        $output = $this->useCase->execute($command);

        // Then
        $this->assertInstanceOf(CreateHistoryOutput::class, $output);
        $this->assertSame($expectedHistory, $output->history);
        $this->assertEquals($command->name, $output->history->name);
        $this->assertEquals($command->type, $output->history->type);
        $this->assertEquals($command->userId, $output->history->user_id);
    }

    public function test_it_creates_history_with_metadata(): void
    {
        // Given
        $metadata = [
            'action' => 'created',
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ];

        $command = new CreateHistoryCommand(
            name: 'User Login',
            type: 'authentication',
            userId: 1,
            metadata: $metadata
        );

        $history = new History([
            'name' => 'User Login',
            'type' => 'authentication',
            'user_id' => 1,
            'metadata' => $metadata,
        ]);

        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->andReturn($history);

        // When
        $output = $this->useCase->execute($command);

        // Then
        $this->assertNotNull($output->history->metadata);
        $this->assertIsArray($output->history->metadata);
        $this->assertEquals($metadata, $output->history->metadata);
    }

    public function test_it_creates_history_without_metadata(): void
    {
        // Given
        $command = new CreateHistoryCommand(
            name: 'Page View',
            type: 'navigation',
            userId: 2,
            metadata: null
        );

        $history = new History([
            'name' => 'Page View',
            'type' => 'navigation',
            'user_id' => 2,
            'metadata' => null,
        ]);

        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->andReturn($history);

        // When
        $output = $this->useCase->execute($command);

        // Then
        $this->assertNull($output->history->metadata);
    }

    public function test_it_dispatches_command_through_bus(): void
    {
        // Given
        $command = new CreateHistoryCommand(
            name: 'Profile Updated',
            type: 'user_action',
            userId: 5,
            isScanned: false,
        );

        $history = new History([
            'name' => 'Profile Updated',
            'type' => 'user_action',
            'user_id' => 5,
            'is_scanned' => false,
        ]);

        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->with($command)
            ->andReturn($history);

        // When
        $output = $this->useCase->execute($command);

        // Then
        $this->assertNotNull($output->history);
        $this->assertEquals('Profile Updated', $output->history->name);
    }

    public function test_it_cant_execute_with_invalid_input(): void
    {
        // Then
        $this->expectException(TypeError::class);

        // When
        /** @phpstan-ignore-next-line */
        $this->useCase->execute('invalid-input');
    }

    public function test_it_handles_special_characters_in_name(): void
    {
        // Given
        $command = new CreateHistoryCommand(
            name: 'Test "Special" Characters & <HTML lang="en">',
            type: 'test',
            userId: 10,
            isScanned: false,
        );

        $history = new History([
            'name' => 'Test "Special" Characters & <HTML lang="en">',
            'type' => 'test',
            'user_id' => 10,
            'is_scanned' => false,
        ]);

        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->andReturn($history);

        // When
        $output = $this->useCase->execute($command);

        // Then
        $this->assertEquals('Test "Special" Characters & <HTML lang="en">', $output->history->name);
    }

    public static function createHistoryCommandProvider(): array
    {
        return [
            'simple history without metadata' => [
                'command' => new CreateHistoryCommand(
                    name: 'User Logged In',
                    type: 'authentication',
                    userId: 1
                ),
                'expectedHistory' => new History([
                    'name' => 'User Logged In',
                    'type' => 'authentication',
                    'user_id' => 1,
                    'is_scanned' => false,
                    'metadata' => null,
                ]),
            ],
            'history with metadata' => [
                'command' => new CreateHistoryCommand(
                    name: 'Order Created',
                    type: 'order',
                    userId: 2,
                    metadata: [
                        'order_id' => 'ORD-123',
                        'amount' => 99.99,
                    ]
                ),
                'expectedHistory' => new History([
                    'name' => 'Order Created',
                    'type' => 'order',
                    'user_id' => 2,
                    'is_scanned' => false,
                    'metadata' => [
                        'order_id' => 'ORD-123',
                        'amount' => 99.99,
                    ],
                ]),
            ],
            'history with complex metadata' => [
                'command' => new CreateHistoryCommand(
                    name: 'Payment Processed',
                    type: 'payment',
                    userId: 3,
                    metadata: [
                        'payment_method' => 'credit_card',
                        'amount' => 149.99,
                        'currency' => 'USD',
                        'transaction' => [
                            'id' => 'TXN-456',
                            'status' => 'completed',
                        ],
                    ]
                ),
                'expectedHistory' => new History([
                    'name' => 'Payment Processed',
                    'type' => 'payment',
                    'user_id' => 3,
                    'is_scanned' => false,
                    'metadata' => [
                        'payment_method' => 'credit_card',
                        'amount' => 149.99,
                        'currency' => 'USD',
                        'transaction' => [
                            'id' => 'TXN-456',
                            'status' => 'completed',
                        ],
                    ],
                ]),
            ],
        ];
    }
}
