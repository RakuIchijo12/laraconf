<?php

namespace Database\Factories;

use App\Models\Talk;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Speaker;

class SpeakerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Speaker::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Fetch random qualifications
        $qualifications = $this->faker->randomElements(
            array_keys(Speaker::QUALIFICATIONS),
            $this->faker->numberBetween(0, 10) // Number of qualifications
        );

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'bio' => $this->faker->text(),
            'qualifications' => $qualifications, // Assign the generated qualifications
            'twitter_handle' => $this->faker->userName(),
        ];
    }

    /**
     * Add associated talks to the speaker.
     */
    public function withTalks(int $count = 1): self
    {
        return $this->has(Talk::factory()->count($count), 'talks');
    }
}
