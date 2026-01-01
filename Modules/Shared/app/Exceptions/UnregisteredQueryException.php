<?php

namespace Modules\Shared\Exceptions;

use Symfony\Component\HttpFoundation\Response;

final class UnregisteredQueryException extends \Exception
{
    public function __construct(string $queryClass)
    {
        parent::__construct(
            message: "No handler registered for query: {$queryClass}",
            code: Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }
}
