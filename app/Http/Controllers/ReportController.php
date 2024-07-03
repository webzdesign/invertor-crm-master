<?php

namespace App\Http\Controllers;

use App\Models\{DriverWallet, Stock, User, Wallet as SellerWallet, Transaction};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;

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
        return abort(404);
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

        $wallet = collect($driverWallet)->merge($sellerWallet)->sortBy('order_id');
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

    public function driverCommission(Request $request) {
        if (!(User::isAdmin() || User::isDriver())) {
            abort(403);
        }

        $total = 0;

        if (!$request->ajax()) {
            $moduleName = 'Driver';
            return view('reports.driver-commission', compact('moduleName'));
        }

        if (User::isAdmin()) {
            $driverWallet = DriverWallet::join('users', 'users.id', '=', 'driver_wallets.driver_id')
            ->join('sales_orders', 'sales_orders.id', '=', 'driver_wallets.so_id')
            ->selectRaw("SUM(driver_wallets.amount) as driver_amount, users.name as driver_info, SUM(driver_wallets.driver_receives) as driver_receives")
            ->groupBy('driver_wallets.driver_id');

            if (isset($request->search['value']) && !empty($request->search['value'])) {
                $driverWallet = $driverWallet->where('users.name', 'LIKE', '%' . trim($request->search['value']) . '%');
            }

        } else {
            $driverWallet = DriverWallet::join('sales_orders', 'sales_orders.id', '=', 'driver_wallets.so_id')
            ->selectRaw("driver_wallets.amount as driver_amount, sales_orders.order_no as driver_info, sales_orders.id as orderid")
            ->where('driver_wallets.driver_id', auth()->user()->id);
        }

        foreach ($driverWallet->get() as $dw) {
            $total += $dw['driver_amount'];
        }

        return dataTables()->of($driverWallet)

        ->editColumn('driver_info', function ($row) {
            if (User::isAdmin()) {
                return $row['driver_info'];
            } else {
                return '<a target="_blank" href="' . route('sales-orders.view', encrypt($row['orderid'])) . '"> ' . ($row['driver_info']) . '</a>';
            }
        })
        ->editColumn('driver_amount', fn ($row) => Helper::currency($row['driver_amount']))
        ->with(['total' => Helper::currency($total)])
        ->rawColumns(['driver_info'])
        ->toJson();
    }

    public function sellerCommission(Request $request) {
        $total = 0;

        if (!$request->ajax()) {
            $moduleName = 'Seller';
            return view('reports.seller-commission', compact('moduleName'));
        }

        if (User::isAdmin()) {
            $sellerWallet = SellerWallet::join('users', 'users.id', '=', 'wallets.seller_id')
            ->join('sales_orders', 'sales_orders.id', '=', 'wallets.form_record_id')
            ->selectRaw("SUM(wallets.commission_amount) as seller_amount, users.name as seller_info")
            ->where('wallets.form', 1)
            ->whereNotNull('wallets.seller_id')
            ->where('wallets.seller_id', '!=', '')
            ->groupBy('wallets.seller_id');

            if (isset($request->search['value']) && !empty($request->search['value'])) {
                $sellerWallet = $sellerWallet->where('users.name', 'LIKE', '%' . trim($request->search['value']) . '%');
            }

        } else {
            $sellerWallet = SellerWallet::join('sales_orders', 'sales_orders.id', '=', 'wallets.form_record_id')
            ->selectRaw("wallets.commission_amount as seller_amount, sales_orders.order_no as seller_info, sales_orders.id as orderid")
            ->where('wallets.form', 1)
            ->whereNotNull('wallets.seller_id')
            ->where('wallets.seller_id', auth()->user()->id)
            ->where('wallets.seller_id', '!=', '');
        }

        foreach ($sellerWallet->get() as $sw) {
            $total += $sw['seller_amount'];
        }

        return dataTables()->of($sellerWallet)

        ->editColumn('seller_info', function ($row) {
            if (User::isAdmin()) {
                return $row['seller_info'];
            } else {
                return '<a target="_blank" href="' . route('sales-orders.view', encrypt($row['orderid'])) . '"> ' . ($row['seller_info']) . '</a>';
            }
        })
        ->editColumn('seller_amount', fn ($row) => Helper::currency($row['seller_amount']))
        ->with(['total' => Helper::currency($total)])
        ->rawColumns(['seller_info'])
        ->toJson();
    }

    public function payAmountToAdmin(Request $request) {

        $credit = Transaction::where('form_id', 1)->where('user_id', auth()->user()->id)->credit()->sum('amount');
        $debit = Transaction::where('form_id', 1)->where('user_id', auth()->user()->id)->debit()->sum('amount');

        $remaining = $credit - $debit;

        if ($remaining == 0 || (is_numeric($request->amount) && $remaining < $request->amount)) {
            return back()->with('error', 'You have insufficient balance in your account.');
        }

        if (!file_exists(storage_path('app/public/payment-receipt'))) {
            mkdir(storage_path('app/public/payment-receipt'), 0777, true);
        }

        if (is_numeric($request->amount)) {

            $attachmentJson = [];
            
            DB::beginTransaction();

            try {

                if ($request->hasFile('file')) {
                    foreach ($request->file('file') as $file) {
                        $name = 'PAY-RECEIPT-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(storage_path('app/public/payment-receipt'), $name);

                        if (file_exists(storage_path("app/public/payment-receipt/{$name}"))) {
                            $attachmentJson[] = $name;
                        }
                    }
                }

                if (empty($attachmentJson)) {
                    $attachmentJson = null;
                } else {
                    $attachmentJson = json_encode($attachmentJson);
                }

                Transaction::create([
                    'form_id' => 1, //Sales Order
                    'transaction_id' => Helper::hash(),
                    'user_id' => 0,
                    'attachments' => $attachmentJson,
                    'voucher' => '',
                    'amount' => $request->amount,
                    'year' => '2024-25',
                    'added_by' => auth()->user()->id
                ]);

                DB::commit();
                return back()->with('success', Helper::currency($request->amount) . " paid to admin successfully.");
            } catch (\Exception $e) {


                if (is_array($attachmentJson) && !empty($attachmentJson)) {
                    foreach ($attachmentJson as $eachImage) {
                        if (file_exists(storage_path("app/public/payment-receipt/{$eachImage}"))) {
                            unlink(storage_path("app/public/payment-receipt/{$eachImage}"));
                        }
                    }
                }

                DB::rollback();

                Helper::logger($e->getMessage());

                return back()->with('error', Helper::$errorMessage);
            }

        } else {
            return back()->with('error', 'Please enter valid amount.');
        }
    }
}
