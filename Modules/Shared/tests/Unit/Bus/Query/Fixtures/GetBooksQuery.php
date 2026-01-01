<?php

namespace Modules\Shared\Tests\Unit\Bus\Query\Fixtures;

use Modules\Shared\Contracts\Query\QueryInterface;

final readonly class GetBooksQuery implements QueryInterface
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 10,
        public ?string $search = null,
        public ?string $sortBy = null,
    ) {
        //
    }
}
