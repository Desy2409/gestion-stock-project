<?php

namespace Database\Seeders;

use App\Models\Extension;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ExtensionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_extension = File::get('database/data/extension.json');
        $extensions = json_decode($json_extension);

        foreach ($extensions as $key => $extension) {
            $existingExtension = Extension::where('code', $extension->code)->first();
            if(!$existingExtension){
                Extension:: create([
                    'code'=>$extension->code,
                    'extension'=>$extension->extension,
                ]);
            }
        }
    }
}
