<?php

namespace Database\Seeders;

use App\Models\Page;
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
            }
        }
    }
}
