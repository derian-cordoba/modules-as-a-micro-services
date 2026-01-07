<?php

namespace Modules\History\Bus\Handler\V1\Delete;

use Illuminate\Support\Facades\DB;
use Modules\History\Bus\Command\V1\Delete\DeleteHistoryCommand;
use Modules\History\Models\History;
use Modules\Shared\Contracts\Command\CommandHandlerInterface;
use Modules\Shared\Contracts\Command\CommandInterface;
use Throwable;

final readonly class DeleteHistoryHandler implements CommandHandlerInterface
{
    /**
     * @throws Throwable
     */
    public function handle(CommandInterface $command): bool
    {
        /** @var DeleteHistoryCommand $command */
        DB::transaction(
            callback: static fn () => History::query()
                ->whereIn(column: 'slug', values: $command->slugs)
                ->where(column: 'user_id', operator: '=', value: $command->userId)
                ->delete(),
        );

        return true;
    }
}
