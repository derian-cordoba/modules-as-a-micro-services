<?php

namespace Modules\Shared\Tests\Unit\Bus\Command\Fixtures;

use Modules\Shared\Contracts\Command\CommandInterface;

final readonly class UpdateBookCommand implements CommandInterface
{
    public function __construct(
        public string $bookId,
        public string $title,
        public ?string $author = null,
    ) {
        //
    }

    /**
     * @inheritDoc
     */
    public function asArray(): array
    {
        return [
            'book_id' => $this->bookId,
            'title' => $this->title,
            'author' => $this->author,
        ];
    }
}
