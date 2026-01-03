<?php

namespace Modules\Shared\Bus\Command;

use Modules\Shared\Contracts\Command\CommandBusInterface;
use Modules\Shared\UseCases\AbstractUseCase;

abstract class AbstractCommandUseCase extends AbstractUseCase
{
    public function __construct(
        protected CommandBusInterface $commandBus
    ) {
        //
    }
}
