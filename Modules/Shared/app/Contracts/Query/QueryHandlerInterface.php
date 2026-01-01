<?php

namespace Modules\Shared\Contracts\Query;

interface QueryHandlerInterface
{
    public function handle(QueryInterface $query): mixed;
}
