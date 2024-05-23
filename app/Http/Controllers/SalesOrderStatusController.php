<?php

namespace App\Http\Controllers;

use App\Models\{SalesOrderStatus, SalesOrder, SalesOrderItem, Deliver, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class SalesOrderStatusController extends Controller
{
    public function index() {
        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::orderBy('sequence', 'ASC')->get();
        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];
        $orders = [];

        foreach ($statuses as $status) {
            $tempOrder = SalesOrder::join('sales_order_items', 'sales_order_items.so_id', '=', 'sales_orders.id')->selectRaw("sales_orders.id, sales_orders.order_no, sales_orders.date, SUM(sales_order_items.amount) as amount")->where('sales_orders.status', $status->id)->groupBy('sales_order_items.so_id');

            if ($tempOrder->exists()) {
                $orders[$status->id] = $tempOrder->get()->toArray();
            }
        }

        return view('sales-orders-status.index', compact('moduleName', 'statuses', 'colours', 'orders'));
    }

    public function edit() {
        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::orderBy('sequence', 'ASC')->get();
        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];

        return view('sales-orders-status.edit', compact('moduleName', 'statuses', 'colours'));        
    }

    public function update(Request $request) {
        $this->validate($request, [
            'name.*' => 'required|distinct'
        ], [
            'name.*.required' => 'Enter status name before you save.',
            'name.*.distinct' => 'Status name must be unique.'
        ]);

        $sequences = $request->sequence;
        $names = $request->name;
        $colors = $request->color;

        if (count($sequences) != count($names)) {
            return redirect()->route('sales-order-status-edit')->with('error', 'Add atleast a card to save.');
        }

        DB::beginTransaction();

        try {
            if (count($sequences) > 0) {

                foreach ($sequences as $key => $value) {

                    if (!is_null($value)) {
                        SalesOrderStatus::where('id', $value)->update([
                            'name' => $names[$key],
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                    } else {
                        SalesOrderStatus::create([
                            'name' => $names[$key],
                            'slug' => Helper::slug($names[$key]),
                            'color' => isset($colors[$key]) ? $colors[$key] : '#bfbfbf',
                            'sequence' => $key + 1
                        ]);
                    }
                }

                DB::commit();
                return redirect()->route('sales-order-status')->with('success', 'Sales order status updated successfully.');
            }   
        } catch (\Exception $e) {
            Helper::logger($e->getMessage() . ' ' . $e->getLine());
            DB::rollBack();
            return redirect()->route('sales-order-status-edit')->with('error', Helper::$errorMessage);
        }

        DB::rollBack();
        return redirect()->route('sales-order-status-edit')->with('error', 'Add atleast a card to save.');
    }

    public function sequence(Request $request) {
        if (SalesOrderStatus::where('id', $request->status)->doesntExist()) {
            return response()->json(['status' => false, 'container' => true]);
        }

        if (SalesOrder::where('id', $request->order)->doesntExist()) {
            return response()->json(['status' => false, 'card' => true]);
        }

        if (SalesOrder::where('id', $request->order)->update(['status' => $request->status])) {
            return response()->json(['status' => true]);
        }

        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function list(Request $request) {
        if(!in_array(auth()->user()->roles->first()->id, [1,2,3])) {
            abort(403);
        }

        $statuses = SalesOrderStatus::active()->select('id', 'name', 'color')->get();

        if (!$request->ajax()) {
            $moduleName = 'Sales Order Status';
    
            return view('sales-orders-status.list', compact('moduleName', 'statuses'));
        }

        $orders = SalesOrder::with('items');
        $thisUserRoles = auth()->user()->roles->pluck('id')->toArray();

        if (!in_array(1, $thisUserRoles)) {
            if (in_array(2, $thisUserRoles)) {
                $orders = $orders->where('seller_id', auth()->user()->id);
            } else if (in_array(3, $thisUserRoles)) {
                $driversOrder = Deliver::where('user_id', auth()->user()->id)->select('soi_id')->pluck('soi_id')->toArray();
                $driversOrder = SalesOrderItem::select('so_id')->whereIn('id', $driversOrder)->groupBy('so_id')->pluck('so_id')->toArray();

                $orders = $orders->whereIn('id', $driversOrder);
            }
        }

        $tempCount = $orders->count();

        if (isset($request->order[1]['column']) && $request->order[1]['column'] == 0) {
            $orders = $orders->orderBy('id', 'desc');
        }

        return dataTables($orders)
                ->addColumn('checkbox', function ($row) {
                    return "<input type='checkbox' class='form-check-input single-checkbox' value='{$row->id}' />";
                })
                ->editColumn('order_no', function ($row) {
                    $route = route('sales-orders.view', encrypt($row->id));
                    return "<a target='_blank' href='{$route}' class='color-blue'> {$row->order_no} </a>";
                })
                ->addColumn('status', function ($row) use ($statuses) {
                    // $html = "<div class='d-flex'> <select class='status-select2'>";

                    // foreach ($statuses as $status) {
                    //     $html.= "<option value='{$status->id}' data-oid='{$row->id}' data-color='{$status->color}' " . ($row->status == $status->id ? 'selected' : '') . " > {$status->name} </option>";
                    // }

                    // $html .= "</select> <button class='select2-opener'> <i class='fa fa-edit'> </i> </button> </div>";

                    return 
                    '<div class="status-main">
                        <label class="status-label" style="background:pink;">Test</label>
                        <button class="status-opener ms-2 d-inline-flex align-items-center justify-content-center"> <i class="fa fa-edit"></i> </button>
                    </div>';
                })
                ->addColumn('date', function ($row) {
                    return \Carbon\Carbon::parse($row->date)->toFormattedDateString();
                })
                ->addColumn('amount', function ($row) {
                    return Helper::currencyFormatter($row->items->sum('amount'), true);
                })
                ->rawColumns(['order_no', 'checkbox', 'status'])
                ->with(['totalOrders' => $tempCount])
                ->make(true);
    }

    public function status (Request $request) {
        if (!empty($request->status) && !empty($request->order)) {
            return response()->json(['status' => SalesOrder::where('id', $request->order)->update(['status' => $request->status]), 'message' => 'Status Updated successfully']);
        }
        return response()->json(['status' => false, 'message' => Helper::$errorMessage]);
    }

    public function statusBulkUpdate(Request $request) {
        if ($request->ids == 'all') {
            $resp = SalesOrder::query()->update(['status' => $request->status]);
            return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for all orders successfully.' : Helper::$errorMessage));
        } else {
            $resp = SalesOrder::whereIn('id', explode(',', $request->ids))->update(['status' => $request->status]);
            return redirect()->back()->with($resp ? 'success' : 'error', ($resp ? 'Status updated for orders successfully.' : Helper::$errorMessage));
        }
    }
}