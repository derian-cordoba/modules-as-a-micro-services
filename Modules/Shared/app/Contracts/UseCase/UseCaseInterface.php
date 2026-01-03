<?php

namespace Modules\Shared\Contracts\UseCase;

interface UseCaseInterface
{
    /** Execute the use case with the given input and return the result.
     *
     * @param mixed $command The input command for the use case.
     * @return mixed The result of the use case execution.
     */
    public function execute(mixed $command): mixed;
}
