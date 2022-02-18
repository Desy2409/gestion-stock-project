<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Currency::query()->truncate();
        $json_currency = File::get('database/data/currency.json');
        $currencies = json_decode($json_currency);
        foreach ($currencies as $key => $currency) {
            $existingOperation = Currency::where('code', $currency->code)->first();
            if (!$existingOperation) {
                Currency::create([
                    'code' => $currency->code,
                    'wording' => $currency->wording,
                    'description' => $currency->description,
                ]);
            }
        }
    }
}
