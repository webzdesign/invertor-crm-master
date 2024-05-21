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

            ['name' => 'Add Storage', 'slug' => 'purchase-orders.create', 'model' => 'Storage', 'description' => 'Can add Storage', 'added_by' => 1],
            ['name' => 'Edit Storage', 'slug' => 'purchase-orders.edit', 'model' => 'Storage', 'description' => 'Can edit Storage', 'added_by' => 1],
            ['name' => 'View Storage', 'slug' => 'purchase-orders.view', 'model' => 'Storage', 'description' => 'Can view Storage', 'added_by' => 1],
            ['name' => 'Delete Storage', 'slug' => 'purchase-orders.delete', 'model' => 'Storage', 'description' => 'Can delete Storage', 'added_by' => 1],

            ['name' => 'Add Procurement Cost', 'slug' => 'procurement-cost.create', 'model' => 'ProcurementCost', 'description' => 'Can add Procurement Cost', 'added_by' => 1],
            ['name' => 'Edit Procurement Cost', 'slug' => 'procurement-cost.edit', 'model' => 'ProcurementCost', 'description' => 'Can edit Procurement Cost', 'added_by' => 1],
            ['name' => 'View Procurement Cost', 'slug' => 'procurement-cost.view', 'model' => 'ProcurementCost', 'description' => 'Can view Procurement Cost', 'added_by' => 1],
            ['name' => 'Delete Procurement Cost', 'slug' => 'procurement-cost.delete', 'model' => 'ProcurementCost', 'description' => 'Can delete Procurement Cost', 'added_by' => 1],
            ['name' => 'Active/Inactive Procurement Cost', 'slug' => 'procurement-cost.activeinactive', 'model' => 'ProcurementCost', 'description' => 'Can Activate or deactivate Procurement Cost', 'added_by' => 1],

            ['name' => 'Add Sales Orders', 'slug' => 'sales-orders.create', 'model' => 'SalesOrder', 'description' => 'Can add Sales Orders', 'added_by' => 1],
            ['name' => 'Edit Sales Orders', 'slug' => 'sales-orders.edit', 'model' => 'SalesOrder', 'description' => 'Can edit Sales Orders', 'added_by' => 1],
            ['name' => 'View Sales Orders', 'slug' => 'sales-orders.view', 'model' => 'SalesOrder', 'description' => 'Can view Sales Orders', 'added_by' => 1],
            ['name' => 'Delete Sales Orders', 'slug' => 'sales-orders.delete', 'model' => 'SalesOrder', 'description' => 'Can delete Sales Orders', 'added_by' => 1],

            ['name' => 'Add Supplier', 'slug' => 'suppliers.create', 'model' => 'Supplier', 'description' => 'Can add Supplier', 'added_by' => 1],
            ['name' => 'Edit Supplier', 'slug' => 'suppliers.edit', 'model' => 'Supplier', 'description' => 'Can Edit Supplier', 'added_by' => 1],
            ['name' => 'View Supplier', 'slug' => 'suppliers.view', 'model' => 'Supplier', 'description' => 'Can view Supplier', 'added_by' => 1],
            ['name' => 'Delete Supplier', 'slug' => 'suppliers.delete', 'model' => 'Supplier', 'description' => 'Can delete Supplier', 'added_by' => 1],
            ['name' => 'Active/Inactive Supplier', 'slug' => 'suppliers.activeinactive', 'model' => 'Supplier', 'description' => 'Can Activate or deactivate Supplier', 'added_by' => 1],

            ['name' => 'Add Sales Order Status', 'slug' => 'sales-order-status.create', 'model' => 'SalesOrderStatus', 'description' => 'Can add Sales Order Status', 'added_by' => 1],
            ['name' => 'Edit Sales Order Status', 'slug' => 'sales-order-status.edit', 'model' => 'SalesOrderStatus', 'description' => 'Can edit Sales Order Status', 'added_by' => 1],
            ['name' => 'View Sales Order Status', 'slug' => 'sales-order-status.view', 'model' => 'SalesOrderStatus', 'description' => 'Can view Sales Order Status', 'added_by' => 1],
            ['name' => 'Delete Sales Order Status', 'slug' => 'sales-order-status.delete', 'model' => 'SalesOrderStatus', 'description' => 'Can delete Sales Order Status', 'added_by' => 1],

            ['name' => 'Add Distribution', 'slug' => 'distribution.create', 'model' => 'Distribution', 'description' => 'Can add Distribution', 'added_by' => 1],
            ['name' => 'View Distribution', 'slug' => 'distribution.view', 'model' => 'Distribution', 'description' => 'Can view Distribution', 'added_by' => 1],

            ['name' => 'View Stock Report', 'slug' => 'stock-report.view', 'model' => 'Report', 'description' => 'Can view Stock Report', 'added_by' => 1],

            ['name' => 'View Payment for Delivery', 'slug' => 'payment-for-delivery.view', 'model' => 'PaymentForDelivery', 'description' => 'Can View Payment for Delivery', 'added_by' => 1],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate($permission);
        }
    }
}
