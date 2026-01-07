<?php

namespace Modules\History\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\History\Bus\Command\V1\Delete\DeleteHistoryCommand;
use Modules\Shared\Contracts\Command\AsCommandInterface;

final class DeleteHistoryRequest extends FormRequest implements AsCommandInterface
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'slugs' => ['required', 'array'],
            'slugs.*' => ['string'],
            'user_id' => ['required', 'integer'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function asCommand(): DeleteHistoryCommand
    {
        return new DeleteHistoryCommand(
            slugs: $this->array(key: 'slugs'),
            userId: $this->integer(key: 'user_id'),
        );
    }
}
