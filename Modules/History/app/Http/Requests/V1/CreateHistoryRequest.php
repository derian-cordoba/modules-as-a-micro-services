<?php

namespace Modules\History\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;
use Modules\History\Bus\Command\V1\Create\CreateHistoryCommand;
use Modules\Shared\Contracts\Command\AsCommandInterface;

final class CreateHistoryRequest extends FormRequest implements AsCommandInterface
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'user_id' => ['required', 'integer'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function asCommand(): CreateHistoryCommand
    {
        return new CreateHistoryCommand(
            name: $this->input(key: 'name'),
            type: $this->input(key: 'type'),
            userId: $this->input(key: 'user_id'),
            metadata: $this->input(key: 'metadata'),
        );
    }
}
