<?php

namespace Modules\Shared\Contracts\Query;

interface QueryBusInterface
{
    /**
     * Ask the given query to its corresponding handler.
     */
    public function ask(QueryInterface $query): mixed;
}
