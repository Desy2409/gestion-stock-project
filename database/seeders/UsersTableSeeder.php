<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_user = File::get('database/data/user.json');
        $users = json_decode($json_user);
        foreach ($users as $key => $user) {
            $existingUser = User::where('email', $user->email)->first();
            if (!$existingUser) {
                User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => Hash::make($user->password),
                    'roles' => $user->roles
                ]);
            }
        }
    }
}
