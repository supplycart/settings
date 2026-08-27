<?php

declare(strict_types=1);

namespace Supplycart\Settings\Tests\Stubs;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Company> */
final class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /** @return array<string, mixed> */
    #[\Override]
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
        ];
    }
}
