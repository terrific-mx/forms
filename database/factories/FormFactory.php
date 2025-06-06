<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'user_id' => User::factory(),
            'ulid' => (string) str()->ulid(),
        ];
    }
}
