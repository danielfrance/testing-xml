<?php

namespace Database\Factories;

use App\Models\TaxIDType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyInfo>
 */
class CompanyInfoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $alternates = [];

        if (fake()->boolean()) {
            $count = fake()->numberBetween(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $alternates[] = fake()->company();
            }
        } else {
            $alternates = null;
        }

        $tax_id_type = TaxIDType::all()->random();

        $state = null;
        if ($tax_id_type->name === 'EIN') {
            $state = DB::table('states')->inRandomOrder()->first();
        }

        $country = DB::table('countries')->inRandomOrder()->first();
        $formationState = DB::table('states')->inRandomOrder()->first();



        return [
            'get_fincen' => fake()->boolean(),
            'foreign_pooled_investment' => fake()->boolean(),
            'existing_reporting_company' => fake()->boolean(),
            'legal_name' => fake()->company(),
            'alternate_name' => $alternates ? json_encode($alternates) : null,
            'tax_id_type_id' => $tax_id_type->name,
            'tax_id_number' => fake()->unique()->numerify('#########'),
            'tax_id_country_id' => $country->id,
            'formation_type' => fake()->randomElement(['foreign', 'domestic']),
            'country_formation_id' => $country->id,
            'state_formation_id' => $formationState->id,
            'current_street_address' => fake()->streetAddress(),
            'current_city' => fake()->city(),
            'current_state_id' => $formationState->id,
            'zip' => fake()->postcode(),
            'current_country_id' => $country->id,
        ];
    }
}
