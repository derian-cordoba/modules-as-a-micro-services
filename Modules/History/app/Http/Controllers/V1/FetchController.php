<?php

namespace Modules\History\Http\Controllers\V1;

use Illuminate\Contracts\Support\Responsable;
use Modules\History\Http\Requests\V1\FetchHistoriesRequest;
use Modules\History\Http\Resources\V1\HistoryCollection;
use Modules\History\UseCases\V1\Fetch\FetchHistoriesUseCase;
use Modules\Shared\Http\Response\CollectionResponse;

final readonly class FetchController
{
    public function __construct(
        private FetchHistoriesUseCase $useCase,
    ) {
        //
    }

    public function __invoke(FetchHistoriesRequest $request): Responsable
    {
        $output = $this->useCase->execute(
            command: $request->asQuery(),
        );

        return CollectionResponse::success(
            data: new HistoryCollection($output->histories),
        );
    }
}
