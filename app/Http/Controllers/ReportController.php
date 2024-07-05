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
                $moduleName = 'Financial Report';
                return view('reports.driver-ledger', compact('moduleName'));
            }
    
            $total = 0;
    
            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("voucher, users.name as user, amount, transaction_type")
            ->whereIn('transactions.amount_type', [0, 2])
            ->where('transactions.user_id', '=', auth()->user()->id);
    
            foreach ($ledger->get() as $data) {    
                if ($data->transaction_type) {
                    $total += $data->amount;
                } else {
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
            ->editColumn('crdr', function ($row) {
                if ($row->transaction_type) {
                    return '<span class="text-danger"> -' . $row->amount . ' </span>';
                } else {
                    return '<span class="text-success"> +' . $row->amount . ' </span>';
                }
            })
            ->with(['bl' => Helper::currency(abs($total))])
            ->rawColumns(['voucher', 'crdr'])
            ->toJson();

        } else if (User::isAdmin()) {

            if (!$request->ajax()) {
                $moduleName = 'Driver Report';
                return view('reports.driver-commission', compact('moduleName'));
            }
    
            $users = [];

            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("users.name as driver_info, users.id as userid")
            ->whereIn('transactions.amount_type', [2])
            ->groupBy('transactions.user_id');

            if (isset($request->search['value']) && !empty($request->search['value'])) {
                $ledger = $ledger->where('users.name', 'LIKE', '%' . trim($request->search['value']) . '%');
            }

            foreach ($ledger->get() as $thisIterator) {

                $tr = Transaction::where('user_id', $thisIterator->userid)
                ->select('transaction_type', 'amount')
                ->whereIn('transactions.amount_type', [0, 2])
                ->get()
                ->toArray();
    
                $rem = 0;
    
                foreach ($tr as $transact) {
                    if ($transact['transaction_type']) {
                        $rem += $transact['amount'];
                    } else {
                        $rem -= $transact['amount'];
                    }
                }

                if (abs($rem) > 0) {
                    $users[] = $thisIterator->userid;
                }
    
            }

            if (!empty($users)) {
                $ledger = $ledger->whereIn('users.id', $users);
            } else {
                $ledger = $ledger->whereNull('users.id');
            }

            return dataTables()->eloquent($ledger)
            ->addColumn('driver_amount', function ($row) {
                $transaction = Transaction::where('user_id', $row->userid)
                ->select('transaction_type', 'amount')
                ->whereIn('transactions.amount_type', [0, 2])
                ->get()->toArray();

                $rem = 0;

                foreach ($transaction as $transact) {
                    if ($transact['transaction_type']) {
                        $rem += $transact['amount'];
                    } else {
                        $rem -= $transact['amount'];
                    }
                }

                return Helper::currency(abs($rem));
            })

            ->addColumn('total', function ($row) {
                $transaction = Transaction::where('user_id', $row->userid)
                ->select('transaction_type', 'amount')
                ->whereIn('transactions.amount_type', [2])
                ->sum('amount');

                return Helper::currency(abs($transaction));
            })

            ->addColumn('paid', function ($row) {
                $transaction = Transaction::where('user_id', $row->userid)
                ->select('transaction_type', 'amount')
                ->whereIn('transactions.amount_type', [0])
                ->sum('amount');

                return Helper::currency(abs($transaction));
            })

            ->toJson();
        } else {
            abort(404);
        }
    }

    public function sellerCommission(Request $request) {

        if (User::isAdmin()) {
         
            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("users.name as seller_info, users.id as userid")
            ->whereIn('transactions.amount_type', [3, 0])
            ->groupBy('transactions.user_id')
            ->seller();

            $users = [];

            foreach ($ledger->get() as $thisIterator) {

                $tr = Transaction::where('user_id', $thisIterator->userid)
                ->select('transaction_type', 'amount')
                ->where('transactions.amount_type', 0)
                ->get()
                ->toArray();

                $rem = 0;

                foreach ($tr as $transact) {
                    if ($transact['transaction_type']) {
                        $rem += $transact['amount'];
                    } else {
                        $rem -= $transact['amount'];
                    }
                }

                $rem = Transaction::where('user_id', $thisIterator->userid)->where('transactions.amount_type', 3)->debit()->sum('amount') - abs($rem);

                if ($rem > 0) {
                    $users[] = $thisIterator->userid;
                }

            }

            if (!$request->ajax()) {
                $moduleName = 'Seller Report';
                $sellers = SellerWallet::join('users','users.id', '=', 'wallets.seller_id')->selectRaw('wallets.seller_id as id, users.name as name, users.email as email')
                            ->when(!empty($users), fn ($builder) => ($builder->whereIn('users.id', $users)))
                            ->groupBy('wallets.seller_id')
                            ->get()
                            ->toArray();

                return view('reports.seller-commission', compact('moduleName', 'sellers'));
            }

            if (isset($request->search['value']) && !empty($request->search['value'])) {
                $ledger = $ledger->where('users.name', 'LIKE', '%' . trim($request->search['value']) . '%');
            }

            if (!empty($users)) {
                $ledger = $ledger->whereIn('users.id', $users);
            } else {
                $ledger = $ledger->whereNull('users.id');
            }

            return dataTables()->eloquent($ledger)
            ->addColumn('seller_amount', function ($row) {
                $transaction = Transaction::where('user_id', $row->userid)
                ->select('transaction_type', 'amount')
                ->where('transactions.amount_type', 0)
                ->get()
                ->toArray();

                $rem = 0;

                foreach ($transaction as $transact) {
                    if ($transact['transaction_type']) {
                        $rem += $transact['amount'];
                    } else {
                        $rem -= $transact['amount'];
                    }
                }

                $rem = Transaction::where('user_id', $row->userid)->where('transactions.amount_type', 3)->debit()->sum('amount') - abs($rem);

                return Helper::currency($rem);

            })

            ->addColumn('total', function ($row) {

                $transaction = Transaction::where('user_id', $row->userid)
                ->select('transaction_type', 'amount')
                ->where('transactions.amount_type', 3)
                ->sum('amount');

                return Helper::currency($transaction);

            })

            ->addColumn('paid', function ($row) {

                $transaction = Transaction::where('user_id', $row->userid)
                ->select('transaction_type', 'amount')
                ->where('transactions.amount_type', 0)
                ->get()
                ->toArray();

                $rem = 0;

                foreach ($transaction as $transact) {
                    if ($transact['transaction_type']) {
                        $rem += $transact['amount'];
                    } else {
                        $rem -= $transact['amount'];
                    }
                }

                return Helper::currency(abs($rem));

            })

            ->toJson();

        } else if (auth()->user()->hasPermission('sales-orders.view')) {

            if (!$request->ajax()) {
                $moduleName = 'Financial Report';
                return view('reports.seller-ledger', compact('moduleName'));
            }

            $total = 0;

            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("voucher, users.name as user, amount, transaction_type, amount_type")
            ->whereIn('transactions.amount_type', [3, 0])
            ->where('transactions.user_id', '=', auth()->user()->id);

            $skip = true;

            if ($ledger->count() == 1) {
                $total = $ledger->first()->amount ?? 0;
            } else {
                foreach ($ledger->get() as $data) {
                    if ($data->amount_type == 3 && $data->transaction_type == 1 && $skip) {
                        $skip = false;
                        continue;
                    }
    
                    if ($data->transaction_type) {
                        $total -= $data->amount;
                    } else {
                        $total += $data->amount;
                    }
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
            ->editColumn('crdr', function ($row){
                if ($row->transaction_type) {
                    return '<span class="text-danger"> -' . $row->amount . ' </span>';
                } else {
                    return '<span class="text-success"> +' . $row->amount . ' </span>';
                }
            })
            ->with(['bl' => Helper::currency($total)])
            ->rawColumns(['voucher', 'crdr'])
            ->toJson();

        } else {
            abort(404);
        }
    }

    public function payAmountToAdmin(Request $request) {

        $credit = Transaction::whereIn('amount_type', [0, 2])->where('user_id', auth()->user()->id)->credit()->sum('amount');
        $debit = Transaction::whereIn('amount_type', [0, 2])->where('user_id', auth()->user()->id)->debit()->sum('amount');

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
                    'amount_type' => 0,
                    'transaction_type' => 1,
                    'user_id' => auth()->user()->id,
                    'voucher' => 'Payment done',
                    'amount' => $request->amount,
                    'year' => Helper::$financialYear,
                    'added_by' => auth()->user()->id
                ]);

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_type' => 0,
                    'user_id' => 1,
                    'attachments' => $attachmentJson,
                    'voucher' => 'Payment received',
                    'amount' => $request->amount,
                    'year' => Helper::$financialYear,
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

        $credit = Transaction::whereIn('amount_type', [0])->where('user_id', 1)->credit()->sum('amount');
        $debit = Transaction::whereIn('amount_type', [0])->where('user_id', 1)->debit()->sum('amount');

        $remaining = $credit - $debit;

        if ($remaining == 0 || (is_numeric($request->amount) && $remaining < $request->amount)) {
            return back()->with('error', 'You have insufficient balance in your account.');
        }

        if (is_numeric($request->amount)) {
            
            DB::beginTransaction();

            try {

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_type' => 1,
                    'user_id' => 1,
                    'voucher' => 'Payment done',
                    'amount' => $request->amount,
                    'year' => Helper::$financialYear,
                    'added_by' => auth()->user()->id
                ]);

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_type' => 0,
                    'user_id' => $request->seller,
                    'voucher' => 'Payment received',
                    'amount' => $request->amount,
                    'year' => Helper::$financialYear,
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
