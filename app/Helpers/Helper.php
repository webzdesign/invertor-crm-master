<?php

namespace App\Helpers;

use App\Models\{PurchaseOrder, Transaction, Distribution};
use App\Models\{SalesOrder, Setting, Product, Country,TwilloMessageNotification,TwilloNotificationHistory};
use App\Models\{State, Stock, City, User};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Http\Request;

class Helper {

    public static $appLogo = 'assets/images/logo.png';
    public static $favIcon = 'assets/images/favicon.ico';
    public static $errorMessage = 'Oops! Something went wrong.';
    public static $notFound = 'Not found.';
    public static $russianMonthNames = [
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь'
    ];

    public static $financialYear = '2024-25';

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
            if ($setting->favicon && file_exists(public_path("assets/images/{$setting->favicon}"))) {
                return "assets/images/{$setting->favicon}";
            }
        }

        return self::$favIcon;
    }

    //get app favicon
    public static function getAppLogo()
    {
        $setting = Setting::select('logo')->first();
        if ($setting) {
            if ($setting->logo && file_exists(public_path("assets/images/{$setting->logo}"))) {
                return "assets/images/{$setting->logo}";
            }
        }

        return self::$appLogo;
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
        $total = 0;

        $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
        ->selectRaw("voucher, users.name as user, amount, transaction_type, amount_type")
        ->whereIn('transactions.amount_type', [3, 0])
        ->where('transactions.user_id', '=', auth()->user()->id)
        ->orderBy('transaction_type', 'ASC');

        foreach ($ledger->get() as $data) {
            if ($data->transaction_type) {
                $total -= $data->amount;
            } else {
                $total += $data->amount;
            }
        }

        return Helper::currency(abs($total));
    }

    public static function getAdminBalance() {
        $cr = Transaction::credit()->where('is_approved', 1)->where('amount_type', 0)->where('user_id', 1)->sum('amount');
        $dr = Transaction::debit()->where('is_approved', 1)->where('amount_type', 0)->where('user_id', 1)->sum('amount');

        return Helper::currency($cr - $dr);
    }

    public static function getDriverBalance() {
        $total = 0;

        $ledger = Transaction::join('users', 'users.id', '=', 'transactions.user_id')
        ->selectRaw("voucher, users.name as user, amount, transaction_type, so_id")
        ->where('transactions.is_approved', 1)
        ->whereIn('transactions.amount_type', [0, 2])
        ->where('transactions.user_id', '=', auth()->user()->id)
        ->orderBy('transaction_type', 'ASC');

        foreach ($ledger->get() as $data) {
            if ($data->transaction_type) {
                $total -= $data->amount;
            } else {
                $total += $data->amount;
            }
        }

        return Helper::currency($total);
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
            ->whereIn('form', ['1', '3'])
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

    public static function getAvailableStockFromDriver($driver, $productId = null) {
        $stockInItems = Stock::where('type', '0')
                        ->where('driver_id', $driver)
                        ->whereIn('form', ['1', '3'])
                        ->when($productId != null, fn ($builder) => $builder->where('product_id', $productId))
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
            ->whereIn('form', ['2', '3', '4'])
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
        if ($amount < 0) {
            return '- £' . number_format(round(abs($amount)), 0, '.', ',');
        } else {
            return '£' . number_format(round($amount), 0, '.', ',');
        }
    }

    public static function hash() {
        return sha1(md5(time() . mt_rand(1, 99999999999999999) . uniqid()));
    }

    public static function formatBytes($element) {
        if ($element == '1048576') {
            return "1 MB";
        } else if ($element == '10485760') {
            return "10 MB";
        } else if ($element == '104857600') {
            return "100 MB";
        } else if ($element == '1048576000') {
            return "1 GB";
        } else if ($element == '10485760000') {
            return "10 GB";
        } else {
            return "10 MB";
        }
    }

    public static $extensionsForDoc = [
        1 => 'docm|dotm|odt|docx|dotx|text|txt|dot|doc',
        2 => 'ppt|pptm|ppsm|potm|odp|pptx|ppsx|potx',
        3 => 'xls|xltm|ods|xlsx|xltx|csv',
        4 => 'dst|dwf|dwfx|dwg|dws|dwt|dxb|dxf',
        5 => 'pdf',
        6 => 'bmp|gif|heic|heif|pjp|jpg|pjpeg|jpeg|jfif|png|tif|ico|webp',
        7 => '3gpp|3gp2|avi|m4v|mp4|mpg|mpeg|ogm|ogv|mov|webm|m4v|mkv|asx|wm|wmv|wvx|avi',
        8 => 'flac|mid|mp3|m4a|mp3|opus|oga|ogg|wav|m4a|mid|wav'
    ];

    public static function returnExtensions($string, $dot = '', $separator = '|') {
        $extensions = '';

        if (!empty($string)) {
            $allowedFileTypes = explode(',', $string);
            if (count($allowedFileTypes) > 0) {
                foreach ($allowedFileTypes as $type) {
                    if (isset(self::$extensionsForDoc[$type]) && !empty(self::$extensionsForDoc[$type])) {
                        $extensions .= str_replace('|', $separator, self::$extensionsForDoc[$type]) . $separator;
                    }
                }
            }
        }

        $extensions = rtrim($extensions, $separator);
        $extensions = explode($separator, $extensions);

        $extensions = array_map(function ($element) use ($dot) {
            return $dot . $element;
        }, $extensions);

        $extensions = implode($separator, $extensions);

        return $extensions;
    }

    public static function sendTwilioMsg($tonumber=null,$statusid=null,$type=null,$order_id=null) {

        try {

            $setting=Setting::select('twilioFromNumber','twilioAuthToken','twilioUrl','twilioAccountSid')->first();
            // Twilio credentials
            if($setting && !empty($tonumber) && $statusid != null && $type !=null) {

                $twilioAccountSid = $setting->twilioAccountSid;
                $twilioAuthToken = $setting->twilioAuthToken;
                $url = "{$setting->twilioUrl}" . $twilioAccountSid . "/Messages.json";

                $notification = TwilloMessageNotification::where('responsibale_user_type',$type)->where('status_id',$statusid)->first();

                if(!empty($notification)) {
                    foreach($tonumber as $touserid=>$tophone) {
                        if($tophone !="") {
                            $to = 'whatsapp:+918160213921';
                            // $to = "whatsapp:+{$tophone}";
                            $from = "whatsapp:{$setting->twilioFromNumber}";

                            $body = $notification->message;

                            $ch = curl_init($url);

                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_USERPWD, $twilioAccountSid . ':' . $twilioAuthToken);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                                'To' => $to,
                                'From' => $from,
                                'Body' => $body
                            ]));

                            $response = curl_exec($ch);
                            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            if (curl_errno($ch)) {
                                TwilloNotificationHistory::create([
                                    'user_id'=>$touserid,
                                    'to_number'=>'+'.$tophone,
                                    'from_number'=>$setting->twilioFromNumber,
                                    'user_type'=>$type,
                                    'message'=>'Error:' . curl_error($ch),
                                    'status_id'=>$statusid,
                                    'order_id'=>$order_id,
                                    'api_response'=>'Error:' . curl_error($ch)
                                ]);
                            } else {

                                if($httpCode == 201 || $httpCode == 200) {
                                    TwilloNotificationHistory::create([
                                        'user_id'=>$touserid,
                                        'to_number'=>'+'.$tophone,
                                        'from_number'=>$setting->twilioFromNumber,
                                        'user_type'=>$type,
                                        'message'=>$body,
                                        'status_id'=>$statusid,
                                        'order_id'=>$order_id,
                                        'api_response'=>$response
                                    ]);
                                } else {

                                    TwilloNotificationHistory::create([
                                        'user_id'=> $touserid,
                                        'to_number'=> '+'.$tophone,
                                        'from_number'=> $setting->twilioFromNumber,
                                        'user_type'=> $type,
                                        'message'=> json_decode($response)->message ?? 'Twilio API encountered an error in its response.',
                                        'status_id'=> $statusid,
                                        'order_id'=> $order_id,
                                        'api_response'=> $response
                                    ]);
                                }
                                //{"account_sid": "AC72c2c1239889f864f8c3621ccdaeb719", "api_version": "2010-04-01", "body": "Hello from Twilio WhatsApp API Testing With Developer!", "date_created": "Wed, 14 Aug 2024 12:08:41 +0000", "date_sent": null, "date_updated": "Wed, 14 Aug 2024 12:08:41 +0000", "direction": "outbound-api", "error_code": null, "error_message": null, "from": "whatsapp:+14155238886", "messaging_service_sid": null, "num_media": "0", "num_segments": "1", "price": null, "price_unit": null, "sid": "SMcd04f021bd7ee79d1d8b0a12a822d0d0", "status": "queued", "subresource_uris": {"media": "/2010-04-01/Accounts/AC72c2c1239889f864f8c3621ccdaeb719/Messages/SMcd04f021bd7ee79d1d8b0a12a822d0d0/Media.json"}, "to": "whatsapp:+918160213921", "uri": "/2010-04-01/Accounts/AC72c2c1239889f864f8c3621ccdaeb719/Messages/SMcd04f021bd7ee79d1d8b0a12a822d0d0.json"}
                            }
                            curl_close($ch);
                        }
                    }
                } else {
                    foreach($tonumber as $touserid=>$tophone) {
                        if($tophone !="") {
                            TwilloNotificationHistory::create([
                                'user_id'=>$touserid,
                                'to_number'=>'+'.$tophone,
                                'from_number'=>$setting->twilioFromNumber,
                                'user_type'=>$type,
                                'message'=>'A notification has not been established for this particular status and type.',
                                'status_id'=>$statusid,
                                'order_id'=>$order_id
                            ]);
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            self::logger($e->getMessage());

        }
    }
}
