<?php

namespace Modules\Shared\Tests\Unit\Bus\Command\Fixtures;

use Modules\Shared\Contracts\Command\CommandInterface;

final readonly class CreateBookCommand implements CommandInterface
{
    public function __construct(
        public string $title,
        public ?string $author = null,
        public ?string $isbn = null,
    ) {
        //
    }

    /**
     * @inheritDoc
     */
    public function asArray(): array
    {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
        ];
    }
}
