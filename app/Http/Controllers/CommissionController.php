<?php

namespace App\Http\Controllers;

use App\Models\{CommissionPrice, Setting};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class CommissionController extends Controller
{
    protected $moduleName = 'Commissions';

    public function index(Request $request) {

        if ($request->method() == 'GET') {

            $moduleName = $this->moduleName;
            $settings = Setting::select('seller_commission', 'bonus')->first();
            $prices = CommissionPrice::select('price')->pluck('price')->toArray();
    
            return view('commissions.index', compact('moduleName', 'settings', 'prices'));

        } else if ($request->method() == 'POST') {

            $this->validate($request,[
                'seller_commission' => 'required|numeric|min:0',
                'bonus' => 'required|numeric|min:0',
                'price.*' => 'sometimes|min:1|numeric'
            ],[
                'seller_commission.required' => 'Enter commission amount.',
                'seller_commission.min' => 'Commission amount can\'t be in negative.',
                'seller_commission.numeric' => 'Enter valid format.',
                'bonus.required' => 'Minimum price must be 1.',
                'bonus.min' => 'Bonus amount can\'t be in negative.',
                'bonus.numeric' => 'Enter valid format.',
                'price.min' => 'Minimum price must be 1.',
                'price.numeric' => 'Enter valid format.'
            ]);

            DB::beginTransaction();

            try {
                $prices = $request->price;

                if (is_array($prices)) {
                    if (count($prices) !== count(array_unique($prices))) {
                        DB::commit();
                        return redirect()->back()->with('error', 'Can\'t add same price multiple times.');
                    }
                }

                CommissionPrice::query()->delete();
                if (is_array($prices) && count($prices) > 0) {
                    foreach ($prices as $price) {
                        CommissionPrice::create(['price' => $price]);
                    }
                } 

                if (Setting::count() > 0) {
                    Setting::first()->update([
                        'seller_commission' => $request->seller_commission,
                        'bonus' => $request->bonus
                    ]);
                } else {
                    Setting::create([
                        'seller_commission' => $request->seller_commission,
                        'bonus' => $request->bonus,
                        'title' => 'E-Bike-CRM'
                    ]);
                }

                DB::commit();
                return redirect()->back()->with('success', 'Commission updated successfully.');
            } catch(\Exception $e) {
                DB::rollBack();
                Helper::logger($e->getMessage(), 'critical');
                return redirect()->back()->with('error', Helper::$errorMessage);
            }
        }
    }
}
