<?php

namespace Modules\History\Http\Controllers\V1;

use Illuminate\Contracts\Support\Responsable;
use Modules\History\Http\Requests\V1\CreateHistoryRequest;
use Modules\History\Http\Resources\V1\HistoryResource;
use Modules\History\UseCases\V1\Create\CreateHistoryUseCase;
use Modules\Shared\Http\Response\ModelResponse;

final readonly class CreateController
{
    public function __construct(
        private CreateHistoryUseCase $useCase,
    ) {
        //
    }

    public function __invoke(CreateHistoryRequest $request): Responsable
    {
        $output = $this->useCase->execute(
            command: $request->asCommand(),
        );

        return ModelResponse::created(
            data: new HistoryResource(resource: $output->history),
        );
    }
}
