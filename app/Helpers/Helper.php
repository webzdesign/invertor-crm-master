<?php

namespace App\Helpers;


use App\Models\ChangeOrderStatusTrigger;
use App\Models\AddTaskToOrderTrigger;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use App\Models\PurchaseOrder;
use App\Models\Distribution;
use Illuminate\Http\Request;
use App\Models\SalesOrder;
use App\Models\Setting;
use App\Models\Trigger;
use App\Models\Product;
use App\Models\Country;
use App\Models\Wallet;
use App\Models\State;
use App\Models\Stock;
use App\Models\City;
use App\Models\User;

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
        $html = '<option value="" selected> --- Select a State --- </option>';

        foreach ($states as $id => $state) {
            $html .= "<option value='{$id}'> {$state} </option>";
        }

        return response()->json(['status' => true, 'states' => $html]);
    }

    public function getCities(Request $request) {
        $cities = City::where('state_id', $request->id)->active()->select('id', 'name as text')->pluck('text', 'id')->toArray();
        $html = '<option value="" selected> --- Select a State --- </option>';

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

    public static function spaceBeforeCap($string = '') {
        return preg_replace('/(?<!\ )[A-Z]/', ' $0', $string);
    }

    public static function generatePurchaseOrderNumber () {
        $orderNo = 0;

        if (PurchaseOrder::withTrashed()->orderBy('id', 'DESC')->first() !== null) {
            $orderNo = PurchaseOrder::withTrashed()->orderBy('id', 'DESC')->first()->id;
        }

        $orderNo += 1;
        $prefix = date('-Y-');
        $orderNo = sprintf('%05d', $orderNo);
        $orderNo = "PO{$prefix}{$orderNo}";

        return $orderNo;
    }

    public static function generateSalesOrderNumber () {
        $orderNo = 0;
        
        if (SalesOrder::withTrashed()->orderBy('id', 'DESC')->first() !== null) {
            $orderNo = SalesOrder::withTrashed()->orderBy('id', 'DESC')->first()->id;
        }

        $orderNo += 1;
        $prefix = date('-Y-');
        $orderNo = sprintf('%05d', $orderNo);
        $orderNo = "SO{$prefix}{$orderNo}";

        return $orderNo;
    }

    public static function generateProductNumber () {
        $proNo = 0;

        if (Product::withTrashed()->orderBy('id', 'DESC')->first() !== null) {
            $proNo = Product::withTrashed()->orderBy('id', 'DESC')->first()->id;
        }

        $proNo += 1;
        $proNo = sprintf('%03d', $proNo);
        $proNo = "{$proNo}";

        return $proNo;
    }

    public static function generateDistributionNumber () {
        $orderNo = 0;

        if (Distribution::withTrashed()->orderBy('id', 'DESC')->first() !== null) {
            $orderNo = Distribution::withTrashed()->orderBy('id', 'DESC')->first()->id;
        }

        $orderNo += 1;
        $prefix = date('Ymdhis-');
        $orderNo = sprintf('%05d', $orderNo);
        $orderNo = "D{$prefix}{$orderNo}";

        return $orderNo;
    }

    public static function getCountriesOrderBy() {
        return Country::active()->select('id', 'name')->orderByRaw("CASE  WHEN name = 'United Kingdom' THEN 0 WHEN name = 'Pakistan' THEN 1 ELSE 2 END")->pluck('name', 'id')->toArray();
    }

    public static function getSellerCommission() {
        return self::currency(Wallet::where('seller_id', auth()->user()->id)->where('form', 1)->sum('commission_amount'));
    }

    public static function currencyFormatter($amount, $showSign = false, $in = 'GBP') {
        if ($showSign === false) {
            return mb_substr(Number::currency($amount, 'GBP'), 1);
        }

        return Number::currency($amount, 'GBP');
    }

    public static function getAvailableStockFromStorage() {
        $prodArr = Product::select('id', 'name')->pluck('name', 'id')->toArray();

        $stockInItems = Stock::where('type', '0')
                                ->whereIn('form', ['1', '3'])
                                ->whereNull('driver_id')
                                ->groupBy('product_id')
                                ->select('product_id')
                                ->pluck('product_id')
                                ->toArray();

        $products = [];

        foreach ($stockInItems as $item) {
            $inStock = Stock::where('type', '0')
            ->whereIn('form', ['1', '3'])
            ->where('product_id', $item)
            ->whereNull('driver_id')
            ->select('qty')
            ->sum('qty');

            $outStock = Stock::where('type', '1')
            ->where('product_id', $item)
            ->whereIn('form', ['3'])
            ->whereNull('driver_id')
            ->select('qty')
            ->sum('qty');

            $availStock = intval($inStock) - intval($outStock);

            if ($availStock > 0 && isset($prodArr[$item])) {
                $products[$item] = $availStock;
            }
        }

        return $products;
    }

    public static function getAvailableStockFromDriver($driver) {
        $stockInItems = Stock::where('type', '0')
                        ->where('driver_id', $driver)
                        ->whereIn('form', ['1', '3'])
                        ->groupBy('product_id')
                        ->select('product_id')
                        ->pluck('product_id')
                        ->toArray();

        $products = [];

        foreach ($stockInItems as $item) {
            $inStock = Stock::where('type', '0')
            ->whereIn('form', ['1', '3'])
            ->where('product_id', $item)
            ->where('driver_id', $driver)
            ->select('qty')
            ->sum('qty');

            $outStock = Stock::where('type', '1')
            ->where('product_id', $item)
            ->whereIn('form', ['3'])
            ->where('driver_id', $driver)
            ->select('qty')
            ->sum('qty');

            $availStock = intval($inStock) - intval($outStock);

            if ($availStock > 0) {
                $products[$item] = $availStock;
            }

        }

        return $products;
    }

    public static function productName($id = null) {
        if ($id == null) {
            return Product::select('id', 'name')->pluck('name', 'id')->toArray();
        } else {
            return Product::select('id', 'name')->where('id', $id)->first()->name ?? '-';
        }
    }

    public static function userName($id = null, $default = '-') {
        if ($id == null) {
            return User::select('id', 'name')->pluck('name', 'id')->toArray();
        } else {
            return User::select('id', 'name')->where('id', $id)->first()->name ?? $default;
        }
    }

    public static function shouldHideBreadcumb() {
        $route = request()->route()->getName() ?? '';

        return !in_array($route, [
            'sales-order-status',
            'sales-order-status-list',
            'sales-order-status-edit'
        ]);
    }

    public static function generateTextColor(string $hexcolor) {
        $hexcolor = str_replace("#", "", $hexcolor);

        $r = hexdec(substr($hexcolor, 0, 2));
        $g = hexdec(substr($hexcolor, 2, 2));
        $b = hexdec(substr($hexcolor, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return ($yiq >= 128) ? '#000' : '#fff';
    }

    public static function getStrToTime($date1, $date2) {
        $diffString = '+0 seconds';

        $date1 = new \DateTime($date1);
        $date2 = new \DateTime($date2);

        if ($date1 < $date2) {
            $diff = $date1->diff($date2);

            if ($diff->days > 0) {
                $diffString .= '+' . $diff->days . ' days ';
            }
            if ($diff->h > 0) {
                $diffString .= '+' . $diff->h . ' hours ';
            }
            if ($diff->i > 0) {
                $diffString .= '+' . $diff->i . ' minutes ';
            }
            if ($diff->s > 0) {
                $diffString .= '+' . $diff->s . ' seconds ';
            }
            
            $diffString = trim($diffString);
        }

        return $diffString;
    }

    public static function currency($amount) {
        return "£" . number_format(round($amount), 0, '.', ',');
    }
}
