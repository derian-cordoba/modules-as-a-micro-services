<?php

namespace Modules\Shared\Bus\Query;

use Modules\Shared\Contracts\Query\QueryBusInterface;
use Modules\Shared\UseCases\AbstractUseCase;

abstract class AbstractQueryUseCase extends AbstractUseCase
{
    public function __construct(
        protected QueryBusInterface $queryBus
    ) {
        //
    }
}
