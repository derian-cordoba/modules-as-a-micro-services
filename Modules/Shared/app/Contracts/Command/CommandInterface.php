<?php

namespace Modules\Shared\Contracts\Command;

interface CommandInterface
{
    /**
     * Convert the command to an array representation.
     */
    public function asArray(): array;
}
