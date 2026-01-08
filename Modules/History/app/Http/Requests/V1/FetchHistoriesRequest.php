<?php

namespace Modules\History\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\History\Bus\Query\V1\Fetch\FetchHistoriesQuery;
use Modules\Shared\Contracts\Query\AsQueryInterface;

final class FetchHistoriesRequest extends FormRequest implements AsQueryInterface
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function asQuery(): FetchHistoriesQuery
    {
        return new FetchHistoriesQuery(
            perPage: $this->input('per_page'),
            page: $this->input('page'),
        );
    }
}
