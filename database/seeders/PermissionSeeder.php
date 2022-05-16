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

        $permission = Permission::where('name', '=', 'user.index-official')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.index-official";
            $permission->alias = "View User Officials";
            $permission->description = "To view the list of user officials";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.index-customer')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.index-customer";
            $permission->alias = "View Customers";
            $permission->description = "To view the list of customers";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.index-supplier')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.index-supplier";
            $permission->alias = "View Suppliers";
            $permission->description = "To view the list of suppliers";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.register-official')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.register-official";
            $permission->alias = "Register User Official";
            $permission->description = "To register a new user official for the company";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.register-customer')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.register-customer";
            $permission->alias = "Register Customer";
            $permission->description = "To register a new customer";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.register-supplier')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.register-supplier";
            $permission->alias = "Register Supplier";
            $permission->description = "To register a new supplier";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.assign-role')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.assign-role";
            $permission->alias = "Assign Role";
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
            $permission->alias = "View Roles";
            $permission->description = "To view the list of roles";
            $permission->permission_group = "Role";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'role.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "role.store";
            $permission->alias = "Create Role";
            $permission->description = "To store a new role";
            $permission->permission_group = "Role";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'role.assign-permission')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "role.assign-permission";
            $permission->alias = "Assign Permission";
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
            $permission->alias = "View Permissions";
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
            $permission->alias = "View Brands";
            $permission->description = "To view the list of brands";
            $permission->permission_group = "Brand";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'brand.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "brand.store";
            $permission->alias = "Create Brand";
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
            $permission->alias = "View Product Categories";
            $permission->description = "To view the list of product categories";
            $permission->permission_group = "Product Category";

            $permission->save();
        }


        // PERMISSION GROUP - PRODUCT MODEL

        $permission = Permission::where('name', '=', 'product-model.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product-model.index";
            $permission->alias = "View Product Models";
            $permission->description = "To view the list of product models";
            $permission->permission_group = "Product Model";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'product-model.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product-model.store";
            $permission->alias = "Create Product Model";
            $permission->description = "To store a new product model";
            $permission->permission_group = "Product Model";

            $permission->save();
        }


        // PERMISSION GROUP - PRODUCT

        $permission = Permission::where('name', '=', 'product.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product.index";
            $permission->alias = "View Products";
            $permission->description = "To view the list of products";
            $permission->permission_group = "Product";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'product.store-phone')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product.store-phone";
            $permission->alias = "Create Product (Phone)";
            $permission->description = "To store a new product (phone)";
            $permission->permission_group = "Product";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'product.store-charger')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product.store-charger";
            $permission->alias = "Create Product (Charger/Power Adapter)";
            $permission->description = "To store a new product (charger/power adapter)";
            $permission->permission_group = "Product";

            $permission->save();
        }


        // PERMISSION GROUP - PURCHASE

        $permission = Permission::where('name', '=', 'purchase.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "purchase.index";
            $permission->alias = "View Purchase List";
            $permission->description = "To view the list of purchase transactions";
            $permission->permission_group = "Purchase";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'purchase.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "purchase.store";
            $permission->alias = "Create Purchase";
            $permission->description = "To store a new purchase transaction";
            $permission->permission_group = "Purchase";

            $permission->save();
        }


        // PERMISSION GROUP - PURCHASE VARIATION

        $permission = Permission::where('name', '=', 'purchase-variation.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "purchase-variation.index";
            $permission->alias = "View Purchase Variations";
            $permission->description = "To view the list of purchase variations";
            $permission->permission_group = "Purchase Variation";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'purchase-variation.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "purchase-variation.store";
            $permission->alias = "Create Purchase Variation";
            $permission->description = "To store a new purchase variation";
            $permission->permission_group = "Purchase Variation";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'purchase-variation.avg-purchase-price')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "purchase-variation.avg-purchase-price";
            $permission->alias = "View Average Purchase Price";
            $permission->description = "To view average purchase prices of available products";
            $permission->permission_group = "Purchase Variation";

            $permission->save();
        }


        // PERMISSION GROUP - SALE

        $permission = Permission::where('name', '=', 'sale.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "sale.index";
            $permission->alias = "View Sales List/Invoices";
            $permission->description = "To view the list of sale transactions";
            $permission->permission_group = "Sale";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'sale.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "sale.store";
            $permission->alias = "Create Sale Invoice";
            $permission->description = "To store a new sale transaction";
            $permission->permission_group = "Sale";

            $permission->save();
        }


        // PERMISSION GROUP - SALE RETURN

        $permission = Permission::where('name', '=', 'sale-return.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "sale-return.index";
            $permission->alias = "View Sale Returns";
            $permission->description = "To view the list of sale return transactions";
            $permission->permission_group = "Sale Return";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'sale-return.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "sale-return.store";
            $permission->alias = "Create Sale Return";
            $permission->description = "To store a new sale return transaction";
            $permission->permission_group = "Sale Return";

            $permission->save();
        }


        // PERMISSION GROUP - EXPENSE

        $permission = Permission::where('name', '=', 'expense.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "expense.index";
            $permission->alias = "View Expenses";
            $permission->description = "To view the list of expense transactions";
            $permission->permission_group = "Expense";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'expense.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "expense.store";
            $permission->alias = "Create Expense";
            $permission->description = "To store a new expense transaction";
            $permission->permission_group = "Expense";

            $permission->save();
        }


    }
}
