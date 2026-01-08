<?php

namespace Modules\History\UseCases\V1\Fetch\Output;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class FetchHistoriesOutput
{
    public function __construct(
        public LengthAwarePaginator $histories,
    ) {
        //
    }
}
