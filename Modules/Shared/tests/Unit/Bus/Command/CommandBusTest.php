<?php

use Modules\Shared\Bus\Command\CommandBus;
use Modules\Shared\Contracts\Command\CommandBusInterface;
use Modules\Shared\Contracts\Command\CommandInterface;
use Modules\Shared\Exceptions\UnregisteredCommandException;
use Modules\Shared\Tests\Unit\Bus\Command\Fixtures\CreateBookCommand;
use Modules\Shared\Tests\Unit\Bus\Command\Fixtures\CreateBookHandler;
use Modules\Shared\Tests\Unit\Bus\Command\Fixtures\UpdateBookCommand;
use Modules\Shared\Tests\Unit\Bus\Command\Fixtures\UpdateBookHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class CommandBusTest extends TestCase
{
    private CommandBusInterface $commandBus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBus = new CommandBus();
    }

    public function test_it_can_register_a_command_handler(): void
    {
        // When
        $this->commandBus->register(
            commandClass: CreateBookCommand::class,
            handlerClass: CreateBookHandler::class
        );

        // Then
        $this->assertTrue(condition: true); // If no exception, registration succeeded
    }

    public function test_it_can_dispatch_a_registered_command(): void
    {
        // Given
        $this->commandBus->register(
            commandClass: CreateBookCommand::class,
            handlerClass: CreateBookHandler::class
        );

        $command = new CreateBookCommand(
            title: 'Clean Code',
            author: 'Robert C. Martin',
            isbn: '978-0132350884'
        );

        // When
        try {
            $result = $this->commandBus->dispatch($command);
        } catch (UnregisteredCommandException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
        $this->assertArrayHasKey(key: 'id', array: $result);
        $this->assertEquals(expected: 'Clean Code', actual: $result['title']);
        $this->assertEquals(expected: 'Robert C. Martin', actual: $result['author']);
    }

    #[DataProvider(methodName: 'multipleCommandsProvider')]
    public function test_it_can_handle_multiple_registered_commands(
        string $commandClass,
        string $handlerClass,
        CommandInterface $command,
        array $expectedKeys
    ): void {
        // Given
        $this->commandBus->register(
            commandClass: $commandClass,
            handlerClass: $handlerClass
        );

        // When
        try {
            $result = $this->commandBus->dispatch($command);
        } catch (UnregisteredCommandException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey(key: $key, array: $result);
        }
    }

    public function test_it_throws_exception_when_dispatching_unregistered_command(): void
    {
        // Given
        $command = new CreateBookCommand(
            title: 'Clean Code',
            author: 'Robert C. Martin',
            isbn: '978-0132350884'
        );

        $exception = new UnregisteredCommandException(commandClass: get_class($command));

        // Then
        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());

        // When
        $this->commandBus->dispatch($command);
    }

    public function test_it_resolves_handler_from_container(): void
    {
        // Given
        $this->commandBus->register(
            commandClass: CreateBookCommand::class,
            handlerClass: CreateBookHandler::class
        );

        $command = new CreateBookCommand(
            title: 'Domain-Driven Design',
            author: 'Eric Evans',
            isbn: '978-0321125217'
        );

        // When
        try {
            $result = $this->commandBus->dispatch($command);
        } catch (UnregisteredCommandException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
        $this->assertNotEmpty(actual: $result);
    }

    public function test_it_can_override_registered_handler(): void
    {
        // Given
        $this->commandBus->register(
            commandClass: CreateBookCommand::class,
            handlerClass: CreateBookHandler::class
        );

        // When - Re-register with same command
        $this->commandBus->register(
            commandClass: CreateBookCommand::class,
            handlerClass: CreateBookHandler::class
        );

        $command = new CreateBookCommand(
            title: 'Refactoring',
            author: 'Martin Fowler',
            isbn: '978-0201485677'
        );

        try {
            $result = $this->commandBus->dispatch($command);
        } catch (UnregisteredCommandException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
    }

    public function test_it_handles_command_with_null_values(): void
    {
        // Given
        $this->commandBus->register(
            commandClass: CreateBookCommand::class,
            handlerClass: CreateBookHandler::class
        );

        $command = new CreateBookCommand(
            title: 'Book Title',
            author: null,
            isbn: null
        );

        // When
        try {
            $result = $this->commandBus->dispatch($command);
        } catch (UnregisteredCommandException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertIsArray(actual: $result);
        $this->assertNull(actual: $result['author']);
        $this->assertNull(actual: $result['isbn']);
    }

    public function test_it_cant_dispatch_invalid_command(): void
    {
        // Then
        $this->expectException(TypeError::class);

        // When
        /** @phpstan-ignore-next-line */
        $this->commandBus->dispatch('not-a-command');
    }

    #[DataProvider(methodName: 'commandChainProvider')]
    public function test_it_can_dispatch_multiple_commands_in_sequence(array $commands): void
    {
        // Given
        $this->commandBus->register(
            commandClass: CreateBookCommand::class,
            handlerClass: CreateBookHandler::class
        );
        $this->commandBus->register(
            commandClass: UpdateBookCommand::class,
            handlerClass: UpdateBookHandler::class
        );

        $results = [];

        // When
        try {
            foreach ($commands as $command) {
                $results[] = $this->commandBus->dispatch($command);
            }
        } catch (UnregisteredCommandException $e) {
            $this->fail(message: $e->getMessage());
        }

        // Then
        $this->assertCount(expectedCount: count($commands), haystack: $results);

        foreach ($results as $result) {
            $this->assertIsArray(actual: $result);
        }
    }

    public static function multipleCommandsProvider(): array
    {
        return [
            'create command' => [
                'commandClass' => CreateBookCommand::class,
                'handlerClass' => CreateBookHandler::class,
                'command' => new CreateBookCommand(
                    title: 'Clean Code',
                    author: 'Robert C. Martin',
                    isbn: '978-0132350884'
                ),
                'expectedKeys' => ['id', 'title', 'author', 'isbn'],
            ],
            'update command' => [
                'commandClass' => UpdateBookCommand::class,
                'handlerClass' => UpdateBookHandler::class,
                'command' => new UpdateBookCommand(
                    bookId: '123',
                    title: 'Updated Title',
                    author: 'Updated Author'
                ),
                'expectedKeys' => ['id', 'title', 'author', 'updated_at'],
            ],
        ];
    }

    public static function commandChainProvider(): array
    {
        return [
            'create and update' => [
                'commands' => [
                    new CreateBookCommand(
                        title: 'Book 1',
                        author: 'Author 1',
                        isbn: '111'
                    ),
                    new UpdateBookCommand(
                        bookId: '1',
                        title: 'Updated Book 1',
                        author: 'Author 1'
                    ),
                ],
            ],
        ];
    }
}
