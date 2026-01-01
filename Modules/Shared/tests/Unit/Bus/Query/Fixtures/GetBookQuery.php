<?php

namespace Modules\Shared\Tests\Unit\Bus\Query\Fixtures;

use Modules\Shared\Contracts\Query\QueryInterface;

final readonly class GetBookQuery implements QueryInterface
{
    public function __construct(
        public string $bookId,
    ) {
        //
    }
}
