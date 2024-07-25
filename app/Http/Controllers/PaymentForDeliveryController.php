<?php

namespace App\Http\Controllers;

use App\Models\{PaymentForDelivery, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class PaymentForDeliveryController extends Controller
{
    public function index(Request $request) {
        if ($request->method() == 'GET') {
            $moduleName = 'Payment for Delivery';
            $payments = PaymentForDelivery::select('id', 'driver_id', 'distance', 'payment')->whereNotNull('driver_id')->get();
            $payment = PaymentForDelivery::select('distance', 'payment')->whereNull('driver_id')->first();

            $drivers = User::whereHas('role', function ($builder) {
                $builder->where('roles.id', 3);
            })->selectRaw("CONCAT(name, ' - ', city_id, '') as name, id")->pluck('name', 'id')->toArray();

            return view('payment-for-delivery.index', compact('moduleName', 'drivers', 'payments', 'payment'));
        } else {

            $this->validate($request,[
                'payment' => 'required|numeric|min:0',
                'distance' => 'required|numeric|min:0',
                'mpayment.*' => 'sometimes|numeric|min:0',
                'mdistance.*' => 'sometimes|numeric|min:0',
                'mdriver.*' => 'sometimes'
            ],[
                'payment.*.required' => 'Enter payment amount.',
                'payment.*.numeric' => 'Enter valid payment amount.',
                'payment.*.min' => 'Payment amount can\'t be less than 1.',
                'distance*.required' => 'Enter distance.',
                'distance*.numeric' => 'Enter valid distance.',
                'distance*.min' => 'Distance can\'t be less than 1.',
                'mpayment*.required' => 'Enter payment amount.',
                'mpayment*.numeric' => 'Enter valid payment amount.',
                'mpayment*.min' => 'Payment amount can\'t be less than 1.',
                'mdistance*.required' => 'Enter distance.',
                'mdistance*.numeric' => 'Enter valid distance.',
                'mdistance*.min' => 'Distance can\'t be less than 1.'
            ]);

            DB::beginTransaction();

            try {

                $drivers = $request->mdriver;

                $toBeEdited = is_array($request->edit_id) && !empty($request->edit_id) ? array_values($request->edit_id) : [];

                PaymentForDelivery::whereNotNull('driver_id')->whereNotIn('id', $toBeEdited)->delete();

                if (PaymentForDelivery::whereNull('driver_id')->exists()) {
                    PaymentForDelivery::whereNull('driver_id')->update([
                        'added_by' => auth()->user()->id,
                        'distance' => $request->distance,
                        'payment' => $request->payment
                    ]);
                } else {
                    PaymentForDelivery::create([
                        'added_by' => auth()->user()->id,
                        'distance' => $request->distance,
                        'payment' => $request->payment
                    ]);
                }

                if (is_array($drivers) && count($drivers) > 0) {
                    foreach ($drivers as $k => $driver) {

                        if (isset($request->edit_id[$k])) {
                            PaymentForDelivery::where('id', $request->edit_id[$k])->update([
                                'driver_id' => $driver,
                                'added_by' => auth()->user()->id,
                                'distance' => $request->mdistance[$k],
                                'payment' => $request->mpayment[$k]
                            ]);
                        } else {
                            PaymentForDelivery::create([
                                'driver_id' => $driver,
                                'added_by' => auth()->user()->id,
                                'distance' => $request->mdistance[$k],
                                'payment' => $request->mpayment[$k]
                            ]);
                        }

                    }
                }

                DB::commit();
                return redirect()->back()->with('success', 'Payment for delivery records updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                Helper::logger($e->getMessage(), 'critical');
                return redirect()->back()->with('error', Helper::$errorMessage);
            }

        }
    }
}
