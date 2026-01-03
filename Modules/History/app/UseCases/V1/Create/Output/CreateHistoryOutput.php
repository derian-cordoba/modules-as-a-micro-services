<?php

namespace Modules\History\UseCases\V1\Create\Output;

use Modules\History\Models\History;

final readonly class CreateHistoryOutput
{
    public function __construct(
        public History $history,
    ) {
        //
    }
}
