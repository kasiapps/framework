<?php

namespace Kasi\Tests\Database\Fixtures\Factories\Money;

use Kasi\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
        ];
    }
}
