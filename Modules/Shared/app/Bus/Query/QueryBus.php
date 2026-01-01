<?php

namespace Modules\Shared\Bus\Query;

use Modules\Shared\Contracts\Query\QueryBusInterface;
use Modules\Shared\Contracts\Query\QueryHandlerInterface;
use Modules\Shared\Contracts\Query\QueryInterface;
use Modules\Shared\Exceptions\UnregisteredQueryException;

final class QueryBus implements QueryBusInterface
{
    public function __construct(
        private array $handlers = [],
    ) {
        //
    }

    /**
     * @inheritDoc
     *
     * @throws UnregisteredQueryException
     */
    public function ask(QueryInterface $query): mixed
    {
        return $this->resolveHandler($query)->handle($query);
    }

    /**
     * Register a query handler.
     */
    public function register(string $queryClass, string $handlerClass): self
    {
        $this->handlers[$queryClass] = $handlerClass;

        return $this;
    }

    /**
     * Resolve the handler for the given query.
     *
     * @throws UnregisteredQueryException
     */
    private function resolveHandler(QueryInterface $query): QueryHandlerInterface
    {
        $queryClass = get_class($query);

        if (!isset($this->handlers[$queryClass])) {
            throw new UnregisteredQueryException(queryClass: $queryClass);
        }

        return app($this->handlers[$queryClass]);
    }
}
