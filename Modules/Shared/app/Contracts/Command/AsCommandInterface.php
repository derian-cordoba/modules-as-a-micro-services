<?php

namespace Modules\Shared\Contracts\Command;

interface AsCommandInterface
{
    /**
     * Convert the implementing class to a use case command.
     */
    public function asCommand(): mixed;
}
