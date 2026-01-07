<?php

namespace Modules\History\UseCases\V1\Delete;

use Modules\History\Bus\Command\V1\Delete\DeleteHistoryCommand;
use Modules\Shared\Bus\Command\AbstractCommandUseCase;

final class DeleteHistoryUseCase extends AbstractCommandUseCase
{
    public function execute(mixed $command): bool
    {
        /** @var DeleteHistoryCommand $command */
        $this->commandBus->dispatch(command: $command);

        return true;
    }
}
