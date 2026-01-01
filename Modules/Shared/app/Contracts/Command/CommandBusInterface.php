<?php

namespace Modules\Shared\Contracts\Command;

interface CommandBusInterface
{
    /**
     * Dispatch the given command to its corresponding handler.
     */
    public function dispatch(CommandInterface $command): mixed;
}
