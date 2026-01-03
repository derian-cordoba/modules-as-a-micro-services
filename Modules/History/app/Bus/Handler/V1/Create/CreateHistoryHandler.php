<?php

namespace Modules\History\Bus\Handler\V1\Create;

use Illuminate\Support\Facades\DB;
use Modules\History\Models\History;
use Modules\Shared\Contracts\Command\CommandHandlerInterface;
use Modules\Shared\Contracts\Command\CommandInterface;
use Throwable;

final readonly class CreateHistoryHandler implements CommandHandlerInterface
{
    /**
     * @throws Throwable
     */
    public function handle(CommandInterface $command): History
    {
        return DB::transaction(
            callback: static fn () => History::query()->create(
                attributes: $command->asArray(),
            ),
        );
    }
}
