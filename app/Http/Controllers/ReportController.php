<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Stock;
use App\Models\User;

class ReportController extends Controller
{
    public function stockReport(Request $request) {
        $drivers = User::whereHas('role', function ($builder) {
            $builder->where('roles.id', 3);
        })->select('users.id as id', 'users.name as name');

        if (!$request->ajax()) {
            $moduleName = 'Stock Report';
            $drivers = $drivers->pluck('name', 'id')->toArray();
            $types = [ '1' => 'Storage', '2' => 'Driver'];

            return view('reports.stock', compact('moduleName', 'drivers', 'types'));
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

        return dataTables()->of($stock)
        ->editColumn('product_id', function ($row) {
            return Helper::productName($row['product_id']);
        })
        ->toJson();
    }
}
