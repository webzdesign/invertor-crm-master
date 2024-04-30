<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('/', function () {
    return redirect("/login");
});

Route::get('/register', function () {
    return redirect("/login");
});

Route::group(["middleware" => "auth"], function () {
    Route::get('dashboard', [\App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('ModuleAccessor:users.view');
    Route::post('users/getallusers', [UserController::class, 'DataTable'])->name('users.getallusers');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('ModuleAccessor:users.create');
    Route::get('users/{id}/view', [UserController::class, 'show'])->name('users.view')->middleware('ModuleAccessor:users.view');
    Route::get('users/{id}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('ModuleAccessor:users.edit');
    Route::post('users/store', [UserController::class, 'store'])->name('users.store');
    Route::put('users/{id}/update', [UserController::class, 'update'])->name('users.update');
    Route::get('users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete')->middleware('ModuleAccessor:users.delete');
    Route::get('users/{id}/status', [UserController::class, 'status'])->name('users.activeinactive')->middleware('ModuleAccessor:users.activeinactive');
    Route::post('/checkUserPhoneNumber', [UserController::class, 'checkUserPhoneNumber']);

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('ModuleAccessor:roles.view');
    Route::post('roles/getallroles', [RoleController::class, 'DataTable'])->name('roles.getallroles');
    Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('ModuleAccessor:roles.create');
    Route::post('roles/store', [RoleController::class, 'store'])->name('roles.store');
    Route::get('roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('ModuleAccessor:roles.edit');
    Route::put('roles/{id}/update', [RoleController::class, 'update'])->name('roles.update');
    Route::get('roles/{id}/view', [RoleController::class, 'show'])->name('roles.view')->middleware('ModuleAccessor:roles.view');
    Route::get('roles/{id}/delete', [RoleController::class, 'destroy'])->name('roles.delete')->middleware('ModuleAccessor:roles.delete');
    Route::get('roles/{id}/status', [RoleController::class, 'status'])->name('roles.activeinactive')->middleware('ModuleAccessor:roles.activeinactive');
    Route::post('roles/checkRoleExist', [RoleController::class, 'checkRoleExist']);

});