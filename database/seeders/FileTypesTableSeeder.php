<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FileTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('file_types')->insert([
            ['code' => "BC", 'wording' => "Bon de commande", 'description' => '', 'authorized_files' => "pdf,docx,xls", 'max_size' => "5120"],
            ['code' => "BA", 'wording' => "Bon d'achat", 'description' => '', 'authorized_files' => "pdf,docx,xls", 'max_size' => "5120"],
            ['code' => "BL", 'wording' => "Bon de livraison", 'description' => '', 'authorized_files' => "pdf,docx,xls", 'max_size' => "5120"],
            ['code' => "VT", 'wording' => "Vente", 'description' => '', 'authorized_files' => "pdf,docx,xls", 'max_size' => "5120"],
            ['code' => "CJ", 'wording' => "Certificat de jaugeage", 'description' => '', 'authorized_files' => "pdf,docx,xls", 'max_size' => "5120"],
        ]);
    }
}
