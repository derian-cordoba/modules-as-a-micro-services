<?php

namespace Modules\History\UseCases\V1\Fetch;

use Modules\History\Bus\Query\V1\Fetch\FetchHistoriesQuery;
use Modules\History\UseCases\V1\Fetch\Output\FetchHistoriesOutput;
use Modules\Shared\Bus\Query\AbstractQueryUseCase;

final class FetchHistoriesUseCase extends AbstractQueryUseCase
{
    public function execute(mixed $command): FetchHistoriesOutput
    {
        /** @var FetchHistoriesQuery $command */
        $histories = $this->queryBus->ask($command);

        return new FetchHistoriesOutput(
            histories: $histories,
        );
    }
}
