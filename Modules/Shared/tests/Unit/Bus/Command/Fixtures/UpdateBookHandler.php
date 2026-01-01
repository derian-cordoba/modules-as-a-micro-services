<?php

namespace Modules\Shared\Tests\Unit\Bus\Command\Fixtures;

use Modules\Shared\Contracts\Command\CommandHandlerInterface;
use Modules\Shared\Contracts\Command\CommandInterface;

final class UpdateBookHandler implements CommandHandlerInterface
{
    public function handle(CommandInterface $command): array
    {
        /** @var UpdateBookCommand $command */
        return [
            'id' => $command->bookId,
            'title' => $command->title,
            'author' => $command->author,
            'updated_at' => now()->toISOString(),
        ];
    }
}
