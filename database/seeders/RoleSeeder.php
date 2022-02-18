<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\Page;
use App\Models\PageOperation;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedOperations();
        $this->seedPages();
        $this->seedPageOperations();
    }

    protected function seedOperations()
    {
        $operations = $this->jsonFileOccurrences('operation');
        foreach ($operations as $key => $operation) {
            $existingOperation = $this->checkExistingElement(Operation::class, 'code', $operation->code);
            if (!$existingOperation) {
                Operation::create([
                    'code' => $operation->code,
                    'wording' => $operation->wording,
                    'description' => $operation->description,
                ]);
            }
        }
    }

    protected function seedPages()
    {
        $pages = $this->jsonFileOccurrences('page');
        foreach ($pages as $key => $page) {
            $existingPage = $this->checkExistingElement(Page::class, 'code', $page->code);
            if (!$existingPage) {
                Page::create([
                    'code' => $page->code,
                    'title' => $page->title,
                    'description' => $page->description,
                    'operations' => $page->operations,
                ]);
            }
        }
    }

    protected function seedPageOperations()
    {
        $pages = Page::all();
        foreach ($pages as $key => $page) {
            foreach ($page->operations as $key => $valueOperation) {
                $operation = $this->checkExistingElement(Operation::class, 'code', $valueOperation);
                if ($operation) {
                    $pageOperation = PageOperation::where('operation_id', $operation->id)->where('page_id', $page->id)->first();
                    if ($pageOperation) {
                        $pageOperation->code = "ROLE_" . $page->code . "_" . $operation->code;
                        $pageOperation->save();
                    } else {
                        $pageOperation = new PageOperation();
                        $pageOperation->code = "ROLE_" . $page->code . "_" . $operation->code;
                        $pageOperation->page_id = $page->id;
                        $pageOperation->operation_id = $operation->id;
                        $pageOperation->save();
                    }
                }
            }
        }
    }

    protected function checkExistingElement($model, $column, $value)
    {
        $existingElement = $model::where($column, $value)->first();
        return $existingElement;
    }

    protected function jsonFileOccurrences($jsonFileName)
    {
        $json_file = File::get('database/data/' . $jsonFileName . '.json');
        $arrayFromJson = json_decode($json_file);
        return $arrayFromJson;
    }
}
