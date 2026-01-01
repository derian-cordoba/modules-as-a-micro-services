<?php

namespace Modules\Shared\Tests\Unit\Bus\Query\Fixtures;

use Modules\Shared\Contracts\Query\QueryHandlerInterface;
use Modules\Shared\Contracts\Query\QueryInterface;

final class GetBooksHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): array
    {
        /** @var GetBooksQuery $query */

        if ($query->page > 100) {
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => $query->page,
                    'per_page' => $query->perPage,
                    'total' => 0,
                ],
            ];
        }

        return [
            'data' => [
                ['id' => '1', 'title' => 'Book 1'],
                ['id' => '2', 'title' => 'Book 2'],
            ],
            'pagination' => [
                'current_page' => $query->page,
                'per_page' => $query->perPage,
                'total' => 2,
            ],
        ];
    }
}
