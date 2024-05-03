<?php

namespace App\Helpers;

use \Illuminate\Support\Facades\Log;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\State;
use App\Models\City;

class Helper {

    public static $appLogo = 'assets/images/logo.png';
    public static $favIcon = 'assets/images/favicon.ico';
    public static $errorMessage = 'Oops! Something went wrong.';

    public static function getAppTitle()
    {
        $setting = Setting::select('title')->first();
        if ($setting) {
            return $setting->title;
        } else {
            return config('app.name');
        }
    }

    public static function getAppFavicon()
    {
        $setting = Setting::select('favicon')->first();
        if ($setting) {
            if ($setting->favicon && @file_exists(storage_path("public/settings/favicon/" . $setting->favicon))) {
                return "storage/app/public/settings/favicon/" . $setting->favicon;
            }
            return self::$favIcon . '?time=' . time();
        } else {
            return self::$favIcon . '?time=' . time();
        }
    }

    //get app favicon
    public static function getAppLogo()
    {
        $setting = Setting::select('logo')->first();
        if ($setting) {
            if ($setting->logo && @file_exists(storage_path("public/settings/logo/" . $setting->logo))) {
                return "storage/app/public/settings/logo/" . $setting->logo;
            }
            return self::$appLogo . '?time=' . time();
        } else {
            return self::$appLogo . '?time=' . time();
        }
    }

    public function getStates(Request $request) {
        $states = State::where('country_id', $request->id)->active()->select('id', 'name as text')->pluck('text', 'id')->toArray();
        $html = '';

        foreach ($states as $id => $state) {
            $html .= "<option value='{$id}'> {$state} </option>";
        }

        return response()->json(['status' => true, 'states' => $html]);
    }

    public function getCities(Request $request) {
        $cities = City::where('state_id', $request->id)->active()->select('id', 'name as text')->pluck('text', 'id')->toArray();
        $html = '';

        foreach ($cities as $id => $city) {
            $html .= "<option value='{$id}'> {$city} </option>";
        }

        return response()->json(['status' => true, 'cities' => $html]);
    }

    public static function logger($message, $type = 'error') {
        Log::$type($message);
    }

    public static function slug($string, $separator = '-') {
        $string = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $string);
        $string = trim($string, $separator);
        if (function_exists('mb_strtolower')) {
            $string = mb_strtolower($string);
        } else {
            $string = strtolower($string);
        }
        $string = preg_replace("/[\/_|+ -]+/", $separator, $string);

        return $string;
    }

    public static function generateOrderNumber () {
        $orderNo = PurchaseOrder::latest()->select('id')->first()->id + 1 ?? 1;
        $prefix = date('-Y-');
        $orderNo = sprintf('%05d', $orderNo);
        $orderNo = "PO{$prefix}{$orderNo}";

        return $orderNo;
    }
}