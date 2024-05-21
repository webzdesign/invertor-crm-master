<?php

namespace App\Http\Controllers;

use App\Models\{SalesOrderStatus, SalesOrder};
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
            $tempOrder = SalesOrder::join('sales_order_items', 'sales_order_items.so_id', '=', 'sales_orders.id')->selectRaw("sales_orders.id, sales_orders.order_no, sales_orders.delivery_date, SUM(sales_order_items.amount) as amount")->where('sales_orders.status', $status->id)->groupBy('sales_order_items.so_id');

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
                            'name' => $value,
                            'slug' => Helper::slug($value),
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
}