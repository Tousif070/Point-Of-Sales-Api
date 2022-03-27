<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = Role::where('name', '=', 'super_admin')->first();

        if($role == null)
        {
            $role = new Role();

            $role->name = "super_admin";

            $role->description = "This user has the highest authority with all the permissions and no restrictions.";

            $role->save();
        }
    }
}
