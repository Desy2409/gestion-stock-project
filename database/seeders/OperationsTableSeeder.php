<?php

namespace Database\Seeders;

use App\Models\Operation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class OperationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_operation = File::get('database/data/operation.json');
        $operations = json_decode($json_operation);
        foreach ($operations as $key => $operation) {
            $existingOperation = Operation::where('code', $operation->code)->first();
            if (!$existingOperation) {
                Operation::create([
                    'code' => $operation->code,
                    'wording' => $operation->wording,
                    'description' => $operation->description,
                ]);
            }
        }
    }
}
