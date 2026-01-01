<?php

namespace Modules\Shared\Tests\Unit\Bus\Command\Fixtures;

use Modules\Shared\Contracts\Command\CommandInterface;

final readonly class UpdateBookCommand implements CommandInterface
{
    public function __construct(
        public string $bookId,
        public string $title,
        public ?string $author = null,
    ) {}
}
