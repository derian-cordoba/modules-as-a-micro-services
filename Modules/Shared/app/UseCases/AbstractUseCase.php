<?php

namespace Modules\Shared\UseCases;

use Modules\Shared\Contracts\UseCase\UseCaseInterface;

abstract class AbstractUseCase implements UseCaseInterface
{
    abstract public function execute(mixed $command): mixed;
}
