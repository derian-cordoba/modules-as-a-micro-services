<?php

namespace Modules\History\Bus\Command\V1\Create;

use Modules\Shared\Contracts\Command\CommandInterface;

final readonly class CreateHistoryCommand implements CommandInterface
{
    public function __construct(
        public string $name,
        public string $type,
        public int $userId,
        public ?array $metadata = null,
    ) {
        //
    }

    /**
     * @inheritDoc
     */
    public function asArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'user_id' => $this->userId,
            'metadata' => $this->metadata,
        ];
    }
}
