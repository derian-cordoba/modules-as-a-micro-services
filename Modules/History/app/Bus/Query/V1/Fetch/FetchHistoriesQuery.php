<?php

namespace Modules\History\Bus\Query\V1\Fetch;

use Modules\Shared\Contracts\Query\QueryInterface;

final readonly class FetchHistoriesQuery implements QueryInterface
{
    public function __construct(
        public ?int $perPage = null,
        public ?int $page = null,
    ) {
        //
    }
}
