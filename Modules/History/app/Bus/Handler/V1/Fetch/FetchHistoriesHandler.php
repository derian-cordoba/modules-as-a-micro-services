<?php

namespace Modules\History\Bus\Handler\V1\Fetch;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\History\Bus\Query\V1\Fetch\FetchHistoriesQuery;
use Modules\History\Models\History;
use Modules\Shared\Contracts\Query\QueryHandlerInterface;
use Modules\Shared\Contracts\Query\QueryInterface;

final readonly class FetchHistoriesHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): LengthAwarePaginator
    {
        /** @var FetchHistoriesQuery $query */
        return History::query()->paginate(
            perPage: $query->perPage,
            page: $query->page,
        );
    }
}
