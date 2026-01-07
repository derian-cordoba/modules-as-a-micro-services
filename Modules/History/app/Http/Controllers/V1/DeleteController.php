<?php

namespace Modules\History\Http\Controllers\V1;

use Illuminate\Contracts\Support\Responsable;
use Modules\History\Http\Requests\V1\DeleteHistoryRequest;
use Modules\History\UseCases\V1\Delete\DeleteHistoryUseCase;
use Modules\Shared\Http\Response\ModelResponse;

final readonly class DeleteController
{
    public function __construct(
        private DeleteHistoryUseCase $useCase,
    ) {
        //
    }

    public function __invoke(DeleteHistoryRequest $request): Responsable
    {
        $this->useCase->execute(
            command: $request->asCommand(),
        );

        return ModelResponse::deleted();
    }
}
