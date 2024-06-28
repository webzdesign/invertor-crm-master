<?php

namespace App\Http\Controllers;

use App\Models\Wallet as SellerWallet;
use App\Models\DriverWallet;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Stock;
use App\Models\User;

class ReportController extends Controller
{
    public function stockReport(Request $request) {
        $total = 0;

        $drivers = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 3);
        })->selectRaw("CONCAT(users.name, ' - (', users.email, ')') as name, users.id as id");

        if (!$request->ajax()) {
            $moduleName = 'Stock Report';
            $drivers = $drivers->pluck('name', 'id')->toArray();
            $products = Stock::with('product')->whereNotNull('product_id')->where('product_id', '!=', '')->groupBy('product_id')->get();
            $types = [ '1' => 'Storage', '2' => 'Driver'];

            return view('reports.stock', compact('moduleName', 'drivers', 'types', 'products'));
        }

        $stock = Helper::getAvailableStockFromStorage();
        $storageStock = collect($stock)->map(function ($val, $key) {
            return ['product_id' => $key, 'qty' => $val, 'type' => 'Storage'];
        })->filter()->values();

        $driverStock = [];

        if ($request->has('filterDriver') && !empty(trim($request->filterDriver))) {
            $request->filterType = '2';
            $drivers = $drivers->where('id', $request->filterDriver)->pluck('name', 'id')->toArray();
        } else {
            $drivers = $drivers->pluck('name', 'id')->toArray();
        }

        foreach ($drivers as $id => $value) {
            $temp = collect(Helper::getAvailableStockFromDriver($id))->map(function ($val, $key) use ($value) {
                return ['product_id' => $key, 'qty' => $val, 'type' => $value];
            })->filter()->values()->toArray();

            foreach ($temp as $ele) {
                $driverStock[] = $ele;
            }
        }

        if ($request->filterType == '1') {
            $stock = collect($storageStock);
        } else if ($request->filterType == '2') {
            $stock = collect($driverStock);
        } else {
            $stock = collect($storageStock)->merge($driverStock);
        }

        if ($request->has('filterProduct') && !empty(trim($request->filterProduct))) {
            $stock = $stock->where('product_id', $request->filterProduct);
        }

        foreach ($stock as $qty) {
            $total += $qty['qty'];
        }

        return dataTables()->of($stock)
        ->editColumn('product_id', function ($row) {
            return Helper::productName($row['product_id']);
        })
        ->editColumn('qty', function ($row) {
            return round($row['qty']);
        })
        ->with(['total' => $total])
        ->toJson();
    }

    public function ledgerReport(Request $request) {

        if (!$request->ajax()) {
            $moduleName = 'Ledger Report';
            $users = User::selectRaw("CONCAT(roles.name, ' - ', users.name, ' (',  users.email, ')' ) as name, users.id as id")
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.id', [2,3,6])
            ->pluck('name', 'id')
            ->toArray();

            return view('reports.ledger', compact('moduleName', 'users'));
        }

        $driverWallet = DriverWallet::selectRaw('driver_wallets.driver_id as user_id, driver_wallets.amount, driver_wallets.created_at as date, sales_orders.id as orderid, sales_orders.order_no as order_id')->join('sales_orders', 'sales_orders.id', '=', 'driver_wallets.so_id')->get()->toArray();
        $sellerWallet = SellerWallet::selectRaw('wallets.seller_id as user_id, wallets.commission_amount as amount, wallets.created_at as date, sales_orders.id as orderid, sales_orders.order_no as order_id')->join('sales_orders', 'sales_orders.id', '=', 'wallets.form_record_id')->where('form', 1)->get()->toArray();

        $wallet = collect($driverWallet)->merge($sellerWallet);
        $total = 0;

        if (!empty($request->user) && is_numeric($request->user)) {
            $wallet = $wallet->where('user_id', $request->user);
        }

        foreach ($wallet as $amount) {
            $total += $amount['amount'];            
        }

        return dataTables()->collection($wallet)
        ->addColumn('user', function ($row) {
            return Helper::userName($row['user_id']);
        })
        ->addColumn('order', function ($row) {
            return '<a target="_blank" href="' . route('sales-orders.view', encrypt($row['orderid'])) . '"> ' . $row['order_id'] . '</a>';
        })
        ->addColumn('date', function ($row) {
            return date($row['date'], strtotime('d-m-Y H:i'));
        })
        ->addColumn('credit', function ($row) {
            return Helper::currency($row['amount']);
        })
        ->rawColumns(['order'])
        ->with(['total' => Helper::currency($total)])
        ->toJson();

    }
}
