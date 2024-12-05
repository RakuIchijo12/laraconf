<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Conference;
use App\Models\Venue;
use App\Enums\Region;

class ConferenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Conference::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $startDate = now()->addMonths(9);
        $endDate = now()->addMonths(9)->addDays(2);
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'start_date' => $this->faker->dateTime(),
            'end_date' => $this->faker->dateTime(),
            'status' => $this->faker->randomElement([
                    'draft', 
                    'published',
                    'archived', 
            ]),
            'region' => $this->faker->randomElement(Region::class),
            'venue_id' => null,
        ];
    }
}
