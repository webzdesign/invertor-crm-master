<?php

namespace App\Http\Controllers;

use App\Models\{BankDetail, Stock, User, Transaction, SalesOrder, CommissionWithdrawalHistory};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class ReportController extends Controller
{
    public function stockReport(Request $request) {
        $total = 0;

        $drivers = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 3);
        })->selectRaw("CONCAT(users.name, ' - (', users.email, ')') as name, users.id as id")
        ->when(User::isDriver(), fn ($builder) => ($builder->where('id', auth()->user()->id)));

        if (!$request->ajax()) {
            $moduleName = 'Stock Report';
            $drivers = $drivers->pluck('name', 'id')->toArray();
            $products = Stock::with('product')->whereNotNull('product_id')->where('product_id', '!=', '')->groupBy('product_id')->get();
            $types = [ '1' => 'Storage', '2' => 'Driver'];

            return view('reports.stock', compact('moduleName', 'drivers', 'types', 'products'));
        }

        $storageStock = $driverStock = [];

        if(!User::isDriver()) {
            $stock = Helper::getAvailableStockFromStorage();
            $storageStock = collect($stock)->map(function ($val, $key) {
                return ['product_id' => $key, 'qty' => $val, 'type' => 'Storage'];
            })->filter()->values();
    
            if ($request->has('filterDriver') && !empty(trim($request->filterDriver))) {
                $request->filterType = '2';
                $drivers = $drivers->where('id', $request->filterDriver)->pluck('name', 'id')->toArray();
            } else {
                $drivers = $drivers->pluck('name', 'id')->toArray();
            }
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

    public function driverCommission(Request $request) {

        if (User::isDriver()) {

            if (!$request->ajax()) {
                $moduleName = 'Financial Report';
                return view('reports.driver-ledger', compact('moduleName'));
            }
    
            $total = 0;
    
            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("voucher, users.name as user, amount, transaction_type, so_id, is_approved, amount_type")
            ->whereIn('transactions.amount_type', [0, 2])
            ->where('transactions.user_id', '=', auth()->user()->id);
    
            foreach ($ledger->get() as $data) {
                if ($data->is_approved == 1) {
                    if ($data->transaction_type) {
                        $total -= $data->amount;
                    } else {
                        $total += $data->amount;
                    }
                }
            }
    
            return dataTables()->eloquent($ledger)
            ->addColumn('voucher', function ($row) {
                if ($row->is_approved == 0 && $row->amount_type == 0) {
                    return "<span class='text-secondary'> Payment pending </span>";
                } else if ($row->is_approved == 1 && $row->amount_type == 0) {
                    return "<span class='text-success'> Payment accepted </span>";
                } else if ($row->is_approved == 2 && $row->amount_type == 0) {
                    return "<span class='text-danger'> Payment rejected </span>";
                } else {
                    if (str_contains($row->voucher, 'SO-')) {
                        $order = SalesOrder::where('order_no', $row->voucher)->first();
                        if ($order != null) {
                            return '<a target="_blank" href="' . route('sales-orders.view', encrypt($order->id)) . '"> ' . ($order->order_no) . '</a>';
                        }
                    }

                    return $row->voucher;
                }
            })
            ->editColumn('crdr', function ($row) {
                if ($row->is_approved == 0 && $row->amount_type == 0) {
                    return '<span class="text-secondary"> ' . $row->amount . ' </span>';
                } else if ($row->is_approved == 2 && $row->amount_type == 0) {
                    return '<span class="text-secondary"> ' . $row->amount . ' </span>';
                } else {
                    if ($row->transaction_type) {
                        return '<span class="text-danger"> -' . $row->amount . ' </span>';
                    } else {
                        return '<span class="text-success"> +' . $row->amount . ' </span>';
                    }
                }
            })
            ->with(['bl' => Helper::currency(abs($total))])
            ->rawColumns(['voucher', 'crdr', 'me'])
            ->toJson();

        } else if (User::isAdmin()) {

            if (!$request->ajax()) {
                $moduleName = 'Driver Report';
                $moduleName2 = 'Driver Payment Requests';
                return view('reports.driver-commission', compact('moduleName', 'moduleName2'));
            }
    
            $total = 0;

            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("users.name as driver_info, users.id as userid")
            ->whereIn('transactions.amount_type', [2])
            ->groupBy('transactions.user_id');

            if (isset($request->search['value']) && !empty($request->search['value'])) {
                $ledger = $ledger->where('users.name', 'LIKE', '%' . $request->search['value'] . '%');
            }

            foreach ($ledger->get() as $thisIterator) {

                $tr = Transaction::where('user_id', $thisIterator->userid)
                ->select('transaction_type', 'amount')
                ->where('transactions.is_approved', 1)
                ->whereIn('transactions.amount_type', [0, 2])
                ->orderBy('transaction_type', 'ASC')
                ->get()->toArray();

                $rem = 0;

                foreach ($tr as $t) {
                    if ($t['transaction_type']) {
                        $rem -= $t['amount'];
                    } else {
                        $rem += $t['amount'];
                    }
                }
    
                $total += $rem;
            }

            return dataTables()->eloquent($ledger)
            ->addColumn('driver_amount', function ($row) {
                $transaction = Transaction::where('user_id', $row->userid)
                ->select('transaction_type', 'amount')
                ->where('transactions.is_approved', 1)
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
            ->with(['total' => Helper::currency($total)])
            ->toJson();
        } else {
            abort(404);
        }
    }

    public function sellerCommission(Request $request) {

        if (User::isAdmin()) {

            if (!$request->ajax()) {
                $moduleName = 'Seller Report';
                $moduleName2 = 'Commission Withdrawal Requests';
                $sellers = CommissionWithdrawalHistory::with('user')->where('status', 0)->groupBy('user_id')->get();
                
                return view('reports.seller-commission', compact('moduleName', 'moduleName2', 'sellers'));
            }

            $total = 0;

            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("users.name as seller_info, users.id as userid")
            ->whereIn('transactions.amount_type', [3, 0])
            ->groupBy('transactions.user_id')
            ->seller();

            if (isset($request->search['value']) && !empty($request->search['value'])) {
                $ledger = $ledger->where('users.name', 'LIKE', '%' . $request->search['value'] . '%');
            }

            foreach ($ledger->get() as $thisIterator) {

                $tr = Transaction::where('user_id', $thisIterator->userid)
                ->select('transaction_type', 'amount')
                ->where('transactions.amount_type', 0)
                ->orderBy('transaction_type', 'ASC')
                ->get()->toArray();

                $rem = 0;

                foreach ($tr as $t) {
                    if ($t['transaction_type']) {
                        $rem -= $t['amount'];
                    } else {
                        $rem += $t['amount'];
                    }
                }

                $rem = Transaction::where('user_id', $thisIterator->userid)->where('transactions.amount_type', 3)->debit()->sum('amount') - $rem;
                
                $total += $rem;
            }

            return dataTables()->eloquent($ledger)
            ->addColumn('seller_amount', function ($row) {
                $transaction = Transaction::where('user_id', $row->userid)
                ->select('transaction_type', 'amount')
                ->where('transactions.amount_type', 0)
                ->orderBy('transaction_type', 'ASC')
                ->get()->toArray();

                $rem = 0;

                foreach ($transaction as $transact) {
                    if ($transact['transaction_type']) {
                        $rem -= $transact['amount'];
                    } else {
                        $rem += $transact['amount'];
                    }
                }

                $rem = Transaction::where('user_id', $row->userid)->where('transactions.amount_type', 3)->debit()->sum('amount') - $rem;

                return Helper::currency($rem);

            })
            ->with(['total' => Helper::currency($total)])
            ->toJson();

        } else if (auth()->user()->hasPermission('sales-orders.view')) {

            if (!$request->ajax()) {
                $moduleName = 'Financial Report';
                $moduleName2 = 'Commission Requests';
                $accounts = BankDetail::where('user_id', auth()->user()->id)->get();

                return view('reports.seller-ledger', compact('moduleName', 'moduleName2', 'accounts'));
            }

            $total = 0;

            $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
            ->selectRaw("voucher, users.name as user, amount, transaction_type, amount_type")
            ->whereIn('transactions.amount_type', [3, 0])
            ->where('transactions.user_id', '=', auth()->user()->id);

            foreach ($ledger->clone()->orderBy('transaction_type', 'ASC')->get() as $data) {
                if ($data->transaction_type) {
                    $total -= $data->amount;
                } else {
                    $total += $data->amount;
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

        $credit = Transaction::whereIn('amount_type', [0, 2])->where('is_approved', 1)->where('user_id', auth()->user()->id)->credit()->sum('amount');
        $debit = Transaction::whereIn('amount_type', [0, 2])->where('is_approved', 1)->where('user_id', auth()->user()->id)->debit()->sum('amount');

        $remaining = $credit - $debit;

        if ($remaining == 0 || (is_numeric($request->amount) && $remaining < $request->amount)) {
            return back()->with('error', 'You have insufficient balance in your account.');
        }

        if (!file_exists(storage_path('app/public/payment-receipt/driver'))) {
            mkdir(storage_path('app/public/payment-receipt/driver'), 0777, true);
        }

        if (is_numeric($request->amount)) {

            $attachmentJson = [];
            
            DB::beginTransaction();

            try {

                if ($request->hasFile('file')) {
                    foreach ($request->file('file') as $file) {
                        $name = 'PAY-RECEIPT-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(storage_path('app/public/payment-receipt/driver'), $name);

                        if (file_exists(storage_path("app/public/payment-receipt/driver/{$name}"))) {
                            $attachmentJson[] = $name;
                        }
                    }
                }

                if (empty($attachmentJson)) {
                    $attachmentJson = null;
                } else {
                    $attachmentJson = json_encode($attachmentJson);
                }

                $transactionUid = Helper::hash();

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_id' => $transactionUid,
                    'transaction_type' => 1,
                    'user_id' => auth()->user()->id,
                    'voucher' => 'Payment done',
                    'amount' => $request->amount,
                    'year' => Helper::$financialYear,
                    'added_by' => auth()->user()->id
                ]);

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_id' => $transactionUid,
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
                        if (file_exists(storage_path("app/public/payment-receipt/driver/{$eachImage}"))) {
                            unlink(storage_path("app/public/payment-receipt/driver/{$eachImage}"));
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

        if (!file_exists(storage_path('app/public/payment-receipt/seller'))) {
            mkdir(storage_path('app/public/payment-receipt/seller'), 0777, true);
        }

        if (is_numeric($request->amount)) {
            
            $attachmentJson = [];

            DB::beginTransaction();

            try {

                if ($request->hasFile('file')) {
                    foreach ($request->file('file') as $file) {
                        $name = 'PAY-RECEIPT-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(storage_path('app/public/payment-receipt/seller'), $name);

                        if (file_exists(storage_path("app/public/payment-receipt/seller/{$name}"))) {
                            $attachmentJson[] = $name;
                        }
                    }
                }

                if (empty($attachmentJson)) {
                    $attachmentJson = null;
                } else {
                    $attachmentJson = json_encode($attachmentJson);
                }

                $transactionUid = Helper::hash();

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_id' => $transactionUid,
                    'transaction_type' => 1,
                    'user_id' => 1,
                    'voucher' => 'Payment done',
                    'amount' => $request->amount,
                    'year' => Helper::$financialYear,
                    'added_by' => auth()->user()->id
                ]);

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_id' => $transactionUid,
                    'transaction_type' => 0,
                    'user_id' => $request->seller,
                    'voucher' => 'Payment received',
                    'attachments' => $attachmentJson,
                    'amount' => $request->amount,
                    'year' => Helper::$financialYear,
                    'added_by' => auth()->user()->id
                ]);

                DB::commit();
                return back()->with('success', Helper::currency($request->amount) . " paid to seller successfully.");
            } catch (\Exception $e) {

                if (is_array($attachmentJson) && !empty($attachmentJson)) {
                    foreach ($attachmentJson as $eachImage) {
                        if (file_exists(storage_path("app/public/payment-receipt/seller/{$eachImage}"))) {
                            unlink(storage_path("app/public/payment-receipt/seller/{$eachImage}"));
                        }
                    }
                }

                DB::rollback();

                return back()->with('error', Helper::$errorMessage);
            }

        } else {
            return back()->with('error', 'Please enter valid amount.');
        }
    }

    public function driverPaymentLog(Request $request) {
        $drivers = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
                ->selectRaw("transactions.amount, transactions.is_approved, transactions.id as id, transaction_id, transactions.created_at as date")
                ->where('amount_type', 0)
                ->where('user_id', 1)
                ->where('transaction_type', 0)
                ->orderBy('id', 'DESC');

        
        if (isset($request->search['value']) && !empty(trim($request->search['value']))) {
            $searchVal = trim($request->search['value']);

            $tmp = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
                ->select("transaction_id")
                ->where('amount_type', 0)
                ->where('users.name', 'LIKE', "%$searchVal%")
                ->whereIn('transaction_id', $drivers->clone()->pluck('transaction_id')->toArray())
                ->pluck('transaction_id')
                ->toArray();

            $tmp = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
                ->selectRaw("transactions.id as id")
                ->where('amount_type', 0)
                ->where('user_id', 1)
                ->whereIn('transaction_id', $tmp)
                ->pluck('id')
                ->toArray();

            $drivers = $drivers->where(function ($builder) use ($tmp, $searchVal) {
                $builder->whereIn('transactions.id', $tmp)
                ->orWhere('transactions.amount', 'LIKE', "%$searchVal%")
                ->orWhere(DB::raw("DATE_FORMAT(transactions.created_at, '%d-%m-%Y')"), 'LIKE', "%$searchVal%");

                if (str_contains('accepted', strtolower($searchVal))) {
                    $builder = $builder->orWhere('transactions.is_approved', 1);
                } else if (str_contains('rejected', strtolower($searchVal))) {
                    $builder = $builder->orWhere('transactions.is_approved', 2);
                }
            });          
        }

        return dataTables()->eloquent($drivers)
        ->addColumn('driver', function ($row) {
            return Transaction::with('user')->where('user_id', '!=', 1)->where('transaction_id', $row->transaction_id)->first()->user->name ?? '-';
        })
        ->addColumn('date', function ($row) {
            return date('d-m-Y', strtotime($row->date));
        })
        ->editColumn('amount', function ($row) {
            return Helper::currency($row->amount);
        })
        ->addColumn('proof', function ($row) {
            return '<button class="btn btn-success btn-sm show-proofs" data-id="' . $row->id . '"> <i class="fa fa-eye"> </i> </button>';
        })
        ->addColumn('action', function ($row) {

            if ($row->is_approved == 1) {
                return '<span class="text-success"> Accepted </span>';
            } else if ($row->is_approved == 2) {
                return '<span class="text-danger"> Rejected </span>';
            } else {
                return '<button class="accept-payment btn-primary f-500 f-14 btn-sm bg-success" data-id="' . $row->id . '"> ACCEPT </button>
                <button class="reject-payment btn-primary f-500 f-14 btn-sm bg-error" data-id="' . $row->id . '"> REJECT </button>';
            }
        })
        ->rawColumns(['action', 'proof'])
        ->toJson();
    }

    public function acceptOrRejectDriverPayment(Request $request, $type) {
        if (Transaction::where('id', $request->id)->exists()) {
            $id = Transaction::where('id', $request->id)->first()->transaction_id;
            if ($type == 'accept') {
                Transaction::where('transaction_id', $id)->update(['is_approved' => 1]);
                return response()->json(['status' => true, 'message' => 'Payment approved successfully.']);
            } else if ($type == 'reject') {
                Transaction::where('transaction_id', $id)->update(['is_approved' => 2]);
                return response()->json(['status' => true, 'message' => 'Payment rejected successfully.']);
            } else {
                return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
            }
        } else {
            return response()->json(['status' => false, 'message' => Helper::$notFound]);
        }
    }

    public function showDriverPaymentProofs(Request $request) {
        $transaction = Transaction::select('transaction_id')->where('id', $request->id)->first()->transaction_id ?? 0;
        $transaction = Transaction::select('attachments')->where('transaction_id', $transaction)->whereNotNull('attachments')->first();

        if ($transaction != null) {
            return response()->json(['status' => true, 'html' => view('reports.proofs', compact('transaction'))->render()]);
        } else {
            return response()->json(['status' => false, 'message' => 'No payment receipt uploaded for this transaction.']);
        }
    }

    public function ibanCheck(Request $request) {
        return response()->json(BankDetail::where('iban_number', $request->iban_add)->doesntExist());        
    }

    public function bankAccountSave(Request $request) {
        if (BankDetail::where('iban_number', $request->iban_add)->doesntExist()) {
            $id = BankDetail::create([
                'user_id' => auth()->user()->id,
                'name' => $request['bank_name_add'],
                'surname' => $request['suername_add'],
                'iban_number' => strtoupper($request['iban_add'])
            ])->id;

            return response()->json(['status' => $id, 'id' => $id, 'message' => 'Bank details saved successfully.']);
        }

        return response()->json(['status' => false, 'message' => 'Invalid IBAN Number']);
    }

    public function bankAccountDelete(Request $request) {
        $account = BankDetail::where('id', $request->id);

        if ($account->exists()) {
            return response()->json(['status' => $account->delete(), 'message' => 'Bank account deleted successfully.']);
        }
 
        return response()->json(['status' => false, 'message' => Helper::$notFound]);
    }

    public function withdrawableAmount(Request $request) {

        $transactions = Transaction::with(['order' => fn ($builder) => $builder->withTrashed()])
                        ->where('user_id', auth()->user()->id)
                        ->where('amount_type', 3)
                        ->whereIn('withdrawal_request', [0, 3])
                        ->orderBy('created_at', 'ASC')
                        ->get();

        return response()->json(['status' => true, 'orders' => count($transactions), 'html' => view('reports.withdrawal-modal', compact('transactions'))->render()]);
    }

    public function withdrawalRequest(Request $request) {

        $this->validate($request,[
            'bank' => 'required',
            'orders' => 'required',
            'transactions' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'amount' => 'required'
        ]);

        DB::beginTransaction();

        try {
            CommissionWithdrawalHistory::create([
                'bank_id' => $request->bank,
                'user_id' => auth()->user()->id,
                'orders' => json_encode($request->orders),
                'from' => $request->from_date,
                'to' => $request->to_date,
                'amount' => array_sum($request->amount)
            ]);
    
            Transaction::whereIn('id', $request->transactions)->update(['withdrawal_request' => 1]);

            DB::commit();
            return redirect()->back()->with('success', 'Amount withdrawal request sent successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', Helper::$errorMessage);
        }
    }

    public function withdrawReqs(Request $request) {
        $reqs = CommissionWithdrawalHistory::join('users', 'users.id', '=', 'commission_withdrawal_histories.user_id')
        ->selectRaw("users.name as name, commission_withdrawal_histories.amount, commission_withdrawal_histories.status, commission_withdrawal_histories.id as id, commission_withdrawal_histories.created_at as date")
        ->where('commission_withdrawal_histories.status', 0)
        ->orderBy('id', 'DESC');

        if (isset($request->search['value']) && !empty($request->search['value'])) {
            $searchVal = $request->search['value'];

            $users = User::select('id')->where('name', 'LIKE', "%$searchVal%")->pluck('id')->toArray();

            $reqs = $reqs->where(function ($builder) use($users, $searchVal) {
                $builder->whereIn('users.id', $users)
                ->orWhere('amount', 'LIKE', "%$searchVal%")
                ->orWhere(DB::raw("DATE_FORMAT(commission_withdrawal_histories.created_at, '%d-%m-%Y')"), 'LIKE', "%$searchVal%");
            });
        }

        if (!empty($request->date)) {
            $reqs = $reqs->where(DB::raw("DATE_FORMAT(commission_withdrawal_histories.created_at, '%d-%m-%Y')"), $request->date);
        }

        if (!empty($request->seller)) {
            $reqs = $reqs->where('commission_withdrawal_histories.user_id', $request->seller);
        }

        return dataTables()->eloquent($reqs)
        ->addColumn('seller_name', function ($row) {
            return $row->name ?? '-';
        })
        ->addColumn('date', function ($row) {
            return date('d-m-Y', strtotime($row->date));
        })
        ->editColumn('amount', function ($row) {
            return Helper::currency($row->amount);
        })
        ->addColumn('details', function ($row) {
            return '<button class="btn btn-success btn-sm show-orders" data-id="' . $row->id . '"> <i class="fa fa-eye"> </i> </button>';
        })
        ->addColumn('action', function ($row) {

            return '<button class="accept-wreq btn-primary f-500 f-14 btn-sm bg-success" data-id="' . $row->id . '"> ACCEPT </button>
                <button class="reject-wreq btn-primary f-500 f-14 btn-sm bg-error" data-id="' . $row->id . '"> REJECT </button>';
        })
        ->rawColumns(['action', 'details'])
        ->toJson();
    }

    public function withdrawReqsAccepted(Request $request) {
        $reqs = CommissionWithdrawalHistory::join('users', 'users.id', '=', 'commission_withdrawal_histories.user_id')
        ->selectRaw("users.name as name, commission_withdrawal_histories.amount, commission_withdrawal_histories.status, commission_withdrawal_histories.id as id, commission_withdrawal_histories.created_at as date")
        ->where('commission_withdrawal_histories.status', 1)
        ->orderBy('id', 'DESC');

        if (isset($request->search['value']) && !empty($request->search['value'])) {
            $searchVal = $request->search['value'];

            $users = User::select('id')->where('name', 'LIKE', "%$searchVal%")->pluck('id')->toArray();

            $reqs = $reqs->where(function ($builder) use($users, $searchVal) {
                $builder->whereIn('users.id', $users)
                ->orWhere('amount', 'LIKE', "%$searchVal%")
                ->orWhere(DB::raw("DATE_FORMAT(commission_withdrawal_histories.created_at, '%d-%m-%Y')"), 'LIKE', "%$searchVal%");
            });
        }

        return dataTables()->eloquent($reqs)
        ->addColumn('seller_name', function ($row) {
            return $row->name ?? '-';
        })
        ->addColumn('date', function ($row) {
            return date('d-m-Y', strtotime($row->date));
        })
        ->editColumn('amount', function ($row) {
            return Helper::currency($row->amount);
        })
        ->addColumn('details', function ($row) {
            return '<button class="btn btn-success btn-sm show-orders" data-id="' . $row->id . '"> <i class="fa fa-eye"> </i> </button>';
        })
        ->rawColumns(['details'])
        ->toJson();
    }

    public function withdrawReqsRejected(Request $request) {
        $reqs = CommissionWithdrawalHistory::join('users', 'users.id', '=', 'commission_withdrawal_histories.user_id')
        ->selectRaw("users.name as name, commission_withdrawal_histories.amount, commission_withdrawal_histories.status, commission_withdrawal_histories.id as id, commission_withdrawal_histories.created_at as date")
        ->where('commission_withdrawal_histories.status', 2)
        ->orderBy('id', 'DESC');

        if (isset($request->search['value']) && !empty($request->search['value'])) {
            $searchVal = $request->search['value'];

            $users = User::select('id')->where('name', 'LIKE', "%$searchVal%")->pluck('id')->toArray();

            $reqs = $reqs->where(function ($builder) use($users, $searchVal) {
                $builder->whereIn('users.id', $users)
                ->orWhere('amount', 'LIKE', "%$searchVal%")
                ->orWhere(DB::raw("DATE_FORMAT(commission_withdrawal_histories.created_at, '%d-%m-%Y')"), 'LIKE', "%$searchVal%");
            });
        }

        return dataTables()->eloquent($reqs)
        ->addColumn('seller_name', function ($row) {
            return $row->name ?? '-';
        })
        ->addColumn('date', function ($row) {
            return date('d-m-Y', strtotime($row->date));
        })
        ->editColumn('amount', function ($row) {
            return Helper::currency($row->amount);
        })
        ->addColumn('details', function ($row) {
            return '<button class="btn btn-success btn-sm show-orders" data-id="' . $row->id . '"> <i class="fa fa-eye"> </i> </button>';
        })
        ->rawColumns(['details'])
        ->toJson();
    }

    public function withdrawReqs2(Request $request) {
        $reqs = CommissionWithdrawalHistory::join('users', 'users.id', '=', 'commission_withdrawal_histories.user_id')
        ->selectRaw("users.name as name, commission_withdrawal_histories.amount, commission_withdrawal_histories.status, commission_withdrawal_histories.id as id, commission_withdrawal_histories.created_at as date")
        ->where('commission_withdrawal_histories.user_id', auth()->user()->id)
        ->orderBy('id', 'DESC');

        if (isset($request->search['value']) && !empty($request->search['value'])) {
            $searchVal = $request->search['value'];
            
            $reqs = $reqs->where(function ($builder) use($searchVal) {
                $builder->where('amount', 'LIKE', "%$searchVal%")
                ->orWhere(DB::raw("DATE_FORMAT(commission_withdrawal_histories.created_at, '%d-%m-%Y')"), 'LIKE', "%$searchVal%");

                if (str_contains('accepted', strtolower($searchVal))) {
                    $builder = $builder->orWhere('commission_withdrawal_histories.status', 1);
                } else if (str_contains('rejected', strtolower($searchVal))) {
                    $builder = $builder->orWhere('commission_withdrawal_histories.status', 2);
                } else if (str_contains('pending', strtolower($searchVal))) {
                    $builder = $builder->orWhere('commission_withdrawal_histories.status', 0);
                }
            });
        }

        return dataTables()->eloquent($reqs)
        ->addColumn('seller_name', function ($row) {
            return $row->name ?? '-';
        })
        ->addColumn('date', function ($row) {
            return date('d-m-Y', strtotime($row->date));
        })
        ->editColumn('amount', function ($row) {
            return Helper::currency($row->amount);
        })
        ->addColumn('details', function ($row) {
            return '<button class="btn btn-success btn-sm show-orders" data-id="' . $row->id . '"> <i class="fa fa-eye"> </i> </button>';
        })
        ->addColumn('action', function ($row) {

            if ($row->status == 1) {
                return '<span class="text-success"> Accepted </span>';
            } else if ($row->status == 2) {
                return '<span class="text-danger"> Rejected </span>';
            } else {
                return '<span class="text-secondary"> Pending </span>';
            }
        })
        ->rawColumns(['action', 'details'])
        ->toJson();
    }

    public function withdrawalReqInfo(Request $request) {
        $info = CommissionWithdrawalHistory::where('id', $request->id)->first();

        if ($info != null) {
            $transactions = Transaction::with('order')->whereIn('so_id', json_decode($info->orders, true))
                            ->where('amount_type', 3)
                            ->where('withdrawal_request', 1)
                            ->get();

            return response()->json(['status' => true, 'html' => view('reports.withdrawal-modal', compact('transactions'))->render()]);
        }

        return response()->json(['status' => false, 'message' => Helper::$notFound]);
    }

    public function acceptWithdrawalRequest(Request $request) {
        $withdrawalRequest = CommissionWithdrawalHistory::where('id', $request->id)->first();
        $attachmentJson = [];

        DB::beginTransaction();

        try {
            if ($withdrawalRequest != null) {
                Transaction::where('transaction_type', 1)
                ->whereIn('so_id', json_decode($withdrawalRequest->orders, true))
                ->where('amount_type', 3)
                ->update(['withdrawal_request' => 2]);
                $sellerAmount = $withdrawalRequest->amount;
                $sellerId = $withdrawalRequest->user_id;

                if ($request->hasFile('receipt')) {
                    foreach ($request->file('receipt') as $file) {
                        $name = 'PAY-RECEIPT-' . date('YmdHis') . uniqid() . '.' . $file->getClientOriginalExtension();
                        $file->move(storage_path('app/public/payment-receipt/seller'), $name);

                        if (file_exists(storage_path("app/public/payment-receipt/seller/{$name}"))) {
                            $attachmentJson[] = $name;
                        }
                    }
                }

                if (empty($attachmentJson)) {
                    $attachmentJson = null;
                } else {
                    $attachmentJson = json_encode($attachmentJson);
                }

                CommissionWithdrawalHistory::where('id', $request->id)->update(['status' => 1, 'attachments' => $attachmentJson]);

                $transactionUid = Helper::hash();

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_id' => $transactionUid,
                    'transaction_type' => 1,
                    'user_id' => 1,
                    'voucher' => 'Payment done',
                    'amount' => $sellerAmount,
                    'year' => Helper::$financialYear,
                    'added_by' => auth()->user()->id
                ]);

                Transaction::create([
                    'amount_type' => 0,
                    'transaction_id' => $transactionUid,
                    'transaction_type' => 0,
                    'user_id' => $sellerId,
                    'attachments' => $attachmentJson,
                    'voucher' => 'Payment received',
                    'amount' => $sellerAmount,
                    'year' => Helper::$financialYear,
                    'added_by' => auth()->user()->id
                ]); 

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Withdrawal request accepted successfully.']);
            } else {
                DB::commit();
                return response()->json(['status' => false, 'message' => Helper::$notFound]);
            }
        } catch (\Exception $e) {

            if (is_array($attachmentJson) && !empty($attachmentJson)) {
                foreach ($attachmentJson as $eachImage) {
                    if (file_exists(storage_path("app/public/payment-receipt/seller/{$eachImage}"))) {
                        unlink(storage_path("app/public/payment-receipt/seller/{$eachImage}"));
                    }
                }
            }

            DB::rollBack();
            return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
        }
    }

    public function rejectWithdrawalRequest(Request $request) {
        $withdrawalRequest = CommissionWithdrawalHistory::where('id', $request->id)->first();

        DB::beginTransaction();

        try {
            if ($withdrawalRequest != null) {
                Transaction::where('transaction_type', 1)
                ->whereIn('so_id', json_decode($withdrawalRequest->orders, true))
                ->where('amount_type', 3)
                ->update(['withdrawal_request' => 3]);      
                
                CommissionWithdrawalHistory::where('id', $request->id)->update(['status' => 2]);

                DB::commit();
                return response()->json(['status' => true, 'message' => 'Withdrawal request rejected successfully.']);
            } else {
                DB::commit();
                return response()->json(['status' => false, 'message' => Helper::$notFound]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
        }
    }

    public function withdrwalDetails(Request $request) {
        $data = CommissionWithdrawalHistory::with(['user', 'bank'])->where('id', $request->id)->first();

        if ($data != null) {
            return response()->json(['status' => true, 'html' => view('reports.withdraw-details', compact('data'))->render()]);
        }

        return response()->json(['status' => false, 'message' => Helper::$notFound]);
    }
}
