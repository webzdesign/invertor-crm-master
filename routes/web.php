<?php


use App\Http\Controllers\BrandsController;
use App\Http\Controllers\CallTaskStatusController;
use App\Http\Controllers\GiftsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformationPagesController;
use App\Http\Controllers\PaymentForDeliveryController;
use App\Http\Controllers\SalesOrderStatusController;
use App\Http\Controllers\ProcurementCostController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\MoldcellWebhookController;
use Illuminate\Support\Facades\Artisan;
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

Route::get('view', function () {
    Artisan::call('view:clear');
    return redirect()->back();
});

Route::match(['GET', 'POST'], 'register/{role}/{user?}', [UserController::class, 'register']);
Route::post('checkUserEmail', [UserController::class, 'checkUserEmail']);

Route::post('moldcellWebhook', [MoldcellWebhookController::class, 'handleMoldcellWebhook']);

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
        Route::get('users/{id}/statusapprove', [UserController::class, 'approve'])->name('users.approve')->middleware('ModuleAccessor:users.activeinactive');
        Route::post('role-permissions', [UserController::class, 'rolePermissions'])->name('role-permissions');
        Route::delete('remove-user-document', [UserController::class, 'removeUserDocument'])->name('remove-user-document');
        /** Users **/

        /** Roles **/
        Route::match(['GET', 'POST'], 'roles', [RoleController::class, 'index'])->name('roles.index')->middleware('ModuleAccessor:roles.view');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('ModuleAccessor:roles.create');
        Route::post('roles/store', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('ModuleAccessor:roles.edit');
        Route::get('set-required-documents/{id}', [RoleController::class, 'setDocs'])->name('set-required-documents')->middleware('ModuleAccessor:roles.edit');
        Route::put('save-required-documents/{id}', [RoleController::class, 'saveDocs'])->name('save-required-documents');
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
        Route::post('checkProductSlug', [ProductController::class, 'checkProductSlug']);
        Route::post('/products/is-hot-product', [ProductController::class, 'isHotProduct'])->name('isHotProduct');
        Route::post('/products/getBrandsByCatgeory', [ProductController::class, 'getBrandsByCatgeory'])->name('getBrandsByCatgeory');
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
        /* Route::match(['GET', 'POST'], 'distribution', [DistributionController::class, 'index'])->name('distribution.index')->middleware('ModuleAccessor:distribution.view');
        Route::get('distribution/create', [DistributionController::class, 'create'])->name('distribution.create')->middleware('ModuleAccessor:distribution.create');
        Route::post('distribution/store', [DistributionController::class, 'store'])->name('distribution.store');
        Route::get('distribution/{id}/view', [DistributionController::class, 'show'])->name('distribution.view')->middleware('ModuleAccessor:distribution.view');
        Route::post('get-blade-for-distribution', [DistributionController::class, 'getBlade'])->name('get-blade-for-distribution');
        Route::post('getProducts', [DistributionController::class, 'getProducts'])->name('getProducts'); */
        /** Distribution System **/

        /** Procurement Cost **/
       /* Route::match(['GET', 'POST'], 'procurement-cost', [ProcurementCostController::class, 'index'])->name('procurement-cost.index')->middleware('ModuleAccessor:procurement-cost.view');
        Route::get('procurement-cost/create', [ProcurementCostController::class, 'create'])->name('procurement-cost.create')->middleware('ModuleAccessor:procurement-cost.create');
        Route::post('procurement-cost/store', [ProcurementCostController::class, 'store'])->name('procurement-cost.store');
        Route::get('procurement-cost/{id}/edit', [ProcurementCostController::class, 'edit'])->name('procurement-cost.edit')->middleware('ModuleAccessor:procurement-cost.edit');
        Route::put('procurement-cost/{id}/update', [ProcurementCostController::class, 'update'])->name('procurement-cost.update');
        Route::get('procurement-cost/{id}/view', [ProcurementCostController::class, 'show'])->name('procurement-cost.view')->middleware('ModuleAccessor:procurement-cost.view');
        Route::get('procurement-cost/{id}/delete', [ProcurementCostController::class, 'destroy'])->name('procurement-cost.delete')->middleware('ModuleAccessor:procurement-cost.delete');
        Route::get('procurement-cost/{id}/status', [ProcurementCostController::class, 'status'])->name('procurement-cost.activeinactive')->middleware('ModuleAccessor:procurement-cost.activeinactive');
        Route::post('procurement-cost/check', [ProcurementCostController::class, 'check']); */
        /** Procurement Cost **/

        /** Sales Order **/
        /* Route::match(['GET', 'POST'], 'sales-orders', [SalesOrderController::class, 'index'])->name('sales-orders.index')->middleware('ModuleAccessor:sales-orders.view');
        Route::get('sales-orders/create', [SalesOrderController::class, 'create'])->name('sales-orders.create')->middleware('ModuleAccessor:sales-orders.create');
        Route::post('sales-orders/store', [SalesOrderController::class, 'store'])->name('sales-orders.store');
        Route::get('sales-orders/{id}/edit', [SalesOrderController::class, 'edit'])->name('sales-orders.edit')->middleware('ModuleAccessor:sales-orders.edit');
        Route::put('sales-orders/{id}/update', [SalesOrderController::class, 'update'])->name('sales-orders.update');
        Route::get('sales-orders/{id}/view', [SalesOrderController::class, 'show'])->name('sales-orders.view')->middleware('ModuleAccessor:sales-orders.view');
        Route::get('sales-orders/{id}/delete', [SalesOrderController::class, 'destroy'])->name('sales-orders.delete')->middleware('ModuleAccessor:sales-orders.delete');
        Route::post('get-products-on-category-so', [SalesOrderController::class, 'productsOnCategory'])->name('get-products-on-category-so');
        Route::post('get-available-item', [SalesOrderController::class, 'getAvailableItem'])->name('get-available-item');
        Route::post('save-so', [SalesOrderController::class, 'saveSo'])->name('save-so');
        Route::post('check-so-price', [SalesOrderController::class, 'checkPrice'])->name('check-so-price');
        Route::post('price-unmatched', [SalesOrderController::class, 'priceUnmatched'])->name('price-unmatched');
        Route::post('change-driver', [SalesOrderController::class, 'changeDriver'])->name('change-driver');
        Route::post('get-real-time-commission', [SalesOrderController::class, 'getRealTimeCommission'])->name('get-real-time-commission');
        Route::post('is-customer-scammer', [SalesOrderController::class, 'isCustomerScammer'])->name('is-customer-scammer');
        Route::get('sales-orders/{id}/confirm', [SalesOrderController::class, 'confirm'])->name('sales-orders.confirm')->middleware('ModuleAccessor:sales-orders.confirm'); */
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
        /* Route::match(['GET', 'POST'], 'orders-to-deliver', [SalesOrderController::class, 'ordersToBeDeliverd'])->name('orders-to-deliver')->middleware('ModuleAccessor:orders-to-deliver.view'); */
        /** Orders To Deliver (for driver) **/

        /** Report **/
        Route::match(['GET', 'POST'], 'stock-report', [ReportController::class, 'stockReport'])->name('stock-report')->middleware('ModuleAccessor:stock-report.view');
        Route::match(['GET', 'POST'], 'ledger-report', [ReportController::class, 'ledgerReport'])->name('ledger-report')->middleware('ModuleAccessor:ledger-report.view');
        /** Report **/

        /** Financial report **/
        /*Route::match(['GET', 'POST'], 'financial-report/driver', [ReportController::class, 'driverCommission'])->name('driver-commission')->middleware('ModuleAccessor:financial-driver-report.view');
        Route::match(['GET', 'POST'], 'financial-report/seller', [ReportController::class, 'sellerCommission'])->name('seller-commission')->middleware('ModuleAccessor:financial-seller-report.view');
        Route::post('pay-amount-to-admin', [ReportController::class, 'payAmountToAdmin'])->name('pay-amount-to-admin');
        Route::post('pay-amount-to-seller', [ReportController::class, 'payAmountToSeller'])->name('pay-amount-to-seller');
        Route::post('driver-payment-log', [ReportController::class, 'driverPaymentLog'])->name('driver-payment-log');
        Route::post('driver-payment/{type}', [ReportController::class, 'acceptOrRejectDriverPayment'])->name('driver-payment');
        Route::post('show-driver-payment-proofs', [ReportController::class, 'showDriverPaymentProofs'])->name('show-driver-payment-proofs');
        Route::post('iban-check', [ReportController::class, 'ibanCheck'])->name('iban-check');
        Route::post('bank-account-save', [ReportController::class, 'bankAccountSave'])->name('bank-account-save');
        Route::post('bank-account-delete', [ReportController::class, 'bankAccountDelete'])->name('bank-account-delete');
        Route::post('withdrawal-request', [ReportController::class, 'withdrawalRequest'])->name('withdrawal-request');
        Route::post('withdrawalable-amount', [ReportController::class, 'withdrawableAmount'])->name('withdrawalable-amount');
        Route::post('seller-withdrawal-reqs', [ReportController::class, 'withdrawReqs'])->name('seller-withdrawal-reqs');
        Route::post('seller-withdrawal-reqs-accepted', [ReportController::class, 'withdrawReqsAccepted'])->name('seller-withdrawal-reqs-accepted');
        Route::post('seller-withdrawal-reqs-rejected', [ReportController::class, 'withdrawReqsRejected'])->name('seller-withdrawal-reqs-rejected');
        Route::post('withdrawal-req-info', [ReportController::class, 'withdrawalReqInfo'])->name('withdrawal-req-info');
        Route::post('withdrwal-details', [ReportController::class, 'withdrwalDetails'])->name('withdrwal-details');
        Route::post('accept-withdrawal-request', [ReportController::class, 'acceptWithdrawalRequest'])->name('accept-withdrawal-request');
        Route::post('reject-withdrawal-request', [ReportController::class, 'rejectWithdrawalRequest'])->name('reject-withdrawal-request');
        Route::post('seller-withdrawal-reqs-2', [ReportController::class, 'withdrawReqs2'])->name('seller-withdrawal-reqs-2');*/
        /** Financial report **/

        /** Payment for deliveyr **/
        /*Route::match(['GET', 'POST'], 'payment-for-delivery', [PaymentForDeliveryController::class, 'index'])->name('payment-for-delivery')->middleware('ModuleAccessor:payment-for-delivery.view');*/
        /** Payment for deliveyr **/

        /** Sales Order Status **/
        Route::match(['GET', 'POST'], 'sales-order-status-list', [SalesOrderStatusController::class, 'list'])->name('sales-order-status-list');
        Route::get('sales-order-status', [SalesOrderStatusController::class, 'index'])->name('sales-order-status')->middleware('ModuleAccessor:sales-order-status.view');
        Route::post('sales-order-status/sequence', [SalesOrderStatusController::class, 'sequence'])->name('sales-order-status-sequence');
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
        Route::post('accept-the-order-from-driver', [SalesOrderStatusController::class, 'acceptOrder'])->name('accept-the-order-from-driver');
        Route::post('reject-the-order-from-driver', [SalesOrderStatusController::class, 'rejectOrder'])->name('reject-the-order-from-driver');
        Route::post('assign-new-driver/{order_id}', [SalesOrderStatusController::class, 'reassignDriverToOrder'])->name('assign-new-driver');
        Route::post('order-place-notification-save', [SalesOrderStatusController::class, 'orderPlaceNotificationSave'])->name('order-place-notification-save');
        Route::post('twillo-notification-save', [SalesOrderStatusController::class, 'twilloNotificationSave'])->name('twillo-notification-save');
        Route::post('twillo-notification-remove', [SalesOrderStatusController::class, 'twilloNotificationRemove'])->name('twillo-notification-remove');
        Route::post('twillo-notification/check', [SalesOrderStatusController::class, 'twilloNotificationCheck'])->name('twillo-notification-check');

        /** Sales Order Status **/

        /** Common **/
        Route::post('getStates', [Helper::class, 'getStates'])->name('getStates');
        Route::post('getCities', [Helper::class, 'getCities'])->name('getCities');
        /** Common **/

        /** Notification **/
        Route::get('read-notification/{id}/{url}', [HomeController::class, 'readNotification'])->name('read-notification');
        Route::get('read-all-notification', [HomeController::class, 'readAllNotification'])->name('read-all-notification');
        Route::post('get-notification', [HomeController::class, 'getNotification'])->name('get-notification');
        /** Notification **/

        Route::get('settings', [SettingController::class, 'index'])->name('settings');
        Route::put('settings-update', [SettingController::class, 'update'])->name('settings.update');

        /** contactus **/
        Route::match(['GET', 'POST'], 'contactus', [ContactUsController::class, 'index'])->name('contactus.index')->middleware('ModuleAccessor:contactus.view');
        Route::post( '/contactus/detail', [ContactUsController::class, 'detail'])->name('contactus.detail');
        /** contactus **/

        /* information pages */
        Route::group(['prefix' => 'information'], function () {
            Route::get('/', [InformationPagesController::class,'index'])->name('information.index')->middleware('ModuleAccessor:information.view');
            Route::post('/list', [InformationPagesController::class,'informationList'])->name('information.list');
            Route::post('/store', [InformationPagesController::class,'store'])->name('information.store');
            Route::get('/create', [InformationPagesController::class,'create'])->name('information.create')->middleware('ModuleAccessor:information.create');
            Route::get('/{id}/edit', [InformationPagesController::class,'edit'])->name('information.edit')->middleware('ModuleAccessor:information.edit');
            Route::get('/{id}/view', [InformationPagesController::class,'view'])->name('information.view')->middleware('ModuleAccessor:information.view');
            Route::post('/{id}/update', [InformationPagesController::class,'update'])->name('information.update');
            Route::get('/{id}/delete', [InformationPagesController::class,'destroy'])->name('information.delete')->middleware('ModuleAccessor:information.delete');
            Route::get('/{id}/status', [InformationPagesController::class, 'status'])->name('information.activeinactive')->middleware('ModuleAccessor:information.activeinactive');
        });
        /* information pages */

        /* slider */
        Route::group(['prefix' => 'sliders'], function () {
            Route::get('/', [SliderController::class,'index'])->name('sliders.index')->middleware('ModuleAccessor:sliders.view');
            Route::post('/list', [SliderController::class,'sliderList'])->name('sliders.list');
            Route::post('/store', [SliderController::class,'store'])->name('sliders.store');
            Route::get('/create', [SliderController::class,'create'])->name('sliders.create')->middleware('ModuleAccessor:sliders.create');
            Route::get('/{id}/edit', [SliderController::class,'edit'])->name('sliders.edit')->middleware('ModuleAccessor:sliders.edit');
            Route::post('/{id}/update', [SliderController::class,'update'])->name('sliders.update');
            Route::get('/{id}/view', [SliderController::class,'view'])->name('sliders.view')->middleware('ModuleAccessor:sliders.view');
             Route::get('/{id}/delete', [SliderController::class,'destroy'])->name('sliders.delete')->middleware('ModuleAccessor:sliders.delete');
            Route::get('/{id}/status', [SliderController::class, 'status'])->name('sliders.activeinactive')->middleware('ModuleAccessor:sliders.activeinactive');
        });
        /* slider */

        /** Brands **/
        Route::match(['GET', 'POST'], 'brands', [BrandsController::class, 'index'])->name('brands.index')->middleware('ModuleAccessor:brands.view');
        Route::get('brands/create', [BrandsController::class, 'create'])->name('brands.create')->middleware('ModuleAccessor:brands.create');
        Route::post('brands/store', [BrandsController::class, 'store'])->name('brands.store');
        Route::get('brands/{id}/edit', [BrandsController::class, 'edit'])->name('brands.edit')->middleware('ModuleAccessor:brands.edit');
        Route::put('brands/{id}/update', [BrandsController::class, 'update'])->name('brands.update');
        Route::get('brands/{id}/view', [BrandsController::class, 'show'])->name('brands.view')->middleware('ModuleAccessor:brands.view');
        Route::get('brands/{id}/delete', [BrandsController::class, 'destroy'])->name('brands.delete')->middleware('ModuleAccessor:brands.delete');
        Route::get('brands/{id}/status', [BrandsController::class, 'status'])->name('brands.activeinactive')->middleware('ModuleAccessor:brands.activeinactive');
        Route::post('checkBrands', [BrandsController::class, 'checkBrands']);
        /** Brands **/

        /** Gifts **/
        Route::match(['GET', 'POST'], 'gifts', [GiftsController::class, 'index'])->name('gifts.index')->middleware('ModuleAccessor:gifts.view');
        Route::get('gifts/create', [GiftsController::class, 'create'])->name('gifts.create')->middleware('ModuleAccessor:gifts.create');
        Route::post('gifts/store', [GiftsController::class, 'store'])->name('gifts.store');
        Route::get('gifts/{id}/edit', [GiftsController::class, 'edit'])->name('gifts.edit')->middleware('ModuleAccessor:gifts.edit');
        Route::put('gifts/{id}/update', [GiftsController::class, 'update'])->name('gifts.update');
        Route::get('gifts/{id}/view', [GiftsController::class, 'show'])->name('gifts.view')->middleware('ModuleAccessor:gifts.view');
        Route::get('gifts/{id}/delete', [GiftsController::class, 'destroy'])->name('gifts.delete')->middleware('ModuleAccessor:gifts.delete');
        Route::get('gifts/{id}/status', [GiftsController::class, 'status'])->name('gifts.activeinactive')->middleware('ModuleAccessor:gifts.activeinactive');
        /** Gifts **/

        /** Task Status **/
        Route::get('task-status', [CallTaskStatusController::class, 'index'])->name('task-status.index')->middleware('ModuleAccessor:task-status.view');
        Route::post('task-status/sequence', [CallTaskStatusController::class, 'sequence'])->name('task-status.sequence');
        Route::get('task-status/automate', [CallTaskStatusController::class, 'edit'])->name('task-status.edit')->middleware('ModuleAccessor:task-status.edit');
        Route::post('task-status/automate-update', [CallTaskStatusController::class, 'update'])->name('task-status.update')->middleware('ModuleAccessor:task-status.edit');
        Route::get('task-status/automate/put-task-for-order', [CallTaskStatusController::class, 'putTaskForOrder'])->name('task-status.put-task-for-order')->middleware('ModuleAccessor:task-status.edit');
        Route::post('task-status/call-history-detail-in-board', [CallTaskStatusController::class, 'callDetailInBoard'])->name('task-status.call-history-detail');
        Route::delete('task-status/automate/delete-status', [CallTaskStatusController::class, 'delete-status'])->name('task-status.delete-status')->middleware('ModuleAccessor:task-status.edit');
        Route::post('save-completion-description-for-call-task', [CallTaskStatusController::class, 'saveDescription'])->name('save-completion-description-for-call-task');
        Route::post('remove-call-task', [CallTaskStatusController::class, 'removeCallTask'])->name('remove-call-task');
        /** Task Status **/
        
    });
});
