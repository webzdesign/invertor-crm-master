<?php

use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
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
        Route::post('checkUserEmail', [UserController::class, 'checkUserEmail']);
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
        Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create')->middleware('ModuleAccessor:purchase-orders.create');
        Route::post('purchase-orders/store', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('purchase-orders/{id}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit')->middleware('ModuleAccessor:purchase-orders.edit');
        Route::put('purchase-orders/{id}/update', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::get('purchase-orders/{id}/view', [PurchaseOrderController::class, 'show'])->name('purchase-orders.view')->middleware('ModuleAccessor:purchase-orders.view');
        Route::get('purchase-orders/{id}/delete', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.delete')->middleware('ModuleAccessor:purchase-orders.delete');
        Route::post('get-products-on-category', [PurchaseOrderController::class, 'productsOnCategory'])->name('get-products-on-category');
        /** Purchase Order **/

        /** Common **/
        Route::post('getStates', [Helper::class, 'getStates'])->name('getStates');
        Route::post('getCities', [Helper::class, 'getCities'])->name('getCities');
        /** Common **/
    });
});