<?php

namespace Modules\Shared\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class UnregisteredCommandException extends \Exception
{
    public function __construct(string $commandClass)
    {
        parent::__construct(
            message: "No handler registered for command: {$commandClass}",
            code: Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }
}
