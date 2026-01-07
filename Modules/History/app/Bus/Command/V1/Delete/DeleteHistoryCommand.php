<?php

namespace Modules\History\Bus\Command\V1\Delete;

use Modules\Shared\Contracts\Command\CommandInterface;

final readonly class DeleteHistoryCommand implements CommandInterface
{
    public function __construct(
        public array $slugs,
        public int $userId,
    ) {
        //
    }

    /**
     * @inheritDoc
     */
    public function asArray(): array
    {
        return [
            'slugs' => $this->slugs,
            'user_id' => $this->userId,
        ];
    }
}
