<?php

namespace Modules\Shared\Tests\Unit\Bus\Query\Fixtures;

use Modules\Shared\Contracts\Query\QueryHandlerInterface;
use Modules\Shared\Contracts\Query\QueryInterface;

final class GetBookHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): ?array
    {
        /** @var GetBookQuery $query */

        if ($query->bookId === 'non-existent') {
            return null;
        }

        return [
            'id' => $query->bookId,
            'title' => 'Sample Book',
            'author' => 'Sample Author',
            'isbn' => '978-0000000000',
        ];
    }
}
