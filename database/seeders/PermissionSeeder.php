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

            /** User Management **/

            ['name' => 'Add roles', 'slug' => 'roles.create', 'model' => 'Role', 'description' => 'Can add roles', 'added_by' => 1],
            ['name' => 'Edit roles', 'slug' => 'roles.edit', 'model' => 'Role', 'description' => 'Can edit roles', 'added_by' => 1],
            ['name' => 'View roles', 'slug' => 'roles.view', 'model' => 'Role', 'description' => 'Can view roles', 'added_by' => 1],
            ['name' => 'Delete roles', 'slug' => 'roles.delete', 'model' => 'Role', 'description' => 'Can delete roles', 'added_by' => 1],
            ['name' => 'Active/Inactive roles', 'slug' => 'roles.activeinactive', 'model' => 'Role', 'description' => 'Can activate or inactive roles', 'added_by' => 1],

            ['name' => 'Add users', 'slug' => 'users.create', 'model' => 'User', 'description' => 'Can add users', 'added_by' => 1],
            ['name' => 'Edit users', 'slug' => 'users.edit', 'model' => 'User', 'description' => 'Can Edit users', 'added_by' => 1],
            ['name' => 'View users', 'slug' => 'users.view', 'model' => 'User', 'description' => 'Can view users', 'added_by' => 1],
            ['name' => 'Delete users', 'slug' => 'users.delete', 'model' => 'User', 'description' => 'Can delete users', 'added_by' => 1],
            ['name' => 'Active/Inactive users', 'slug' => 'users.activeinactive', 'model' => 'User', 'description' => 'Can activate or inactive users', 'added_by' => 1],

            ['name' => 'Add supplier', 'slug' => 'suppliers.create', 'model' => 'Supplier', 'description' => 'Can add supplier', 'added_by' => 1],
            ['name' => 'Edit supplier', 'slug' => 'suppliers.edit', 'model' => 'Supplier', 'description' => 'Can Edit supplier', 'added_by' => 1],
            ['name' => 'View supplier', 'slug' => 'suppliers.view', 'model' => 'Supplier', 'description' => 'Can view supplier', 'added_by' => 1],
            ['name' => 'Delete supplier', 'slug' => 'suppliers.delete', 'model' => 'Supplier', 'description' => 'Can delete supplier', 'added_by' => 1],
            ['name' => 'Active/Inactive supplier', 'slug' => 'suppliers.activeinactive', 'model' => 'Supplier', 'description' => 'Can activate or inactive supplier', 'added_by' => 1],

            /** User Management **/

            /** Product & Stock Management **/

            ['name' => 'Add categories', 'slug' => 'categories.create', 'model' => 'Category', 'description' => 'Can add categories', 'added_by' => 1],
            ['name' => 'Edit categories', 'slug' => 'categories.edit', 'model' => 'Category', 'description' => 'Can edit categories', 'added_by' => 1],
            ['name' => 'View categories', 'slug' => 'categories.view', 'model' => 'Category', 'description' => 'Can view categories', 'added_by' => 1],
            ['name' => 'Delete categories', 'slug' => 'categories.delete', 'model' => 'Category', 'description' => 'Can delete categories', 'added_by' => 1],
            ['name' => 'Active/Inactive categories', 'slug' => 'categories.activeinactive', 'model' => 'Category', 'description' => 'Can activate or inactive categories', 'added_by' => 1],

            ['name' => 'Add products', 'slug' => 'products.create', 'model' => 'Product', 'description' => 'Can add products', 'added_by' => 1],
            ['name' => 'Edit products', 'slug' => 'products.edit', 'model' => 'Product', 'description' => 'Can edit products', 'added_by' => 1],
            ['name' => 'View products', 'slug' => 'products.view', 'model' => 'Product', 'description' => 'Can view products', 'added_by' => 1],
            ['name' => 'Delete products', 'slug' => 'products.delete', 'model' => 'Product', 'description' => 'Can delete products', 'added_by' => 1],
            ['name' => 'Active/Inactive products', 'slug' => 'products.activeinactive', 'model' => 'Product', 'description' => 'Can activate or inactive products', 'added_by' => 1],

            ['name' => 'Add storage', 'slug' => 'purchase-orders.create', 'model' => 'Storage', 'description' => 'Can add storage', 'added_by' => 1],
            ['name' => 'Edit storage', 'slug' => 'purchase-orders.edit', 'model' => 'Storage', 'description' => 'Can edit storage', 'added_by' => 1],
            ['name' => 'View storage', 'slug' => 'purchase-orders.view', 'model' => 'Storage', 'description' => 'Can view storage', 'added_by' => 1],
            ['name' => 'Delete storage', 'slug' => 'purchase-orders.delete', 'model' => 'Storage', 'description' => 'Can delete storage', 'added_by' => 1],

            ['name' => 'Add distribution', 'slug' => 'distribution.create', 'model' => 'Distribution', 'description' => 'Can add distribution', 'added_by' => 1],
            ['name' => 'View distribution', 'slug' => 'distribution.view', 'model' => 'Distribution', 'description' => 'Can view distribution', 'added_by' => 1],

            /** Product & Stock Management **/

            /** Price Management **/

            ['name' => 'Add procurement cost', 'slug' => 'procurement-cost.create', 'model' => 'ProcurementCost', 'description' => 'Can add procurement cost', 'added_by' => 1],
            ['name' => 'Edit procurement cost', 'slug' => 'procurement-cost.edit', 'model' => 'ProcurementCost', 'description' => 'Can edit procurement cost', 'added_by' => 1],
            ['name' => 'View procurement cost', 'slug' => 'procurement-cost.view', 'model' => 'ProcurementCost', 'description' => 'Can view procurement cost', 'added_by' => 1],
            ['name' => 'Delete procurement cost', 'slug' => 'procurement-cost.delete', 'model' => 'ProcurementCost', 'description' => 'Can delete procurement cost', 'added_by' => 1],
            ['name' => 'Active/Inactive procurement cost', 'slug' => 'procurement-cost.activeinactive', 'model' => 'ProcurementCost', 'description' => 'Can activate or inactive procurement cost', 'added_by' => 1],

            ['name' => 'Manage payment for delivery', 'slug' => 'payment-for-delivery.view', 'model' => 'PaymentForDelivery', 'description' => 'Can manage payment for delivery', 'added_by' => 1],

            /** Price Management **/

            /** Sales & Leads Management **/

            ['name' => 'Add sales orders', 'slug' => 'sales-orders.create', 'model' => 'SalesOrder', 'description' => 'Can add sales orders', 'added_by' => 1],
            // ['name' => 'Edit sales orders', 'slug' => 'sales-orders.edit', 'model' => 'SalesOrder', 'description' => 'Can edit sales orders', 'added_by' => 1],
            ['name' => 'View sales orders', 'slug' => 'sales-orders.view', 'model' => 'SalesOrder', 'description' => 'Can view sales orders', 'added_by' => 1],
            ['name' => 'Delete sales orders', 'slug' => 'sales-orders.delete', 'model' => 'SalesOrder', 'description' => 'Can delete sales orders', 'added_by' => 1],
            ['name' => 'Access to the filter', 'slug' => 'sales-orders.accessfilter', 'model' => 'SalesOrder', 'description' => 'Can access filter sales orders', 'added_by' => 1],
            ['name' => 'Confirm sales orders', 'slug' => 'sales-orders.confirm', 'model' => 'SalesOrder', 'description' => 'Can confirm sales orders', 'added_by' => 1],

            ['name' => 'Add sales order status', 'slug' => 'sales-order-status.create', 'model' => 'SalesOrderStatus', 'description' => 'Can add sales order status', 'added_by' => 1],
            ['name' => 'Edit sales order status', 'slug' => 'sales-order-status.edit', 'model' => 'SalesOrderStatus', 'description' => 'Can edit sales order status', 'added_by' => 1],
            ['name' => 'View sales order status', 'slug' => 'sales-order-status.view', 'model' => 'SalesOrderStatus', 'description' => 'Can view sales order status', 'added_by' => 1],
            ['name' => 'Delete sales order status', 'slug' => 'sales-order-status.delete', 'model' => 'SalesOrderStatus', 'description' => 'Can delete sales order status', 'added_by' => 1],

            ['name' => 'View orders to delivery', 'slug' => 'orders-to-deliver.view', 'model' => 'OrdersToDeliver', 'description' => 'Can view to be delivered orders', 'added_by' => 1],

            /** Sales & Leads Management **/

            /** Reports **/

            ['name' => 'View stock report', 'slug' => 'stock-report.view', 'model' => 'Report', 'description' => 'Can view stock report', 'added_by' => 1],
            // ['name' => 'View ledger report', 'slug' => 'ledger-report.view', 'model' => 'Report', 'description' => 'Can view ledger report', 'added_by' => 1],

            ['name' => 'View seller report', 'slug' => 'financial-seller-report.view', 'model' => 'FinancialReport', 'description' => 'Can view financial seller report', 'added_by' => 1],
            ['name' => 'View driver report', 'slug' => 'financial-driver-report.view', 'model' => 'FinancialReport', 'description' => 'Can view financial driver report', 'added_by' => 1],
            /** Reports **/

            ['name' => 'View contact us', 'slug' => 'contactus.view', 'model' => 'Contact Us', 'description' => 'Can view contact us', 'added_by' => 1],

            /* information pages */
            ['name' => 'Add Information', 'slug' => 'information.create', 'model' => 'Information', 'description' => 'Can add Information', 'added_by' => 1],
            ['name' => 'Edit Information', 'slug' => 'information.edit', 'model' => 'Information', 'description' => 'Can edit Information', 'added_by' => 1],
            ['name' => 'View Information', 'slug' => 'information.view', 'model' => 'Information', 'description' => 'Can view Information', 'added_by' => 1],
            ['name' => 'Delete Information', 'slug' => 'information.delete', 'model' => 'Information', 'description' => 'Can delete Information', 'added_by' => 1],
            ['name' => 'Active/Inactive Information', 'slug' => 'information.activeinactive', 'model' => 'Information', 'description' => 'Can activate or inactive Information', 'added_by' => 1],

             /* sliders pages */
            ['name' => 'Add Sliders', 'slug' => 'sliders.create', 'model' => 'Sliders', 'description' => 'Can add Sliders', 'added_by' => 1],
            ['name' => 'Edit Sliders', 'slug' => 'sliders.edit', 'model' => 'Sliders', 'description' => 'Can edit Sliders', 'added_by' => 1],
            ['name' => 'View Sliders', 'slug' => 'sliders.view', 'model' => 'Sliders', 'description' => 'Can view Sliders', 'added_by' => 1],
            ['name' => 'Delete Sliders', 'slug' => 'sliders.delete', 'model' => 'Sliders', 'description' => 'Can delete Sliders', 'added_by' => 1],
            ['name' => 'Active/Inactive Sliders', 'slug' => 'sliders.activeinactive', 'model' => 'Sliders', 'description' => 'Can activate or inactive Sliders', 'added_by' => 1],

            /* brands */
            ['name' => 'Add Brands', 'slug' => 'brands.create', 'model' => 'Brands', 'description' => 'Can add Brands', 'added_by' => 1],
            ['name' => 'Edit Brands', 'slug' => 'brands.edit', 'model' => 'Brands', 'description' => 'Can edit Brands', 'added_by' => 1],
            ['name' => 'View Brands', 'slug' => 'brands.view', 'model' => 'Brands', 'description' => 'Can view Brands', 'added_by' => 1],
            ['name' => 'Delete Brands', 'slug' => 'brands.delete', 'model' => 'Brands', 'description' => 'Can delete Brands', 'added_by' => 1],
            ['name' => 'Active/Inactive Brands', 'slug' => 'brands.activeinactive', 'model' => 'Brands', 'description' => 'Can activate or inactive Brands', 'added_by' => 1],

            /* gifts */
            ['name' => 'Add Gifts', 'slug' => 'gifts.create', 'model' => 'Gifts', 'description' => 'Can add Gifts', 'added_by' => 1],
            ['name' => 'Edit Gifts', 'slug' => 'gifts.edit', 'model' => 'Gifts', 'description' => 'Can edit Gifts', 'added_by' => 1],
            ['name' => 'View Gifts', 'slug' => 'gifts.view', 'model' => 'Gifts', 'description' => 'Can view Gifts', 'added_by' => 1],
            ['name' => 'Delete Gifts', 'slug' => 'gifts.delete', 'model' => 'Gifts', 'description' => 'Can delete Gifts', 'added_by' => 1],
            ['name' => 'Active/Inactive Gifts', 'slug' => 'gifts.activeinactive', 'model' => 'Gifts', 'description' => 'Can activate or inactive Gifts', 'added_by' => 1],

            /** Task Status **/
            ['name' => 'Add task status', 'slug' => 'task-status.create', 'model' => 'MoldcellCallHistory', 'description' => 'Can add task status', 'added_by' => 1],
            ['name' => 'Edit task status', 'slug' => 'task-status.edit', 'model' => 'MoldcellCallHistory', 'description' => 'Can edit task status', 'added_by' => 1],
            ['name' => 'View task status', 'slug' => 'task-status.view', 'model' => 'MoldcellCallHistory', 'description' => 'Can view task status', 'added_by' => 1],
            ['name' => 'Delete task status', 'slug' => 'task-status.delete', 'model' => 'MoldcellCallHistory', 'description' => 'Can delete task status', 'added_by' => 1],
            /** Task Status **/
        ];

        $toBeDeleted = $toBeRestored = [];

        foreach ($permissions as $permission) {
            if ($exists = Permission::where('slug', $permission['slug'])->onlyTrashed()->exists()) {
                $toBeRestored[] = $exists;
            }

            $toBeDeleted[] = Permission::firstOrCreate(['slug' => $permission['slug']], $permission)->id;

        }

        if (!empty($toBeDeleted)) {
            Permission::whereNotIn('id', $toBeDeleted)->delete();
        }

        if (!empty($toBeRestored)) {
            Permission::whereIn('id', $toBeRestored)->restore();
        }
    }
}
