<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Marchand>
 */
class MarchandFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'nom' => $this->faker->company(),
            'code' => strtoupper(Str::random(6)),
            'description' => $this->faker->catchPhrase(),
        ];
    }
}
