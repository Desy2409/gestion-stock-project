<?php

namespace Database\Seeders;

use App\Models\PageOperation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PageOperationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_page_operation = File::get('database/data/page_operation.json');
        $pageOperations = json_decode($json_page_operation);
        foreach ($pageOperations as $key => $pageOperation) {
            $existingPageOperation = PageOperation::where('code', $pageOperation->code)->first();
            if (!$existingPageOperation) {
                PageOperation::create([
                    'code' => $pageOperation->code,
                    'title' => $pageOperation->title,
                    'description' => $pageOperation->description,
                    'role' => $pageOperation->role,
                    'operation_id' => $pageOperation->operation_id,
                    'page_id' => $pageOperation->page_id,
                ]);
            }
        }
    }
}
