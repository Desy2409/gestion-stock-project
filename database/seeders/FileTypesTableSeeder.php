<?php

namespace Database\Seeders;

use App\Models\FileType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class FileTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_file_type = File::get("database/data/file_type.json");
        $fileTypes = json_decode($json_file_type);
        foreach ($fileTypes as $key => $fileType) {
            $existingFileType = FileType::where('code', $fileType->code)->first();
            if (!$existingFileType) {
                FileType::create([
                    'code' => $fileType->code,
                    'wording' => $fileType->wording,
                    'description' => $fileType->description,
                    'authorized_files' => $fileType->authorized_files,
                    'max_size' => $fileType->max_size,
                ]);
            }
        }
    }
}
