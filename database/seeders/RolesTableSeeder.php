<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\PageOperation;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_role = File::get('database/data/role.json');
        $roles = json_decode($json_role);
        foreach ($roles as $key => $role) {
           $existingRole = Role::where('code',$role->code)->first();
           if (!$existingRole) {
               Role::create([
                   'code'=>$role->code,
                   'wording'=>$role->wording,
                   'description'=>$role->description,
                   'description'=>$role->description,
                   'page_operation_id'=>$role->page_operation_id,
                   'operation_id'=>$role->operation_id,
               ]);
           }
        }
    }
}
