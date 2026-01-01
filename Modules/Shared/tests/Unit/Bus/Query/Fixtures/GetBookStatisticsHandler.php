<?php

namespace Modules\Shared\Tests\Unit\Bus\Query\Fixtures;

use Modules\Shared\Contracts\Query\QueryHandlerInterface;
use Modules\Shared\Contracts\Query\QueryInterface;

final class GetBookStatisticsHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): array
    {
        /** @var GetBookStatisticsQuery $query */

        return [
            'total_books' => 150,
            'total_authors' => 75,
            'books_by_category' => [
                'fiction' => 80,
                'non-fiction' => 70,
            ],
            'date_range' => [
                'from' => $query->fromDate,
                'to' => $query->toDate,
            ],
        ];
    }
}
