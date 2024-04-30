<?php

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