<?php

namespace Modules\Shared\Bus\Command;

use Modules\Shared\Contracts\Command\CommandBusInterface;
use Modules\Shared\Contracts\Command\CommandHandlerInterface;
use Modules\Shared\Contracts\Command\CommandInterface;
use Modules\Shared\Exceptions\UnregisteredCommandException;

final class CommandBus implements CommandBusInterface
{
    public function __construct(
        private array $handlers = [],
    ) {
        //
    }

    /**
     * @inheritDoc
     *
     * @throws UnregisteredCommandException
     */
    public function dispatch(CommandInterface $command): mixed
    {
        return $this->resolveHandler($command)->handle($command);
    }

    /**
     * Register a command handler.
     */
    public function register(string $commandClass, string $handlerClass): self
    {
        $this->handlers[$commandClass] = $handlerClass;

        return $this;
    }

    /**
     * Resolve the handler for the given command.
     *
     * @throws UnregisteredCommandException
     */
    private function resolveHandler(CommandInterface $command): CommandHandlerInterface
    {
        $commandClass = get_class($command);

        if (!isset($this->handlers[$commandClass])) {
            throw new UnregisteredCommandException(commandClass: $commandClass);
        }

        return app($this->handlers[$commandClass]);
    }
}
