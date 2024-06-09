<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyApplicant>
 */
class CompanyApplicantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $finCenId = fake()->boolean() ? fake()->unique()->numberBetween(130000000000, 999999999999) : null;

        $state = DB::table('states')->inRandomOrder()->first();
        $bool = fake()->boolean();


        return [
            'fincen_id' => $finCenId,
            'last_name' => fake()->lastName,
            'first_name' => fake()->firstName,
            'middle_name' => fake()->firstNameMale,
            'suffix' => fake()->suffix,
            'dob' => fake()->date('Y-m-d', '-18 years'),
            'address_type' => fake()->randomElement(['residential', 'business']),
            'address' => fake()->streetAddress,
            'city' => fake()->city,
            'state_id' => $state->id,
            'zip' => fake()->postcode,
            'id_type' => fake()->randomElement([
                'state_tribe_id', 'foreign_passport',
                'us_passport', 'drivers_license'
            ]),
            'id_number' => fake()->unique()->numerify('##########'),
            'id_document_state' => $state->id,
            'id_document_tribe' => 1,
            'email' => $bool ? fake()->unique()->safeEmail : null,
            'phone' => $bool ? fake()->phoneNumber : null,
            'info_verified_at' => $bool ? fake()->dateTime() : null,
        
        ];
    }
}
