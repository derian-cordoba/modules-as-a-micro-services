<?php

namespace Modules\History\Tests\Unit\UseCases\V1\Delete;

use Mockery;
use Modules\History\Bus\Command\V1\Delete\DeleteHistoryCommand;
use Modules\History\UseCases\V1\Delete\DeleteHistoryUseCase;
use Modules\Shared\Contracts\Command\CommandBusInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use TypeError;

final class DeleteHistoryUseCaseTest extends TestCase
{
    private CommandBusInterface $commandBus;
    private DeleteHistoryUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandBus = Mockery::mock(CommandBusInterface::class);
        $this->useCase = new DeleteHistoryUseCase($this->commandBus);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[DataProvider(methodName: 'deleteHistoryCommandProvider')]
    public function test_it_can_execute_delete_history_use_case(
        DeleteHistoryCommand $command
    ): void {
        // Given
        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(DeleteHistoryCommand::class))
            ->andReturnTrue();

        // When
        $result = $this->useCase->execute($command);

        // Then
        $this->assertTrue($result);
    }

    public function test_it_dispatches_command_through_bus(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: ['test-slug-1', 'test-slug-2'],
            userId: 1
        );

        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->with($command)
            ->andReturnTrue();

        // When
        $result = $this->useCase->execute($command);

        // Then
        $this->assertTrue($result);
    }

    public function test_it_handles_single_slug(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: ['single-slug'],
            userId: 1
        );

        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->andReturnTrue();

        // When
        $result = $this->useCase->execute($command);

        // Then
        $this->assertTrue($result);
    }

    public function test_it_handles_multiple_slugs(): void
    {
        // Given
        $command = new DeleteHistoryCommand(
            slugs: ['slug-1', 'slug-2', 'slug-3', 'slug-4', 'slug-5'],
            userId: 1
        );

        $this->commandBus
            ->shouldReceive('dispatch')
            ->once()
            ->andReturnTrue();

        // When
        $result = $this->useCase->execute($command);

        // Then
        $this->assertTrue($result);
    }

    public function test_it_cant_execute_with_invalid_input(): void
    {
        // Then
        $this->expectException(TypeError::class);

        // When
        /** @phpstan-ignore-next-line */
        $this->useCase->execute('invalid-input');
    }

    public static function deleteHistoryCommandProvider(): array
    {
        return [
            'single slug' => [
                'command' => new DeleteHistoryCommand(
                    slugs: ['test-history-slug'],
                    userId: 1
                ),
            ],
            'multiple slugs' => [
                'command' => new DeleteHistoryCommand(
                    slugs: ['slug-1', 'slug-2', 'slug-3'],
                    userId: 2
                ),
            ],
            'different user' => [
                'command' => new DeleteHistoryCommand(
                    slugs: ['user-history-slug'],
                    userId: 999
                ),
            ],
        ];
    }
}
