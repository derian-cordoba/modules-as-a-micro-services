<?php

namespace Modules\History\UseCases\V1\Create;

use Modules\History\Bus\Command\V1\Create\CreateHistoryCommand;
use Modules\History\UseCases\V1\Create\Output\CreateHistoryOutput;
use Modules\Shared\Bus\Command\AbstractCommandUseCase;

final class CreateHistoryUseCase extends AbstractCommandUseCase
{
    public function execute(mixed $command): CreateHistoryOutput
    {
        /** @var CreateHistoryCommand $command */
        $history = $this->commandBus->dispatch(command: $command);

        return new CreateHistoryOutput(
            history: $history,
        );
    }
}
