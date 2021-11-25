<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_country = File::get('database/data/country.json');
        $countries = json_decode($json_country);
        foreach ($countries as $key => $country) {
            $existingCountry = Country::where('name_fr', $country->name_fr)->first();
            if (!$existingCountry) {
                Country::create([
                    'code' => $country->code,
                    'alpha2' => $country->alpha2,
                    'alpha3' => $country->alpha3,
                    'name_en' => $country->name_en,
                    'name_fr' => $country->name_fr,
                    'indicative' => $country->indicative,
                ]);
            }
        }
    }
}
