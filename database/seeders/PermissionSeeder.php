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

        $permission = Permission::where('name', '=', 'user.update-official')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.update-official";
            $permission->alias = "Update User Official";
            $permission->description = "To update information of a user official";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.update-customer')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.update-customer";
            $permission->alias = "Update Customer";
            $permission->description = "To update information of a customer";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.update-supplier')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.update-supplier";
            $permission->alias = "Update Supplier";
            $permission->description = "To update information of a supplier";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.cua')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.cua";
            $permission->alias = "Customer User Association (CUA)";
            $permission->description = "To assign a customer under a user";
            $permission->permission_group = "User";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'user.cua-enable')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "user.cua-enable";
            $permission->alias = "Enable CUA";
            $permission->description = "If enabled then one can deal with only his/her assigned customers. Otherwise can deal with all customers";
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

        $permission = Permission::where('name', '=', 'purchase.lock')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "purchase.lock";
            $permission->alias = "Lock/Unlock Purchase";
            $permission->description = "To lock/unlock a purchase for adding products";
            $permission->permission_group = "Purchase";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'purchase.verification')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "purchase.verification";
            $permission->alias = "Purchase Verification";
            $permission->description = "To verify purchase transactions";
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


        // PERMISSION GROUP - PRODUCT LOCATION

        $permission = Permission::where('name', '=', 'product-location.on-hand')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product-location.on-hand";
            $permission->alias = "Products On Hand";
            $permission->description = "To view the products on hand for different users";
            $permission->permission_group = "Product Location";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'product-location.transfer')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product-location.transfer";
            $permission->alias = "Product Transfer";
            $permission->description = "To transfer product variations from one user to another user";
            $permission->permission_group = "Product Location";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'product-location.history')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "product-location.history";
            $permission->alias = "Product Transfer History";
            $permission->description = "To view product transfer history";
            $permission->permission_group = "Product Location";

            $permission->save();
        }


        // PERMISSION GROUP - SKU TRANSFER

        $permission = Permission::where('name', '=', 'sku-transfer.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "sku-transfer.store";
            $permission->alias = "SKU Transfer";
            $permission->description = "To transfer sku of a variation from one to another";
            $permission->permission_group = "SKU Transfer";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'sku-transfer.history')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "sku-transfer.history";
            $permission->alias = "SKU Transfer History";
            $permission->description = "To view SKU transfer history";
            $permission->permission_group = "SKU Transfer";

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

        $permission = Permission::where('name', '=', 'sale.verification')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "sale.verification";
            $permission->alias = "Sale Verification";
            $permission->description = "To verify sale transactions";
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

        $permission = Permission::where('name', '=', 'sale-return.verification')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "sale-return.verification";
            $permission->alias = "Sale Return Verification";
            $permission->description = "To verify sale return transactions";
            $permission->permission_group = "Sale Return";

            $permission->save();
        }


        // PERMISSION GROUP - EXPENSE CATEGORY

        $permission = Permission::where('name', '=', 'expense-category.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "expense-category.index";
            $permission->alias = "View Expense Categories";
            $permission->description = "To view the list of expense categories";
            $permission->permission_group = "Expense Category";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'expense-category.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "expense-category.store";
            $permission->alias = "Create Expense Category";
            $permission->description = "To store a new expense category";
            $permission->permission_group = "Expense Category";

            $permission->save();
        }


        // PERMISSION GROUP - EXPENSE REFERENCE

        $permission = Permission::where('name', '=', 'expense-reference.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "expense-reference.index";
            $permission->alias = "View Expense References";
            $permission->description = "To view the list of expense references";
            $permission->permission_group = "Expense Reference";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'expense-reference.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "expense-reference.store";
            $permission->alias = "Create Expense Reference";
            $permission->description = "To store a new expense reference";
            $permission->permission_group = "Expense Reference";

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

        $permission = Permission::where('name', '=', 'expense.summary')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "expense.summary";
            $permission->alias = "View Expense Summary";
            $permission->description = "To view total expense by expense categories";
            $permission->permission_group = "Expense";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'expense.verification')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "expense.verification";
            $permission->alias = "Expense Verification";
            $permission->description = "To verify expense transactions";
            $permission->permission_group = "Expense";

            $permission->save();
        }


        // PERMISSION GROUP - PAYMENT METHOD

        $permission = Permission::where('name', '=', 'payment-method.index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "payment-method.index";
            $permission->alias = "View Payment Methods";
            $permission->description = "To view the list of payment methods";
            $permission->permission_group = "Payment Method";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'payment-method.store')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "payment-method.store";
            $permission->alias = "Create Payment Method";
            $permission->description = "To store a new payment method";
            $permission->permission_group = "Payment Method";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'payment-method.report')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "payment-method.report";
            $permission->alias = "Payment Method Report";
            $permission->description = "To view payment method report";
            $permission->permission_group = "Payment Method";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'payment-method.payments')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "payment-method.payments";
            $permission->alias = "View Payments";
            $permission->description = "To view payments by payment method";
            $permission->permission_group = "Payment Method";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'payment.verification')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "payment.verification";
            $permission->alias = "Payment Verification";
            $permission->description = "To verify payments";
            $permission->permission_group = "Payment Method";

            $permission->save();
        }


        // PERMISSION GROUP - MONEY TRANSACTION

        $permission = Permission::where('name', '=', 'money-transaction.sale-payment')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "money-transaction.sale-payment";
            $permission->alias = "Make Sale Payment";
            $permission->description = "To make sale payment for sale invoice";
            $permission->permission_group = "Money Transaction";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'money-transaction.purchase-payment')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "money-transaction.purchase-payment";
            $permission->alias = "Make Purchase Payment";
            $permission->description = "To make purchase payment for purchase invoice";
            $permission->permission_group = "Money Transaction";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'money-transaction.expense-payment')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "money-transaction.expense-payment";
            $permission->alias = "Make Expense Payment";
            $permission->description = "To make expense payment for an expense";
            $permission->permission_group = "Money Transaction";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'money-transaction.customer-credit')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "money-transaction.customer-credit";
            $permission->alias = "Add Customer Credit";
            $permission->description = "To add credit for a customer";
            $permission->permission_group = "Money Transaction";

            $permission->save();
        }


        // PERMISSION GROUP - REPORT

        $permission = Permission::where('name', '=', 'report.cas-index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.cas-index";
            $permission->alias = "Customer Account Statement (CAS)";
            $permission->description = "To view customer account statement";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.spr-index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.spr-index";
            $permission->alias = "Sale Payment Report (SPR)";
            $permission->description = "To view all the sale payments";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.ppr-index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.ppr-index";
            $permission->alias = "Purchase Payment Report (PPR)";
            $permission->description = "To view all the purchase payments";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.epr-index')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.epr-index";
            $permission->alias = "Expense Payment Report (EPR)";
            $permission->description = "To view all the expense payments";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.pbsi')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.pbsi";
            $permission->alias = "Profit by Sale Invoice";
            $permission->description = "To view gross profit from sale invoices";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.pbc')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.pbc";
            $permission->alias = "Profit by Customer";
            $permission->description = "To view gross profit from customers";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.pbd')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.pbd";
            $permission->alias = "Profit by Date";
            $permission->description = "To view gross profit by date";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.pbp')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.pbp";
            $permission->alias = "Profit by Products";
            $permission->description = "To view gross profit by products";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.pbpm')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.pbpm";
            $permission->alias = "Profit by Product Models";
            $permission->description = "To view gross profit by product models";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.pbpc')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.pbpc";
            $permission->alias = "Profit by Product Categories";
            $permission->description = "To view gross profit by product categories";
            $permission->permission_group = "Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'report.verification')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "report.verification";
            $permission->alias = "Verification Report";
            $permission->description = "To view verification report";
            $permission->permission_group = "Report";

            $permission->save();
        }


        // PERMISSION GROUP - RECORD

        $permission = Permission::where('name', '=', 'record.transactions')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "record.transactions";
            $permission->alias = "Transaction Records";
            $permission->description = "To view records of transactions";
            $permission->permission_group = "Record";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'record.money')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "record.money";
            $permission->alias = "Money Records";
            $permission->description = "To view records of cash in & cash out";
            $permission->permission_group = "Record";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'record.verification')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "record.verification";
            $permission->alias = "Verification Records";
            $permission->description = "To view records of verifications";
            $permission->permission_group = "Record";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'record.user-log')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "record.user-log";
            $permission->alias = "User Log";
            $permission->description = "To view login/logout records of users";
            $permission->permission_group = "Record";

            $permission->save();
        }


        // PERMISSION GROUP - POOL

        $permission = Permission::where('name', '=', 'pool.add')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "pool.add";
            $permission->alias = "Add Money";
            $permission->description = "To add money in pool";
            $permission->permission_group = "Pool";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'pool.withdraw')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "pool.withdraw";
            $permission->alias = "Withdraw Money";
            $permission->description = "To withdraw money from pool";
            $permission->permission_group = "Pool";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'pool.update')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "pool.update";
            $permission->alias = "Update Pool";
            $permission->description = "To update pool";
            $permission->permission_group = "Pool";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'pool.delete')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "pool.delete";
            $permission->alias = "Delete Pool";
            $permission->description = "To delete pool";
            $permission->permission_group = "Pool";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'pool.history')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "pool.history";
            $permission->alias = "Pool History";
            $permission->description = "To view pool history";
            $permission->permission_group = "Pool";

            $permission->save();
        }


        // PERMISSION GROUP - SEARCH

        $permission = Permission::where('name', '=', 'search.imei')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "search.imei";
            $permission->alias = "Search IMEI";
            $permission->description = "To search an IMEI & view its details";
            $permission->permission_group = "Search";

            $permission->save();
        }


        // PERMISSION GROUP - SALESMAN REPORT

        $permission = Permission::where('name', '=', 'srfp')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "srfp";
            $permission->alias = "First Person View";
            $permission->description = "To view one's own salesman reports";
            $permission->permission_group = "Salesman Report";

            $permission->save();
        }

        $permission = Permission::where('name', '=', 'srtp')->first();

        if($permission == null)
        {
            $permission = new Permission();

            $permission->name = "srtp";
            $permission->alias = "Third Person View";
            $permission->description = "To view salesman reports of other salesmen";
            $permission->permission_group = "Salesman Report";

            $permission->save();
        }
        

    }
}
