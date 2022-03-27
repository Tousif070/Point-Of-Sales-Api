<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('username', '=', 'ishiful')->first();

        if($user == null)
        {
            $user = new User();

            $user->first_name = "Shiful";

            $user->last_name = "Islam";

            $user->username = "ishiful";

            $user->email = "ishiful@gmail.com";

            $user->password = Hash::make("11111111");

            $user->pin_number = 222222;

            $user->type = 1;

            $user->save();


            $role = Role::where('name', '=', 'super_admin')->first();

            $user->roles()->attach($role->id);
        }
    }
}
