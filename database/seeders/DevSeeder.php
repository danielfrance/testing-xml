<?php

namespace Database\Seeders;

use App\Models\BeneficialOwner;
use App\Models\CompanyApplicant;
use App\Models\CompanyInfo;
use App\Models\Filing;
use App\Models\FilingType;
use App\Models\Role;
use App\Models\TaxIDType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DevSeeder extends Seeder
{

    public $states = [
        ['abbreviation' => 'AL', 'name' => 'Alabama'],
        ['abbreviation' => 'AK', 'name' => 'Alaska'],
        ['abbreviation' => 'AZ', 'name' => 'Arizona'],
        ['abbreviation' => 'AR', 'name' => 'Arkansas'],
        ['abbreviation' => 'AS', 'name' => 'American Samoa'],
        ['abbreviation' => 'CA', 'name' => 'California'],
        ['abbreviation' => 'CO', 'name' => 'Colorado'],
        ['abbreviation' => 'CT', 'name' => 'Connecticut'],
        ['abbreviation' => 'DE', 'name' => 'Delaware'],
        ['abbreviation' => 'DC', 'name' => 'District Of Columbia'],
        ['abbreviation' => 'FM', 'name' => 'Micronesia, Federated States of'],
        ['abbreviation' => 'FL', 'name' => 'Florida'],
        ['abbreviation' => 'GA', 'name' => 'Georgia'],
        ['abbreviation' => 'GU', 'name' => 'Guam'],
        ['abbreviation' => 'HI', 'name' => 'Hawaii'],
        ['abbreviation' => 'ID', 'name' => 'Idaho'],
        ['abbreviation' => 'IL', 'name' => 'Illinois'],
        ['abbreviation' => 'IN', 'name' => 'Indiana'],
        ['abbreviation' => 'IA', 'name' => 'Iowa'],
        ['abbreviation' => 'KS', 'name' => 'Kansas'],
        ['abbreviation' => 'KY', 'name' => 'Kentucky'],
        ['abbreviation' => 'LA', 'name' => 'Louisiana'],
        ['abbreviation' => 'ME', 'name' => 'Maine'],
        ['abbreviation' => 'MH', 'name' => 'Marshall Islands'],
        ['abbreviation' => 'MD', 'name' => 'Maryland'],
        ['abbreviation' => 'MA', 'name' => 'Massachusetts'],
        ['abbreviation' => 'MI', 'name' => 'Michigan'],
        ['abbreviation' => 'MN', 'name' => 'Minnesota'],
        ['abbreviation' => 'MS', 'name' => 'Mississippi'],
        ['abbreviation' => 'MO', 'name' => 'Missouri'],
        ['abbreviation' => 'MT', 'name' => 'Montana'],
        ['abbreviation' => 'NE', 'name' => 'Nebraska'],
        ['abbreviation' => 'NV', 'name' => 'Nevada'],
        ['abbreviation' => 'NH', 'name' => 'New Hampshire'],
        ['abbreviation' => 'NJ', 'name' => 'New Jersey'],
        ['abbreviation' => 'NM', 'name' => 'New Mexico'],
        ['abbreviation' => 'NY', 'name' => 'New York'],
        ['abbreviation' => 'NC', 'name' => 'North Carolina'],
        ['abbreviation' => 'ND', 'name' => 'North Dakota'],
        ['abbreviation' => 'MP', 'name' => 'Northern Mariana Islands'],
        ['abbreviation' => 'OH', 'name' => 'Ohio'],
        ['abbreviation' => 'OK', 'name' => 'Oklahoma'],
        ['abbreviation' => 'OR', 'name' => 'Oregon'],
        ['abbreviation' => 'PW', 'name' => 'Palau'],
        ['abbreviation' => 'PA', 'name' => 'Pennsylvania'],
        ['abbreviation' => 'PR', 'name' => 'Puerto Rico'],
        ['abbreviation' => 'RI', 'name' => 'Rhode Island'],
        ['abbreviation' => 'SC', 'name' => 'South Carolina'],
        ['abbreviation' => 'SD', 'name' => 'South Dakota'],
        ['abbreviation' => 'TN', 'name' => 'Tennessee'],
        ['abbreviation' => 'TX', 'name' => 'Texas'],
        ['abbreviation' => 'UT', 'name' => 'Utah'],
        ['abbreviation' => 'VT', 'name' => 'Vermont'],
        ['abbreviation' => 'VI', 'name' => 'Virgin Islands, U.S.'],
        ['abbreviation' => 'VA', 'name' => 'Virginia'],
        ['abbreviation' => 'WA', 'name' => 'Washington'],
        ['abbreviation' => 'WV', 'name' => 'West Virginia'],
        ['abbreviation' => 'WI', 'name' => 'Wisconsin'],
        ['abbreviation' => 'WY', 'name' => 'Wyoming'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $adminUser = $this->seedAdmins();
        $this->seedBMLAdmins();
        $this->seedFilingTypes();
        $this->seedTaxIDTypes();


        $this->seedCountries();
        $this->seedStates();
        $this->seedTribes();
        $this->seedFiles();

        // dd($adminUser->team);
        $this->seedFilings($adminUser->team);
    }



    public function seedAdmins(): object
    {
        $this->command->info('Seeding Admins 🧑‍🍳');
        $baseAdmin = env('BASE_ADMIN');
        $adminCheck = User::where('email', $baseAdmin)->first();

        $teamCheck = Team::where('name', 'Ball Morse Lowe')->first();

        if (!$teamCheck) {
            $this->command->info('Ball Morse Lowe team not found, creating Ball Morse Lowe team');
            $teamCheck = Team::create([
                'name' => 'Ball Morse Lowe',
                'display_name' => 'Ball Morse Lowe',
                'description' => 'Ball Morse Lowe',
                'slug' => 'ball-morse-lowe'
            ]);
        }

        if (!$adminCheck) {
            $this->command->info('Admin not found, creating Super Admin');
            $adminCheck = User::factory()->create([
                'name' => 'Super Admin',
                'email' => $baseAdmin,
                'email_verified_at' => now(),
                'password' => bcrypt(env('BASE_PASSWORD')),
                'team_id' => $teamCheck->id,
            ]);
        } else {
            $this->command->info('Admin found');
        }


        $adminRole = Role::where('name', 'superadministrator')->first();

        if (!$adminRole) {
            $this->command->info('Super Admin role not found, creating Super Admin role');
            $adminRole = Role::create([
                'name' => 'superadministrator',
                'display_name' => 'Super Administrator',
                'description' => 'Super Administrator',
            ]);
        } else {
            $this->command->info('Super Admin role found');
        }

        if (!$adminCheck->hasRole('superadministrator')) {

            $this->command->info('Assigning Super Admin role to Super Admin Role');

            $adminCheck->addRole($adminRole, $teamCheck);
        } else {
            $this->command->info('Super Admin already has Super Admin role');
        }

        return $adminCheck;
    }

    public function seedBMLAdmins(): void
    {
        $this->command->info('Seeding BML Admins 🧑‍🍳');
        $bmlAdmins = [
            [
                'email' => 'epatrick@bml.law',
                'name' => 'Eric Patrick',
            ],
            [
                'email' => 'zball@bml.law',
                'name' => 'Zach Ball',
            ],
            [
                'email' => 'plowe@bml.law',
                'name' => 'Parker Lowe',
            ]
        ];

        foreach ($bmlAdmins as $admin) {
            $adminCheck = User::where('email', $admin['email'])->first();

            $teamCheck = Team::where('name', 'Ball Morse Lowe')->first();

            if (!$adminCheck) {
                $this->command->info('Admin not found, creating Super Admin');
                $adminCheck = User::factory()->create([
                    'name' => $admin['name'],
                    'email' => $admin['email'],
                    'email_verified_at' => now(),
                    'password' => bcrypt(env('BASE_PASSWORD')),
                    'team_id' => $teamCheck->id,
                ]);
            } else {
                $this->command->info('Admin found');
            }

            $adminRole = Role::where('name', 'superadministrator')->first();

            if (!$adminCheck->hasRole('superadministrator')) {

                $this->command->info('Assigning Super Admin role to Super Admin Role');

                $adminCheck->addRole($adminRole, $teamCheck);
            } else {
                $this->command->info('Super Admin already has Super Admin role');
            }
        }
    }

    public function seedFilingTypes(): void
    {
        $this->command->info('Seeding Filing Types 📑');
        $filingTypes = [
            "Initial Report",
            "Correction",
            "Update",
            "Newly Exempt",
        ];

        foreach ($filingTypes as $filingType) {
            $filingTypeCheck = FilingType::where('name', $filingType)->first();

            if (!$filingTypeCheck) {
                $this->command->info('Filing Type not found, creating Filing Type');
                FilingType::create([
                    'name' => $filingType,
                    'value' => strtolower(str_replace(' ', '_', $filingType)),
                ]);
            }
        }
    }

    public function seedTaxIDTypes(): void
    {
        $this->command->info('Seeding Tax ID Types 🏢');
        $taxIDTypes = [
            "EIN",
            "SSN/TIN",
            "Foreign"
        ];

        foreach ($taxIDTypes as $taxIDType) {
            $taxIDTypeCheck = TaxIDType::where('name', $taxIDType)->first();

            if (!$taxIDTypeCheck) {
                $this->command->info('Tax ID Type not found, creating Tax ID Type');
                TaxIDType::create([
                    'name' => $taxIDType,
                    'value' => strtolower(str_replace(' ', '_', $taxIDType)),
                ]);
            }
        }
    }

    public function seedCountries(): void
    {
        $this->command->info('Seeding Countries 🌎');;

        $countries = json_decode(Storage::disk('local')->get('public/countries.json'));

        foreach ($countries as $country) {
            $countryCheck = DB::table('countries')->where('name', $country->name)->first();

            if (!$countryCheck) {
                $this->command->info($country->name . ' not found, creating Country');

                if (
                    $country->name == 'American Samoa' ||
                    $country->name == 'Guam' ||
                    $country->name == 'Northern Mariana Islands' ||
                    $country->name == 'Puerto Rico' ||
                    $country->name == 'Palau' ||
                    $country->name == 'Micronesia, Federated States of' ||
                    $country->name == 'Virgin Islands, U.S.' ||
                    $country == 'Marshall Islands'
                ) {
                    DB::table('countries')->insert(['name' => $country->name,
                        'us_territory' => true, 'created_at' => now(),
                        'updated_at' => now(),
                        'iso' => $country->code
                    ]);;
                } elseif ($country->name == 'United States Minor Outlying Islands') {
                    // skip
                    continue;
                } else {
                    DB::table('countries')->insert(['name' => $country->name,
                        'iso' => $country->code,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function seedStates(): void
    {
        $this->command->info('Seeding States 🇺🇸');

        foreach ($this->states as $state) {
            $stateCheck = DB::table('states')->where('name', $state['name'])->first();

            if (!$stateCheck) {
                $this->command->info($state['name'] . ' not found, creating State');
                DB::table('states')->insert([
                    'name' => $state['name'],
                    'abbreviation' => $state['abbreviation'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function seedTribes(): void
    {
        $this->command->info('Seeding Tribes');

        $count = DB::table('tribes')->count();

        if ($count > 0) {
            $this->command->info('Tribes already seeded');
            return;
        }

        $tribes = Http::get('https://cdxapi.epa.gov/oms-tribes-rest-services/api/v1/tribes')->json();

        // add "other" tribe to tribes array
        $tribes[] = ['currentName' => 'Other'];

        foreach ($tribes as $tribe) {
            $tribeCheck = DB::table('tribes')->where('name', $tribe['currentName'])->first();

            if (!$tribeCheck) {
                $this->command->info($tribe['currentName'] . ' not found, creating Tribe');
                DB::table('tribes')->insert([
                    'name' => $tribe['currentName'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function seedFiles(): void
    {
        $this->command->info('Seeding Files 📃');

        $count = DB::table('files')->count();

        if ($count > 0) {
            $this->command->info('Files already seeded');
            return;
        }

        DB::table('files')->insert([
            'team_id' => 1,
            'name' => 'placehold-600x400.png',
            'path' => 'https://placehold.co/600x400?text=Hello+World',
            'size' => 1000,
            'type' => 'image/png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function seedFilings($team): void
    {
        $count = Filing::count();

        if ($count > 0) {
            $this->command->info('Filings already seeded');
            return;
        }

        $this->command->info('Seeding Filings 📦 ');

        $usa = DB::table('countries')->where('name', 'United States')->first();

        DB::transaction(function () use ($team, $usa) {
            $filings = Filing::factory()->count(10)->create([
                'team_id' => $team->id,
            ]);

            $this->command->info('Seeding Company Info, Company Applicants, and Beneficial Owners');
            $filings->each(function ($filing) use ($usa) {
                CompanyInfo::factory()->count(1)->create([
                    'team_id' => $filing->team_id,
                    'filing_id' => $filing->id,
                    'tax_id_type_id' => TaxIDType::all()->random()->id,
                    'tax_id_country_id' => rand(1, 200),
                ]);

                $applicants = CompanyApplicant::factory()->count(1)->create([
                    'team_id' => $filing->team_id,
                    'country_id' => $usa->id,
                    'id_document_country' => $usa->id,
                ]);

                $applicants->each(function ($applicant) use ($filing) {
                    $filing->companyApplicants()->attach($applicant, ['created_at' => now(), 'updated_at' => now()]);
                });

                $owners = BeneficialOwner::factory()->count(2)->create([
                    'team_id' => $filing->team_id,
                    'country_id' => $usa->id,
                    'id_document_country' => $usa->id,
                ]);

                $owners->each(function ($owner) use ($filing) {
                    $filing->beneficialOwners()->attach($owner, ['created_at' => now(), 'updated_at' => now()]);
                });
            });
        });
    }
}
