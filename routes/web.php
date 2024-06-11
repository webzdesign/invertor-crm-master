<?php

use App\Http\Controllers\PaymentForDeliveryController;
use App\Http\Controllers\SalesOrderStatusController;
use App\Http\Controllers\ProcurementCostController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;

Auth::routes();

Route::get('/', function () {
    return redirect("/login");
});

Route::get('/register', function () {
    return redirect("/login");
});

Route::match(['GET', 'POST'], 'register/{role}/{user?}', [UserController::class, 'register']);
Route::post('checkUserEmail', [UserController::class, 'checkUserEmail']);

Route::group(["middleware" => "auth"], function () {
    Route::group(["middleware" => "StatusChecker"], function () {
        Route::get('dashboard', [\App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

        /** Users **/
        Route::match(['GET', 'POST'], 'users', [UserController::class, 'index'])->name('users.index')->middleware('ModuleAccessor:users.view');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('ModuleAccessor:users.create');
        Route::get('users/{id}/view', [UserController::class, 'show'])->name('users.view')->middleware('ModuleAccessor:users.view');
        Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('ModuleAccessor:users.edit');
        Route::post('users/store', [UserController::class, 'store'])->name('users.store');
        Route::put('users/{id}/update', [UserController::class, 'update'])->name('users.update');
        Route::get('users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete')->middleware('ModuleAccessor:users.delete');
        Route::get('users/{id}/status', [UserController::class, 'status'])->name('users.activeinactive')->middleware('ModuleAccessor:users.activeinactive');
        /** Users **/

        /** Roles **/
        Route::match(['GET', 'POST'], 'roles', [RoleController::class, 'index'])->name('roles.index')->middleware('ModuleAccessor:roles.view');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('ModuleAccessor:roles.create');
        Route::post('roles/store', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('ModuleAccessor:roles.edit');
        Route::put('roles/{id}/update', [RoleController::class, 'update'])->name('roles.update');
        Route::get('roles/{id}/view', [RoleController::class, 'show'])->name('roles.view')->middleware('ModuleAccessor:roles.view');
        Route::get('roles/{id}/delete', [RoleController::class, 'destroy'])->name('roles.delete')->middleware('ModuleAccessor:roles.delete');
        Route::get('roles/{id}/status', [RoleController::class, 'status'])->name('roles.activeinactive')->middleware('ModuleAccessor:roles.activeinactive');
        Route::post('roles/checkRoleExist', [RoleController::class, 'checkRoleExist']);
        /** Roles **/

        /** Categories **/
        Route::match(['GET', 'POST'], 'categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('ModuleAccessor:categories.view');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create')->middleware('ModuleAccessor:categories.create');
        Route::post('categories/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit')->middleware('ModuleAccessor:categories.edit');
        Route::put('categories/{id}/update', [CategoryController::class, 'update'])->name('categories.update');
        Route::get('categories/{id}/view', [CategoryController::class, 'show'])->name('categories.view')->middleware('ModuleAccessor:categories.view');
        Route::get('categories/{id}/delete', [CategoryController::class, 'destroy'])->name('categories.delete')->middleware('ModuleAccessor:categories.delete');
        Route::get('categories/{id}/status', [CategoryController::class, 'status'])->name('categories.activeinactive')->middleware('ModuleAccessor:categories.activeinactive');
        Route::post('checkCategory', [CategoryController::class, 'checkCategory']);
        /** Categories **/

        /** Products **/
        Route::match(['GET', 'POST'], 'products', [ProductController::class, 'index'])->name('products.index')->middleware('ModuleAccessor:products.view');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create')->middleware('ModuleAccessor:products.create');
        Route::post('products/store', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('ModuleAccessor:products.edit');
        Route::put('products/{id}/update', [ProductController::class, 'update'])->name('products.update');
        Route::get('products/{id}/view', [ProductController::class, 'show'])->name('products.view')->middleware('ModuleAccessor:products.view');
        Route::get('products/{id}/delete', [ProductController::class, 'destroy'])->name('products.delete')->middleware('ModuleAccessor:products.delete');
        Route::get('products/{id}/status', [ProductController::class, 'status'])->name('products.activeinactive')->middleware('ModuleAccessor:products.activeinactive');
        Route::post('checkProduct', [ProductController::class, 'checkProduct']);
        Route::get('products-image/{id}', [ProductController::class, 'images'])->name('products.image');
        Route::post('product-image/{id}', [ProductController::class, 'saveProductImage'])->name('product-image');
        Route::delete('remove-product-images', [ProductController::class, 'deleteImage'])->name('remove-product-images');
        /** Products **/

        /** Purchase Order **/
        Route::match(['GET', 'POST'], 'purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index')->middleware('ModuleAccessor:purchase-orders.view');
        Route::post('purchase-orders-data', [PurchaseOrderController::class, 'data'])->name('purchase-orders.data')->middleware('ModuleAccessor:purchase-orders.view');
        Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create')->middleware('ModuleAccessor:purchase-orders.create');
        Route::post('purchase-orders/store', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('purchase-orders/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit')->middleware('ModuleAccessor:purchase-orders.edit');
        Route::put('purchase-orders/{id}/update', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::get('purchase-orders/{id}/view', [PurchaseOrderController::class, 'show'])->name('purchase-orders.view')->middleware('ModuleAccessor:purchase-orders.view');
        Route::get('purchase-orders/{id}/delete', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.delete')->middleware('ModuleAccessor:purchase-orders.delete');
        Route::post('get-products-on-category', [PurchaseOrderController::class, 'productsOnCategory'])->name('get-products-on-category');
        /** Purchase Order **/

        /** Distribution System  **/
        Route::match(['GET', 'POST'], 'distribution', [DistributionController::class, 'index'])->name('distribution.index')->middleware('ModuleAccessor:distribution.view');
        Route::get('distribution/create', [DistributionController::class, 'create'])->name('distribution.create')->middleware('ModuleAccessor:distribution.create');
        Route::post('distribution/store', [DistributionController::class, 'store'])->name('distribution.store');
        Route::get('distribution/{id}/view', [DistributionController::class, 'show'])->name('distribution.view')->middleware('ModuleAccessor:distribution.view');
        Route::post('get-blade-for-distribution', [DistributionController::class, 'getBlade'])->name('get-blade-for-distribution');
        Route::post('getProducts', [DistributionController::class, 'getProducts'])->name('getProducts');
        /** Distribution System **/

        /** Procurement Cost **/
        Route::match(['GET', 'POST'], 'procurement-cost', [ProcurementCostController::class, 'index'])->name('procurement-cost.index')->middleware('ModuleAccessor:procurement-cost.view');
        Route::get('procurement-cost/create', [ProcurementCostController::class, 'create'])->name('procurement-cost.create')->middleware('ModuleAccessor:procurement-cost.create');
        Route::post('procurement-cost/store', [ProcurementCostController::class, 'store'])->name('procurement-cost.store');
        Route::get('procurement-cost/{id}/edit', [ProcurementCostController::class, 'edit'])->name('procurement-cost.edit')->middleware('ModuleAccessor:procurement-cost.edit');
        Route::put('procurement-cost/{id}/update', [ProcurementCostController::class, 'update'])->name('procurement-cost.update');
        Route::get('procurement-cost/{id}/view', [ProcurementCostController::class, 'show'])->name('procurement-cost.view')->middleware('ModuleAccessor:procurement-cost.view');
        Route::get('procurement-cost/{id}/delete', [ProcurementCostController::class, 'destroy'])->name('procurement-cost.delete')->middleware('ModuleAccessor:procurement-cost.delete');
        Route::get('procurement-cost/{id}/status', [ProcurementCostController::class, 'status'])->name('procurement-cost.activeinactive')->middleware('ModuleAccessor:procurement-cost.activeinactive');
        Route::post('procurement-cost/check', [ProcurementCostController::class, 'check']);
        /** Procurement Cost **/

        /** Sales Order **/
        Route::match(['GET', 'POST'], 'sales-orders', [SalesOrderController::class, 'index'])->name('sales-orders.index')->middleware('ModuleAccessor:sales-orders.view');
        Route::get('sales-orders/create', [SalesOrderController::class, 'create'])->name('sales-orders.create')->middleware('ModuleAccessor:sales-orders.create');
        Route::post('sales-orders/store', [SalesOrderController::class, 'store'])->name('sales-orders.store');
        Route::get('sales-orders/{id}/edit', [SalesOrderController::class, 'edit'])->name('sales-orders.edit')->middleware('ModuleAccessor:sales-orders.edit');
        Route::put('sales-orders/{id}/update', [SalesOrderController::class, 'update'])->name('sales-orders.update');
        Route::get('sales-orders/{id}/view', [SalesOrderController::class, 'show'])->name('sales-orders.view')->middleware('ModuleAccessor:sales-orders.view');
        Route::get('sales-orders/{id}/delete', [SalesOrderController::class, 'destroy'])->name('sales-orders.delete')->middleware('ModuleAccessor:sales-orders.delete');
        Route::post('get-products-on-category-so', [SalesOrderController::class, 'productsOnCategory'])->name('get-products-on-category-so');
        Route::post('get-available-item', [SalesOrderController::class, 'getAvailableItem'])->name('get-available-item');
        Route::post('save-so', [SalesOrderController::class, 'saveSo'])->name('save-so');
        /** Sales Order **/

        /** Suppliers **/
        Route::match(['GET', 'POST'], 'suppliers', [SupplierController::class, 'index'])->name('suppliers.index')->middleware('ModuleAccessor:suppliers.view');
        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create')->middleware('ModuleAccessor:suppliers.create');
        Route::get('suppliers/{id}/view', [SupplierController::class, 'show'])->name('suppliers.view')->middleware('ModuleAccessor:suppliers.view');
        Route::get('suppliers/{id}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit')->middleware('ModuleAccessor:suppliers.edit');
        Route::post('suppliers/store', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::put('suppliers/{id}/update', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::get('suppliers/{id}/delete', [SupplierController::class, 'destroy'])->name('suppliers.delete')->middleware('ModuleAccessor:suppliers.delete');
        Route::get('suppliers/{id}/status', [SupplierController::class, 'status'])->name('suppliers.activeinactive')->middleware('ModuleAccessor:suppliers.activeinactive');
        /** Suppliers **/

        /** Orders To Deliver (for driver) **/
        Route::match(['GET', 'POST'], 'orders-to-deliver', [SalesOrderController::class, 'ordersToBeDeliverd'])->name('orders-to-deliver');
        /** Orders To Deliver (for driver) **/

        /** Report **/
        Route::match(['GET', 'POST'], 'stock-report', [ReportController::class, 'stockReport'])->name('stock-report')->middleware('ModuleAccessor:stock-report.view');
        /** Report **/

        /** Payment for deliveyr **/
        Route::match(['GET', 'POST'], 'payment-for-delivery', [PaymentForDeliveryController::class, 'index'])->name('payment-for-delivery')->middleware('ModuleAccessor:payment-for-delivery.view');        
        /** Payment for deliveyr **/

        /** Sales Order Status **/
        Route::match(['GET', 'POST'], 'sales-order-status-list', [SalesOrderStatusController::class, 'list'])->name('sales-order-status-list');
        Route::get('sales-order-status', [SalesOrderStatusController::class, 'index'])->name('sales-order-status')->middleware('ModuleAccessor:sales-order-status.view');
        Route::post('sales-order-status/sequence', [SalesOrderStatusController::class, 'sequence'])->name('sales-order-status-sequence')->middleware('ModuleAccessor:sales-order-status.edit');
        Route::get('sales-order-status/delete', [SalesOrderStatusController::class, 'delete'])->name('sales-order-status-delete')->middleware('ModuleAccessor:sales-order-status.delete');
        Route::get('sales-order-status/automate', [SalesOrderStatusController::class, 'edit'])->name('sales-order-status-edit')->middleware('ModuleAccessor:sales-order-status.edit');
        Route::post('sales-order-status/update', [SalesOrderStatusController::class, 'update'])->name('sales-order-status-update')->middleware('ModuleAccessor:sales-order-status.edit');
        Route::post('sales-order-status/create', [SalesOrderStatusController::class, 'create'])->name('sales-order-status-store')->middleware('ModuleAccessor:sales-order-status.create');
        Route::post('sales-order-status-update-status', [SalesOrderStatusController::class, 'status'])->name('sales-order-status-update-status');
        Route::post('sales-order-status-update-status-bulk', [SalesOrderStatusController::class, 'statusBulkUpdate'])->name('sales-order-status-update-status-bulk');
        Route::post('sales-order-manage-role', [SalesOrderStatusController::class, 'manageStatus'])->name('sales-order-manage-role')->middleware('ModuleAccessor:sales-order-status.edit');
        Route::post('sales-order-manage-role-get', [SalesOrderStatusController::class, 'getManagedStatus'])->name('sales-order-manage-role-get')->middleware('ModuleAccessor:sales-order-status.edit');
        Route::post('sales-order-next-status', [SalesOrderStatusController::class, 'nextStatus'])->name('sales-order-next-status');
        Route::post('put-order-on-cron', [SalesOrderStatusController::class, 'putOnCron'])->name('put-order-on-cron');
        Route::post('put-task-for-order', [SalesOrderStatusController::class, 'putTaskForOrder'])->name('put-task-for-order');
        Route::post('order-detail-in-board', [SalesOrderStatusController::class, 'orderDetailInBoard'])->name('order-detail-in-board');
        Route::post('sales-order-next-status-for-add-task', [SalesOrderStatusController::class, 'nextStatusForTask'])->name('sales-order-next-status-for-add-task');
        Route::post('remove-task', [SalesOrderStatusController::class, 'removeTask'])->name('remove-task');
        Route::post('save-completion-description-for-task', [SalesOrderStatusController::class, 'saveDescription'])->name('save-completion-description-for-task');
        Route::post('sales-order-responsible-user', [SalesOrderStatusController::class, 'salesOrderResponsibleUser'])->name('sales-order-responsible-user');
        Route::post('sales-order-responsible-user-save', [SalesOrderStatusController::class, 'salesOrderResponsibleUserSave'])->name('sales-order-responsible-user-save');
        Route::post('get-trigger-tasks', [SalesOrderStatusController::class, 'getTriggerTasks'])->name('get-trigger-tasks');
        Route::delete('delete-status', [SalesOrderStatusController::class, 'deleteStatus'])->name('delete-status');
        /** Sales Order Status **/

        /** Common **/
        Route::post('getStates', [Helper::class, 'getStates'])->name('getStates');
        Route::post('getCities', [Helper::class, 'getCities'])->name('getCities');
        /** Common **/
    });
});