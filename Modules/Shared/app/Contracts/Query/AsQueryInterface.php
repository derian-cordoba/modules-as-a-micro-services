<?php

namespace Modules\Shared\Contracts\Query;

interface AsQueryInterface
{
    /**
     * Convert the implementing class to a use case query.
     */
    public function asQuery(): mixed;
}
