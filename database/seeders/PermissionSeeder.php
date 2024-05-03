<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'Add Roles', 'slug' => 'roles.create', 'model' => 'Role', 'description' => 'Can add roles', 'added_by' => 1],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'model' => 'Role', 'description' => 'Can edit roles', 'added_by' => 1],
            ['name' => 'View Roles', 'slug' => 'roles.view', 'model' => 'Role', 'description' => 'Can view roles', 'added_by' => 1],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'model' => 'Role', 'description' => 'Can delete roles', 'added_by' => 1],
            ['name' => 'Active/Inactive Roles', 'slug' => 'roles.activeinactive', 'model' => 'Role', 'description' => 'Can Activate or deactivate roles', 'added_by' => 1],

            ['name' => 'Add Users', 'slug' => 'users.create', 'model' => 'User', 'description' => 'Can add Users', 'added_by' => 1],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'model' => 'User', 'description' => 'Can Edit Users', 'added_by' => 1],
            ['name' => 'View Users', 'slug' => 'users.view', 'model' => 'User', 'description' => 'Can view Users', 'added_by' => 1],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'model' => 'User', 'description' => 'Can delete Users', 'added_by' => 1],
            ['name' => 'Active/Inactive Users', 'slug' => 'users.activeinactive', 'model' => 'User', 'description' => 'Can Activate or deactivate Users', 'added_by' => 1],

            ['name' => 'Add Categories', 'slug' => 'categories.create', 'model' => 'Category', 'description' => 'Can add Categories', 'added_by' => 1],
            ['name' => 'Edit Categories', 'slug' => 'categories.edit', 'model' => 'Category', 'description' => 'Can edit Categories', 'added_by' => 1],
            ['name' => 'View Categories', 'slug' => 'categories.view', 'model' => 'Category', 'description' => 'Can view Categories', 'added_by' => 1],
            ['name' => 'Delete Categories', 'slug' => 'categories.delete', 'model' => 'Category', 'description' => 'Can delete Categories', 'added_by' => 1],
            ['name' => 'Active/Inactive Categories', 'slug' => 'categories.activeinactive', 'model' => 'Category', 'description' => 'Can Activate or deactivate Categories', 'added_by' => 1],

            ['name' => 'Add Products', 'slug' => 'products.create', 'model' => 'Product', 'description' => 'Can add Products', 'added_by' => 1],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'model' => 'Product', 'description' => 'Can edit Products', 'added_by' => 1],
            ['name' => 'View Products', 'slug' => 'products.view', 'model' => 'Product', 'description' => 'Can view Products', 'added_by' => 1],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'model' => 'Product', 'description' => 'Can delete Products', 'added_by' => 1],
            ['name' => 'Active/Inactive Products', 'slug' => 'products.activeinactive', 'model' => 'Product', 'description' => 'Can Activate or deactivate Products', 'added_by' => 1],

            ['name' => 'Add Purchase Orders', 'slug' => 'purchase-orders.create', 'model' => 'PurchaseOrder', 'description' => 'Can add Purchase Orders', 'added_by' => 1],
            ['name' => 'Edit Purchase Orders', 'slug' => 'purchase-orders.edit', 'model' => 'PurchaseOrder', 'description' => 'Can edit Purchase Orders', 'added_by' => 1],
            ['name' => 'View Purchase Orders', 'slug' => 'purchase-orders.view', 'model' => 'PurchaseOrder', 'description' => 'Can view Purchase Orders', 'added_by' => 1],
            ['name' => 'Delete Purchase Orders', 'slug' => 'purchase-orders.delete', 'model' => 'PurchaseOrder', 'description' => 'Can delete Purchase Orders', 'added_by' => 1],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate($permission);
        }
    }
}
