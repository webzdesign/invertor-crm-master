<?php

namespace App\Http\Controllers;

use App\Models\{DriverWallet, Stock, User, Wallet as SellerWallet, Transaction, SalesOrder};
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

        if (User::isDriver()) {

            if (!$request->ajax()) {
                $moduleName = 'Driver Report';
                return view('reports.driver-ledger', compact('moduleName'));
            }
    
            $total = $credit = $debit = $temp = 0;
    
            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("voucher, users.name as user, amount, transaction_type")
            ->where('transactions.form_id', 1)
            ->whereIn('transactions.ledger_type', [0, 1])
            ->where('transactions.user_id', '=', auth()->user()->id);
    
            foreach ($ledger->get() as $data) {
    
                if ($data->transaction_type) {
                    $debit += $data->amount;
                    $total += $data->amount;
                } else {
                    $credit += $data->amount;
                    $total -= $data->amount;
                }
            }
    
            return dataTables()->eloquent($ledger)
            ->addColumn('voucher', function ($row) {
                if (str_contains($row->voucher, 'SO-')) {
                    $order = SalesOrder::where('order_no', $row->voucher)->first();
                    if ($order != null) {
                        return '<a target="_blank" href="' . route('sales-orders.view', encrypt($order->id)) . '"> ' . ($order->order_no) . '</a>';
                    }
                }
    
                return $row->voucher;
            })
            ->addColumn('cr', function ($row) {
                if ($row->transaction_type) {
                    return '-';
                } else {
                    return $row->amount;
                }
            })
            ->addColumn('dr', function ($row) {
                if ($row->transaction_type) {
                    return $row->amount;
                } else {
                    return '-';
                }
            })
            ->editColumn('amount', function ($row) use (&$temp){
                if ($row->transaction_type) {
                    $temp += $row->amount;
                } else {
                    $temp -= $row->amount;
                }
    
                return abs($temp);
            })
            ->with(['cr' => abs($credit), 'dr' => abs($debit), 'bl' => abs($total)])
            ->rawColumns(['voucher'])
            ->toJson();

        } else if (User::isAdmin()) {

            if (!$request->ajax()) {
                $moduleName = 'Driver Report';
                return view('reports.driver-commission', compact('moduleName'));
            }
    
            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("users.name as driver_info, users.id as userid")
            ->where('transactions.user_id', '!=', 1)
            ->whereIn('transactions.ledger_type', [0, 1])
            ->groupBy('transactions.user_id');

            return dataTables()->of($ledger)
            ->addColumn('driver_amount', function ($row) {
                $transaction = Transaction::where('user_id', $row->userid)->where('form_id', 1)
                ->select('transaction_type', 'amount')
                ->get()->toArray();

                $rem = 0;

                foreach ($transaction as $transact) {
                    if ($transact['transaction_type']) {
                        $rem += $transact['amount'];
                    } else {
                        $rem -= $transact['amount'];
                    }
                }

                return abs($rem);

            })
            ->toJson();
        } else {
            abort(404);
        }
    }

    public function sellerCommission(Request $request) {

        if (User::isAdmin()) {
         
            if (!$request->ajax()) {
                $moduleName = 'Seller Report';
                $sellers = SellerWallet::join('users','users.id', '=', 'wallets.seller_id')->selectRaw('wallets.seller_id as id, users.name as name, users.email as email')->groupBy('wallets.seller_id')->get()->toArray();

                return view('reports.seller-commission', compact('moduleName', 'sellers'));
            }

            $sellerWallet = SellerWallet::join('users', 'users.id', '=', 'wallets.seller_id')
            ->join('sales_orders', 'sales_orders.id', '=', 'wallets.form_record_id')
            ->selectRaw("SUM(wallets.commission_amount) as seller_amount, users.name as seller_info, users.id as uid")
            ->where('wallets.form', 1)
            ->whereNotNull('wallets.seller_id')
            ->where('wallets.seller_id', '!=', '')
            ->groupBy('wallets.seller_id');

            if (isset($request->search['value']) && !empty($request->search['value'])) {
                $sellerWallet = $sellerWallet->where('users.name', 'LIKE', '%' . trim($request->search['value']) . '%');
            }

            return dataTables()->of($sellerWallet)
            ->editColumn('seller_amount', function ($row) {
                $remaining = $row->seller_amount;

                $cr = Transaction::where('form_id', 1)->credit()->where('user_id', $row->uid)->sum('amount');
                $dr = Transaction::where('form_id', 1)->debit()->where('user_id', $row->uid)->sum('amount');

                return $remaining - ($cr - $dr);
            })
            ->toJson();

        } else if (auth()->user()->hasPermission('sales-orders.view')) {

            if (!$request->ajax()) {
                $moduleName = 'Seller Report';
                return view('reports.seller-ledger', compact('moduleName'));
            }

            $total = $credit = $debit = $temp = 0;

            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("voucher, users.name as user, amount, transaction_type")
            ->where('transactions.form_id', 1)
            ->where('transactions.ledger_type', 2)
            ->where('transactions.user_id', '=', auth()->user()->id);

            foreach ($ledger->get() as $data) {
    
                if ($data->transaction_type) {
                    $debit += $data->amount;
                    $total += $data->amount;
                } else {
                    $credit += $data->amount;
                    $total -= $data->amount;
                }
            }

            return dataTables()->eloquent($ledger)
            ->addColumn('voucher', function ($row) {
                if (str_contains($row->voucher, 'SO-')) {
                    $order = SalesOrder::where('order_no', $row->voucher)->first();
                    if ($order != null) {
                        return '<a target="_blank" href="' . route('sales-orders.view', encrypt($order->id)) . '"> ' . ($order->order_no) . '</a>';
                    }
                }
    
                return $row->voucher;
            })
            ->addColumn('cr', function ($row) {
                if ($row->transaction_type) {
                    return '-';
                } else {
                    return $row->amount;
                }
            })
            ->addColumn('dr', function ($row) {
                if ($row->transaction_type) {
                    return $row->amount;
                } else {
                    return '-';
                }
            })
            ->editColumn('amount', function ($row) use (&$temp){
                if ($row->transaction_type) {
                    $temp += $row->amount;
                } else {
                    $temp -= $row->amount;
                }
    
                return abs($temp);
            })
            ->with(['cr' => abs($credit), 'dr' => abs($debit), 'bl' => abs($total)])
            ->rawColumns(['voucher'])
            ->toJson();

        } else {
            abort(404);
        }
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
                    'transaction_type' => 1,
                    'user_id' => auth()->user()->id,
                    'ledger_type' => 1,
                    'voucher' => 'DRIVER TO ADMIN',
                    'amount' => $request->amount,
                    'year' => '2024-25',
                    'added_by' => auth()->user()->id
                ]);

                Transaction::create([
                    'form_id' => 1, //Sales Order
                    'transaction_id' => Helper::hash(),
                    'transaction_type' => 0,
                    'user_id' => 1,
                    'ledger_type' => 1,
                    'attachments' => $attachmentJson,
                    'voucher' => 'DRIVER TO ADMIN',
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

    public function payAmountToSeller(Request $request) {

        $credit = Transaction::where('form_id', 1)->where('user_id', 1)->credit()->sum('amount');
        $debit = Transaction::where('form_id', 1)->where('user_id', 1)->debit()->sum('amount');

        $remaining = $credit - $debit;

        if ($remaining == 0 || (is_numeric($request->amount) && $remaining < $request->amount)) {
            return back()->with('error', 'You have insufficient balance in your account.');
        }

        if (is_numeric($request->amount)) {
            
            DB::beginTransaction();

            try {

                Transaction::create([
                    'form_id' => 1, //Sales Order
                    'transaction_id' => Helper::hash(),
                    'transaction_type' => 1,
                    'user_id' => 1,
                    'ledger_type' => 2,
                    'voucher' => 'ADMIN TO SELLER',
                    'amount' => $request->amount,
                    'year' => '2024-25',
                    'added_by' => auth()->user()->id
                ]);

                Transaction::create([
                    'form_id' => 1, //Sales Order
                    'transaction_id' => Helper::hash(),
                    'transaction_type' => 0,
                    'user_id' => $request->seller,
                    'ledger_type' => 2,
                    'voucher' => 'ADMIN TO SELLER',
                    'amount' => $request->amount,
                    'year' => '2024-25',
                    'added_by' => auth()->user()->id
                ]);

                DB::commit();
                return back()->with('success', Helper::currency($request->amount) . " paid to seller successfully.");
            } catch (\Exception $e) {

                DB::rollback();

                return back()->with('error', Helper::$errorMessage);
            }

        } else {
            return back()->with('error', 'Please enter valid amount.');
        }
    }
}
