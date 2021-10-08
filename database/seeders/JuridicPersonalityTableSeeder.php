<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JuridicPersonalityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('juridic_personalities')->insert([
            ['code' => Str::random(10), 'wording' => 'Personne physique', 'description' => ''],
            ['code' => Str::random(10), 'wording' => 'Personne morale', 'description' => ''],
        ]);
    }
}
