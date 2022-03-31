<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // PERMISSION GROUP - USER

        $permission = Permission::where('name', '=', 'user.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.index";
            $permission->description = "To view the list of users";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.register')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.register";
            $permission->description = "To register a new user";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.assign-role')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.assign-role";
            $permission->description = "To assign a role to a user";
            $permission->permission_group = "User";

            $permission->save();
        }


        // PERMISSION GROUP - ROLE

        $permission = Permission::where('name', '=', 'role.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "role.index";
            $permission->description = "To view the list of roles";
            $permission->permission_group = "Role";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'role.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "role.store";
            $permission->description = "To store a new role";
            $permission->permission_group = "Role";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'role.assign-permission')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "role.assign-permission";
            $permission->description = "To assign a permission to a role";
            $permission->permission_group = "Role";

            $permission->save();
        }


        // PERMISSION GROUP - PERMISSION

        $permission = Permission::where('name', '=', 'permission.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "permission.index";
            $permission->description = "To view the list of permissions";
            $permission->permission_group = "Permission";

            $permission->save();
        }


        // PERMISSION GROUP - BRAND

        $permission = Permission::where('name', '=', 'brand.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "brand.index";
            $permission->description = "To view the list of brands";
            $permission->permission_group = "Brand";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'brand.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "brand.store";
            $permission->description = "To store a new brand";
            $permission->permission_group = "Brand";

            $permission->save();
        }


        // PERMISSION GROUP - PRODUCT CATEGORY

        $permission = Permission::where('name', '=', 'product-category.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product-category.index";
            $permission->description = "To view the list of product categories";
            $permission->permission_group = "Product Category";

            $permission->save();
        }


        // PERMISSION GROUP - PRODUCT

        $permission = Permission::where('name', '=', 'product.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product.index";
            $permission->description = "To view the list of products";
            $permission->permission_group = "Product";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'product.store-phone')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product.store-phone";
            $permission->description = "To store a new product (phone)";
            $permission->permission_group = "Product";

            $permission->save();
        }


    }
}
