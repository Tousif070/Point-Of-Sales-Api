<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['namespace' => 'App\Http\Controllers'], function() {

    Route::post('login', 'LoginController@login');

    // THE FOLLOWING ROUTE IS TEMPORARY. WILL BE REMOVED LATER
    Route::get('getpdf/{sale_transaction_id}', 'SaleTransactionController@downloadSaleInvoice');

    Route::group(['middleware' => ['auth:sanctum', 'TokenTimer']], function() {

        Route::post('logout', 'LoginController@logout');


        Route::group(['prefix' => 'user'], function() {

            Route::get('index-official', 'UserController@indexOfficial');

            Route::get('index-customer', 'UserController@indexCustomer');

            Route::get('index-supplier', 'UserController@indexSupplier');

            
            Route::post('register-official', 'UserController@registerOfficial');

            Route::post('register-customer', 'UserController@registerCustomer');

            Route::post('register-supplier', 'UserController@registerSupplier');


            Route::get('show-official/{user_official_id}', 'UserController@showOfficial');

            Route::get('show-customer/{customer_id}', 'UserController@showCustomer');

            Route::get('show-supplier/{supplier_id}', 'UserController@showSupplier');


            Route::post('update-official/{user_official_id}', 'UserController@updateOfficial');

            Route::post('update-customer/{customer_id}', 'UserController@updateCustomer');

            Route::post('update-supplier/{supplier_id}', 'UserController@updateSupplier');


            Route::get('customer/get-shipping-addresses/{customer_id}', 'UserController@getShippingAddresses');

            Route::post('customer/store-shipping-address/{customer_id}', 'UserController@storeShippingAddress');

            Route::post('customer/edit-shipping-address/{customer_id}', 'UserController@editShippingAddress');

            Route::post('customer/delete-shipping-address/{customer_id}', 'UserController@deleteShippingAddress');


            Route::get('cua-index', 'UserController@cuaIndex');

            Route::get('cua-assign-view', 'UserController@cuaAssignView');

            Route::post('cua-assign', 'UserController@cuaAssign');


            Route::get('customer/get-customer-credit-history/{customer_id}', 'UserController@getCustomerCreditHistory');

            Route::post('customer/store-customer-credit/{customer_id}', 'UserController@storeCustomerCredit');

            Route::post('customer/withdraw-customer-credit/{customer_id}', 'UserController@withdrawCustomerCredit');
            

            Route::get('assign-role-view/{user_id}', 'UserController@assignRoleView');

            Route::post('assign-role', 'UserController@assignRole');

            Route::get('has-permission', 'UserController@hasPermission');

            Route::get('get-permissions', 'UserController@getPermissions');

        });


        Route::group(['prefix' => 'role'], function() {

            Route::get('index', 'RoleController@index');
            
            Route::post('store', 'RoleController@store');

            Route::get('assign-permission-view/{role_id}', 'RoleController@assignPermissionView');

            Route::post('assign-permission', 'RoleController@assignPermission');

        });


        Route::group(['prefix' => 'permission'], function() {

            Route::get('index', 'PermissionController@index');
            
            Route::post('store', 'PermissionController@store'); // THIS ROUTE WILL NOT BE USED BY ANY CLIENT. BUT IT'S STILL KEPT JUST IN CASE

        });


        Route::group(['prefix' => 'brand'], function() {

            Route::get('index', 'BrandController@index');
            
            Route::post('store', 'BrandController@store');

        });
        

        Route::group(['prefix' => 'product-category'], function() {

            Route::get('index', 'ProductCategoryController@index');
            
            Route::post('store', 'ProductCategoryController@store'); // THIS ROUTE WILL NOT BE USED BY ANY CLIENT. BUT IT'S STILL KEPT JUST IN CASE

        });


        Route::group(['prefix' => 'product-model'], function() {

            Route::get('index', 'ProductModelController@index');
            
            Route::post('store', 'ProductModelController@store');

        });


        Route::group(['prefix' => 'product'], function() {

            Route::get('index', 'ProductController@index');

            Route::get('store-product-view', 'ProductController@storeProductView');
            
            Route::post('store-phone', 'ProductController@storePhone');

            Route::post('store-charger', 'ProductController@storeCharger');

            Route::get('get-purchase-variations/{product_id}', 'ProductController@getPurchaseVariations');

        });


        Route::group(['prefix' => 'purchase'], function() {

            Route::get('index', 'PurchaseTransactionController@index');

            Route::get('store-purchase-view', 'PurchaseTransactionController@storePurchaseView');
            
            Route::post('store', 'PurchaseTransactionController@store');

            Route::get('toggle-lock/{purchase_transaction_id}', 'PurchaseTransactionController@toggleLock');

            Route::get('get-purchase-variations/{purchase_transaction_id}', 'PurchaseTransactionController@getPurchaseVariations');

            Route::get('get-purchase-invoice/{purchase_transaction_id}', 'PurchaseTransactionController@getPurchaseInvoice');

            Route::post('verification', 'PurchaseTransactionController@verification');

        });


        Route::group(['prefix' => 'purchase-variation'], function() {

            Route::get('index', 'PurchaseVariationController@index');

            Route::get('store-purchase-variation-view', 'PurchaseVariationController@storePurchaseVariationView');
            
            Route::post('store', 'PurchaseVariationController@store');

            Route::get('get-product-category-type/{product_id}', 'PurchaseVariationController@getProductCategoryType');

            Route::get('get-average-purchase-price', 'PurchaseVariationController@getAveragePurchasePrice');

        });


        Route::group(['prefix' => 'product-location'], function() {

            Route::get('on-hand', 'ProductLocationController@onHand');

            Route::get('on-hand-variations/{user_id}', 'ProductLocationController@onHandVariations');

            Route::get('transfer-view', 'ProductLocationController@transferView');

            Route::post('transfer', 'ProductLocationController@transfer');

            Route::get('transfer-history', 'ProductLocationController@transferHistory');

            Route::get('transfer-history-details/{batch_no}', 'ProductLocationController@transferHistoryDetails');

        });


        Route::group(['prefix' => 'sku-transfer'], function() {

            Route::get('view', 'SkuTransferController@view');

            Route::get('store-view', 'SkuTransferController@storeView');

            Route::post('store', 'SkuTransferController@store');

            Route::get('history', 'SkuTransferController@history');

            Route::get('history-details/{batch_no}', 'SkuTransferController@historyDetails');

        });


        Route::group(['prefix' => 'sale'], function() {

            Route::get('index', 'SaleTransactionController@index');

            Route::get('store-sale-view', 'SaleTransactionController@storeSaleView');

            Route::post('store', 'SaleTransactionController@store');

            Route::get('imei-scan', 'SaleTransactionController@imeiScan');

            Route::get('imei-scan-alternative', 'SaleTransactionController@imeiScanAlternative');

            Route::get('purchase-variations-for-sale', 'SaleTransactionController@purchaseVariationsForSale');

            Route::get('get-sale-variations/{sale_transaction_id}', 'SaleTransactionController@getSaleVariations');

            Route::get('get-sale-invoice/{sale_transaction_id}', 'SaleTransactionController@getSaleInvoice');

            Route::get('downlaod-sale-invoice/{sale_transaction_id}', 'SaleTransactionController@downloadSaleInvoice');

            Route::get('email-sale-invoice/{sale_transaction_id}', 'SaleTransactionController@emailSaleInvoice');

            Route::post('verification', 'SaleTransactionController@verification');

        });


        Route::group(['prefix' => 'sale-return'], function() {

            Route::get('index', 'SaleReturnTransactionController@index');

            Route::get('store-sale-return-view/{sale_transaction_id}', 'SaleReturnTransactionController@storeSaleReturnView');

            Route::post('store', 'SaleReturnTransactionController@store');

            Route::get('get-sale-return-variations/{sale_return_transaction_id}', 'SaleReturnTransactionController@getSaleReturnVariations');

            Route::get('get-sale-return-invoice/{sale_return_transaction_id}', 'SaleReturnTransactionController@getSaleReturnInvoice');

            Route::post('verification', 'SaleReturnTransactionController@verification');

        });


        Route::group(['prefix' => 'expense-category'], function() {

            Route::get('index', 'ExpenseCategoryController@index');
            
            Route::post('store', 'ExpenseCategoryController@store');

        });


        Route::group(['prefix' => 'expense-reference'], function() {

            Route::get('index', 'ExpenseReferenceController@index');
            
            Route::post('store', 'ExpenseReferenceController@store');

        });


        Route::group(['prefix' => 'expense'], function() {

            Route::get('index', 'ExpenseTransactionController@index');

            Route::get('store-expense-view', 'ExpenseTransactionController@storeExpenseView');
            
            Route::post('store', 'ExpenseTransactionController@store');

            Route::get('summary', 'ExpenseTransactionController@summary');

            Route::post('verification', 'ExpenseTransactionController@verification');

        });


        Route::group(['prefix' => 'payment-method'], function() {

            Route::get('index', 'PaymentMethodController@index');
            
            Route::post('store', 'PaymentMethodController@store');

            Route::get('report', 'PaymentMethodController@report');

            Route::get('payments/{payment_method_id}', 'PaymentMethodController@payments');

        });


        Route::group(['prefix' => 'money-transaction'], function() {

            Route::get('sale-payment-view', 'MoneyTransactionController@salePaymentView');

            Route::get('purchase-payment-view', 'MoneyTransactionController@purchasePaymentView');

            Route::get('expense-payment-view', 'MoneyTransactionController@expensePaymentView');

            Route::get('customer-dropdown', 'MoneyTransactionController@customerDropdown');

            Route::get('collective-sale-payment-view', 'MoneyTransactionController@collectiveSalePaymentView');

            Route::get('make-payment-view', 'MoneyTransactionController@makePaymentView');

            Route::get('add-customer-credit-view', 'MoneyTransactionController@addCustomerCreditView');

            Route::get('customer-credit-available-credit', 'MoneyTransactionController@customerCreditAvailableCredit');

            Route::post('store', 'MoneyTransactionController@store');

            Route::post('payment-verification', 'MoneyTransactionController@paymentVerification');

        });


        Route::group(['prefix' => 'record'], function() {

            Route::get('transactions', 'RecordController@transactions');

            Route::get('cash-in', 'RecordController@cashIn');

            Route::get('cash-out', 'RecordController@cashOut');

            Route::get('verification-record', 'RecordController@verificationRecord');

            Route::get('user-log', 'RecordController@userLog');

        });


        Route::group(['prefix' => 'report'], function() {

            Route::get('cas-index-view', 'ReportController@casIndexView');

            Route::get('cas-index', 'ReportController@casIndex');

            Route::get('spr-index', 'ReportController@sprIndex');

            Route::get('ppr-index', 'ReportController@pprIndex');

            Route::get('epr-index', 'ReportController@eprIndex');


            Route::get('profit-by-sale-invoice', 'ReportController@profitBySaleInvoice');

            Route::get('profit-by-customer-view', 'ReportController@profitByCustomerView');

            Route::get('profit-by-customer', 'ReportController@profitByCustomer');

            Route::get('profit-by-date', 'ReportController@profitByDate');

            Route::get('profit-by-products', 'ReportController@profitByProducts');

            Route::get('profit-by-product-models', 'ReportController@profitByProductModels');

            Route::get('profit-by-product-categories', 'ReportController@profitByProductCategories');
            

            Route::get('verification-report', 'ReportController@verificationReport');

        });


        Route::group(['prefix' => 'pool'], function() {

            Route::get('history', 'PoolController@history');

            Route::post('add', 'PoolController@add');

            Route::post('withdraw', 'PoolController@withdraw');

            Route::post('update/{pool_id}', 'PoolController@update');

            Route::delete('delete/{pool_id}', 'PoolController@delete');

        });
        

        Route::group(['prefix' => 'search'], function() {

            Route::get('imei', 'SearchController@imei');

        });
        

        Route::group(['prefix' => 'salesman-report'], function() {

            Route::get('commission-by-sale-invoice-fp', 'SalesmanReportController@commissionBySaleInvoiceFp');

            Route::get('commission-by-customer-fp', 'SalesmanReportController@commissionByCustomerFp');

            Route::get('sales-due-fp', 'SalesmanReportController@salesDueFp');

            Route::get('commission-by-return-invoice-fp', 'SalesmanReportController@commissionByReturnInvoiceFp');


            Route::get('salesman-dropdown', 'SalesmanReportController@salesmanDropdown');
            

            Route::get('commission-by-sale-invoice-tp', 'SalesmanReportController@commissionBySaleInvoiceTp');

            Route::get('commission-by-customer-tp', 'SalesmanReportController@commissionByCustomerTp');

            Route::get('sales-due-tp', 'SalesmanReportController@salesDueTp');

            Route::get('commission-by-return-invoice-tp', 'SalesmanReportController@commissionByReturnInvoiceTp');
            

            Route::get('commission-summary-tp', 'SalesmanReportController@commissionSummaryTp');

            Route::get('sales-due-summary-tp', 'SalesmanReportController@salesDueSummaryTp');

        });
        

    });

});