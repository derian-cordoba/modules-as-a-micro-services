<?php

namespace Modules\Shared\Tests\Unit\Bus\Command\Fixtures;

use Modules\Shared\Contracts\Command\CommandHandlerInterface;
use Modules\Shared\Contracts\Command\CommandInterface;

final class CreateBookHandler implements CommandHandlerInterface
{
    public function handle(CommandInterface $command): array
    {
        /** @var CreateBookCommand $command */
        return [
            'id' => uniqid(prefix: 'book_', more_entropy: true),
            'title' => $command->title,
            'author' => $command->author,
            'isbn' => $command->isbn,
            'created_at' => now()->toISOString(),
        ];
    }
}
