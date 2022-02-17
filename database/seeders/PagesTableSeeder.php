<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\Page;
use App\Models\PageOperation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_page = File::get('database/data/page.json');
        $pages = json_decode($json_page);
        foreach ($pages as $key => $page) {
            $existingPage = Page::where('code', $page->code)->first();
            if (!$existingPage) {
                Page::create([
                    'code' => $page->code,
                    'title' => $page->title,
                    'description' => $page->description,
                    'operations' => $page->operations,
                ]);

                $page = Page::latest()->first();;

                foreach ($page->operations as $key => $valueOperation) {
                    $operation = Operation::where('code', '=', $valueOperation)->first();
                    if ($operation) {
                        $pageOperation = PageOperation::where('operation_id',$operation->id)->where('page_id',$page->id)->first();
                        if ($pageOperation) {
                            $pageOperation->code = "ROLE_".$page->code."_".$operation->code;
                            $pageOperation->save();
                        }else{
                            $pageOperation = new PageOperation();
                            $pageOperation->code = "ROLE_".$page->code."_".$operation->code;
                            $pageOperation->page_id = $page->id;
                            $pageOperation->operation_id = $operation->id;
                            $pageOperation->save() ;
                        }
                    }
                }
            }
        }
    }
}
