<?php

namespace Modules\History\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Modules\History\Models\History;

final class HistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = History::class;

    /**
     * @inheritDoc
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'type' => $this->faker->word(),
            'user_id' => $this->faker->unique()->numberBetween(1, 100),
            'metadata' => null,
        ];
    }
}
