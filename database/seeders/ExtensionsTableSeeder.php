<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExtensionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('extensions')->insert([
            ['code'=>'.jpg','extension'=>'JPG'],
            ['code'=>'.jpeg','extension'=>'JPEG'],
            ['code'=>'.bmp','extension'=>'BMP'],
            ['code'=>'.webp','extension'=>'WEBP'],
            ['code'=>'.png','extension'=>'PNG'],
            ['code'=>'.gif','extension'=>'GIF'],
            ['code'=>'.svg','extension'=>'SVG'],
            ['code'=>'.avi','extension'=>'AVI'],
            ['code'=>'.mpeg','extension'=>'MPEG'],
            ['code'=>'.mp4','extension'=>'MP4'],
            ['code'=>'.mkv','extension'=>'MKV'],
            ['code'=>'.flv','extension'=>'FLV'],
            ['code'=>'.mov','extension'=>'MOV'],
            ['code'=>'.wmv','extension'=>'WMV'],
            ['code'=>'.webm','extension'=>'WEBM'],
            ['code'=>'.pdf','extension'=>'PDF'],
        ]);
    }
}
