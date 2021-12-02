<?php

declare(strict_types=1);

namespace Tests\integration\eloquent\fixtures\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\integration\eloquent\fixtures\Models\Country;

class CityFactory extends Factory
{
    
    public function definition()
    {
        return [
            'name' => $this->faker->city(),
            'country_id' => Country::factory(),
            'population' => $this->faker->numberBetween(100000, 1000000),
        ];
    }
    
}