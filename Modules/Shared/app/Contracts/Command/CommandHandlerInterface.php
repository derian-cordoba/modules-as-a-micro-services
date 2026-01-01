<?php

namespace Modules\Shared\Contracts\Command;

interface CommandHandlerInterface
{
    public function handle(CommandInterface $command): mixed;
}
