<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\SalesOrderStatus;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class SalesOrderStatusController extends Controller
{
    public function index() {
        $moduleName = 'Sales Order Status';
        $statuses = SalesOrderStatus::orderBy('sequence', 'ASC')->get();
        $colours = ['#99ccff', '#ffcccc', '#ffff99', '#c1c1c1', '#9bffe2', '#f7dd8b', '#c5ffd6'];

        return view('sales-orders-status.index', compact('moduleName', 'statuses', 'colours'));
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

        DB::beginTransaction();

        try {
            if (count($request->name) > 0) {

                SalesOrderStatus::where('id', '!=', '1')->delete();
                foreach ($request->name as $key => $value) {
                    SalesOrderStatus::create([
                        'name' => $value,
                        'slug' => Helper::slug($value),
                        'color' => isset($request->color[$key + 1]) ? $request->color[$key + 1] : '#bfbfbf',
                        'sequence' => $key + 1
                    ]);
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
}